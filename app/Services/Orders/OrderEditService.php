<?php

namespace App\Services\Orders;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\OrderItemFinishing;
use App\Models\Product;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class OrderEditService
{
    public function updateDraft(Order $order, array $payload, User $actor): Order
    {
        return DB::transaction(function () use ($order, $payload, $actor) {
            $order = Order::query()->whereKey($order->id)->lockForUpdate()->firstOrFail();

            if ($this->hasIssuedInvoice($order)) {
                throw ValidationException::withMessages([
                    'invoice' => 'This order cannot be edited because an invoice has been issued.',
                ]);
            }

            $meta = is_array($order->meta) ? $order->meta : [];
            $quote = is_array(($meta['quote'] ?? null)) ? ($meta['quote'] ?? []) : [];

            foreach ([
                'valid_until',
                'tax_mode',
                'discount_mode',
                'discount_value',
                'notes_internal',
                'notes_customer',
                'terms',
            ] as $k) {
                if (array_key_exists($k, $payload)) {
                    $quote[$k] = $payload[$k];
                }
            }

            $meta['quote'] = $quote;

            $order->update([
                'customer_id' => isset($payload['customer_id']) ? ($payload['customer_id'] ? (int) $payload['customer_id'] : null) : $order->customer_id,
                'customer_snapshot' => $payload['customer_snapshot'] ?? $order->customer_snapshot,
                'currency' => $payload['currency'] ?? $order->currency,
                'ordered_at' => $payload['ordered_at'] ?? $order->ordered_at,
                'shipping_fee' => isset($payload['shipping_fee']) ? number_format((float) $payload['shipping_fee'], 2, '.', '') : $order->shipping_fee,
                'other_fee' => isset($payload['other_fee']) ? number_format((float) $payload['other_fee'], 2, '.', '') : $order->other_fee,
                'meta' => $meta,
                'updated_by' => $actor->id,
            ]);

            $this->syncItemsFromForm($order, (array) ($payload['items'] ?? []), $actor);
            $this->recalculateTotals($order, $actor);

            return $order->fresh(['items.finishings']);
        });
    }

    public function recalculateTotals(Order $order, User $actor): Order
    {
        return DB::transaction(function () use ($order, $actor) {
            $order = Order::query()->whereKey($order->id)->lockForUpdate()->firstOrFail();
            $order->load(['items.finishings']);

            $subtotal = '0.00';
            $lineDiscountTotal = '0.00';
            $taxTotal = '0.00';

            foreach ($order->items as $it) {
                $lineSubtotal = (string) ($it->line_subtotal ?? 0);
                $lineDiscount = (string) ($it->discount_amount ?? 0);
                $lineTax = (string) ($it->tax_amount ?? 0);

                $finishingsTotal = (string) $it->finishings->sum('total');
                $lineSubtotal = bcadd($lineSubtotal, $finishingsTotal, 2);

                $subtotal = bcadd($subtotal, $lineSubtotal, 2);
                $lineDiscountTotal = bcadd($lineDiscountTotal, $lineDiscount, 2);
                $taxTotal = bcadd($taxTotal, $lineTax, 2);
            }

            $meta = is_array($order->meta) ? $order->meta : [];
            $quote = is_array(($meta['quote'] ?? null)) ? ($meta['quote'] ?? []) : [];

            $quoteMode = (string) ($quote['discount_mode'] ?? 'none');
            $quoteValue = (float) ($quote['discount_value'] ?? 0);
            $quoteValueStr = number_format(max(0, $quoteValue), 2, '.', '');

            // Quote-level discount applies after line discounts.
            $discountBase = bcsub($subtotal, $lineDiscountTotal, 2);
            if (bccomp($discountBase, '0', 2) === -1) {
                $discountBase = '0.00';
            }

            $quoteDiscount = '0.00';
            if ($quoteMode === 'percent') {
                $pct = bcdiv($quoteValueStr, '100', 6);
                $quoteDiscount = bcmul($discountBase, $pct, 2);
            } elseif ($quoteMode === 'amount') {
                $quoteDiscount = (bccomp($quoteValueStr, $discountBase, 2) === 1) ? $discountBase : $quoteValueStr;
            }

            $discountTotal = bcadd($lineDiscountTotal, $quoteDiscount, 2);

            $grand = $subtotal;
            $grand = bcsub($grand, $discountTotal, 2);
            $grand = bcadd($grand, $taxTotal, 2);
            $grand = bcadd($grand, (string) ($order->shipping_fee ?? 0), 2);
            $grand = bcadd($grand, (string) ($order->other_fee ?? 0), 2);

            $order->update([
                'subtotal' => $subtotal,
                'discount_total' => $discountTotal,
                'tax_total' => $taxTotal,
                'grand_total' => $grand,
                'updated_by' => $actor->id,
            ]);

            return $order->fresh();
        });
    }

    private function syncItemsFromForm(Order $order, array $items, User $actor): void
    {
        $order = Order::query()->whereKey($order->id)->lockForUpdate()->firstOrFail();

        $items = array_values($items);

        $productIds = collect($items)
            ->map(fn ($row) => isset($row['product_id']) ? (int) $row['product_id'] : 0)
            ->filter(fn ($id) => $id > 0)
            ->unique()
            ->values();

        $productsById = Product::query()
            ->whereIn('id', $productIds)
            ->get(['id', 'name'])
            ->keyBy('id');

        /** @var \Illuminate\Support\Collection<int,\App\Models\OrderItem> $existingById */
        $existingById = $order->items()->with('finishings')->get()->keyBy('id');

        $keptIds = [];

        foreach ($items as $idx => $row) {
            $itemId = isset($row['id']) ? (int) $row['id'] : 0;
            $productId = isset($row['product_id']) ? (int) $row['product_id'] : 0;
            if ($productId <= 0) {
                throw ValidationException::withMessages(["items.{$idx}.product_id" => 'Product is required.']);
            }

            $qty = max(1, (int) ($row['qty'] ?? 1));

            $unitPrice = is_numeric($row['unit_price'] ?? null) ? number_format((float) $row['unit_price'], 2, '.', '') : '0.00';
            $lineSubtotal = is_numeric($row['line_subtotal'] ?? null) ? number_format((float) $row['line_subtotal'], 2, '.', '') : '0.00';
            $discount = is_numeric($row['discount_amount'] ?? null) ? number_format((float) $row['discount_amount'], 2, '.', '') : '0.00';
            $tax = is_numeric($row['tax_amount'] ?? null) ? number_format((float) $row['tax_amount'], 2, '.', '') : '0.00';

            if (bccomp((string) $discount, (string) $lineSubtotal, 2) === 1) {
                $discount = (string) $lineSubtotal;
            }

            $lineTotal = bcadd(
                bcsub((string) $lineSubtotal, (string) $discount, 2),
                (string) $tax,
                2
            );

            $incomingSnapshot = $row['pricing_snapshot'] ?? null;
            $incomingSnapshot = is_array($incomingSnapshot) ? $incomingSnapshot : [];

            $payload = [
                'order_id' => $order->id,
                'working_group_id' => $order->working_group_id,

                'product_id' => $productId,
                'variant_set_item_id' => isset($row['variant_set_item_id']) ? (int) $row['variant_set_item_id'] : null,
                'roll_id' => isset($row['roll_id']) ? (int) $row['roll_id'] : null,

                'title' => (string) ($row['title'] ?? ($productsById->get($productId)?->name ?? 'Item')),
                'description' => $row['description'] ?? null,

                'qty' => $qty,

                'width' => isset($row['width']) && $row['width'] !== '' ? number_format((float) $row['width'], 3, '.', '') : null,
                'height' => isset($row['height']) && $row['height'] !== '' ? number_format((float) $row['height'], 3, '.', '') : null,
                'unit' => $row['unit'] ?? null,
                'area_sqft' => isset($row['area_sqft']) && $row['area_sqft'] !== '' ? number_format((float) $row['area_sqft'], 4, '.', '') : null,
                'offcut_sqft' => isset($row['offcut_sqft']) && $row['offcut_sqft'] !== '' ? number_format((float) $row['offcut_sqft'], 4, '.', '') : '0.0000',

                'unit_price' => $unitPrice,
                'line_subtotal' => $lineSubtotal,
                'discount_amount' => $discount,
                'tax_amount' => $tax,
                'line_total' => $lineTotal,

                'sort_order' => $idx,
            ];

            $payload['pricing_snapshot'] = array_merge($incomingSnapshot, [
                'source' => 'admin.orders.form',
                'stored_at' => now()->toISOString(),
                'stored_by' => $actor->id,
                'working_group_id' => (int) $order->working_group_id,
                'product_id' => (int) $productId,
                'roll_id' => $payload['roll_id'],
                'area_sqft' => $payload['area_sqft'],
                'offcut_sqft' => $payload['offcut_sqft'],
                'unit_price' => (float) $payload['unit_price'],
                'line_subtotal' => (float) $payload['line_subtotal'],
                'line_total' => (float) $payload['line_total'],
            ]);

            /** @var \App\Models\OrderItem $item */
            $item = null;
            if ($itemId) {
                $item = $existingById->get($itemId);
                if (! $item || (int) $item->order_id !== (int) $order->id) {
                    throw ValidationException::withMessages([
                        'items' => "Invalid order item id: {$itemId}",
                    ]);
                }
                $item->update($payload);
                $keptIds[] = $item->id;
            } else {
                $created = OrderItem::create($payload);
                $item = $created;
                $keptIds[] = $created->id;
            }

            $finRows = $row['finishings'] ?? null;
            if (is_array($finRows) && $item) {
                $item->loadMissing('finishings');
                $finishingsById = $item->finishings->keyBy('id');

                $keptFinishingIds = [];

                foreach (array_values($finRows) as $fIdx => $fRow) {
                    $finId = isset($fRow['id']) ? (int) $fRow['id'] : 0;
                    $finishingProductId = isset($fRow['finishing_product_id']) ? (int) $fRow['finishing_product_id'] : 0;

                    $finRemove = (bool) ($fRow['remove'] ?? false);
                    if ($finRemove && $finId) {
                        $fin = $finishingsById->get($finId);
                        if ($fin) {
                            $fin->delete();
                        }
                        continue;
                    }

                    if ($finishingProductId <= 0) {
                        throw ValidationException::withMessages([
                            "items.{$idx}.finishings.{$fIdx}.finishing_product_id" => 'Finishing product is required.',
                        ]);
                    }

                    $fQty = max(1, (int) ($fRow['qty'] ?? 1));
                    $fUnit = (float) ($fRow['unit_price'] ?? 0);
                    $fTotal = round($fQty * $fUnit, 2);

                    $label = (string) ($fRow['label'] ?? '');
                    if ($label === '') {
                        $label = (string) Product::query()->whereKey($finishingProductId)->value('name');
                    }
                    if ($label === '') {
                        $label = 'Finishing';
                    }

                    if ($finId) {
                        $fin = $finishingsById->get($finId);
                        if (! $fin) {
                            throw ValidationException::withMessages([
                                "items.{$idx}.finishings" => "Invalid finishing id: {$finId}",
                            ]);
                        }

                        $fin->update([
                            'finishing_product_id' => $finishingProductId,
                            'label' => $label,
                            'qty' => $fQty,
                            'unit_price' => number_format($fUnit, 2, '.', ''),
                            'total' => number_format($fTotal, 2, '.', ''),
                            'pricing_snapshot' => array_merge(is_array($fin->pricing_snapshot) ? $fin->pricing_snapshot : [], [
                                'source' => 'admin.orders.form.finishings',
                                'stored_at' => now()->toISOString(),
                                'stored_by' => $actor->id,
                            ]),
                        ]);

                        $keptFinishingIds[] = $fin->id;
                        continue;
                    }

                    $created = OrderItemFinishing::create([
                        'order_item_id' => $item->id,
                        'finishing_product_id' => $finishingProductId,
                        'option_id' => null,
                        'label' => $label,
                        'qty' => $fQty,
                        'unit_price' => number_format($fUnit, 2, '.', ''),
                        'total' => number_format($fTotal, 2, '.', ''),
                        'pricing_snapshot' => [
                            'source' => 'admin.orders.form.finishings',
                            'stored_at' => now()->toISOString(),
                            'stored_by' => $actor->id,
                        ],
                    ]);

                    $keptFinishingIds[] = $created->id;
                }

                // Soft-delete finishings not mentioned (keeps traceability for draft invoices).
                $item->finishings()
                    ->when(! empty($keptFinishingIds), fn ($q) => $q->whereNotIn('id', $keptFinishingIds))
                    ->delete();
            }
        }

        // Soft-delete removed items (keeps traceability for draft invoices).
        $query = OrderItem::query()->where('order_id', $order->id);
        if (! empty($keptIds)) {
            $query->whereNotIn('id', $keptIds);
        }
        $query->delete();
    }

    private function hasIssuedInvoice(Order $order): bool
    {
        return $order->invoices()
            ->whereNull('deleted_at')
            ->whereNotIn('status', ['draft', 'void', 'refunded'])
            ->exists();
    }
}

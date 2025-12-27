<?php

namespace App\Services\Invoices;

use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\InvoiceItemFinishing;
use App\Models\Product;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class InvoiceEditService
{
    /**
     * Mirrors EstimateFlowService behavior: finishings are added into invoice totals.
     */
    public function updateDraft(Invoice $invoice, array $payload, User $actor): Invoice
    {
        return DB::transaction(function () use ($invoice, $payload, $actor) {
            $invoice = Invoice::query()->whereKey($invoice->id)->lockForUpdate()->firstOrFail();

            if ($invoice->status !== 'draft') {
                throw ValidationException::withMessages([
                    'status' => 'Only draft invoices can be edited.',
                ]);
            }

            if ($invoice->locked_at) {
                throw ValidationException::withMessages([
                    'locked' => 'This invoice is locked and cannot be edited.',
                ]);
            }

            if ($invoice->payments()->exists()) {
                throw ValidationException::withMessages([
                    'payments' => 'This invoice already has payment entries; revert payments before editing the draft.',
                ]);
            }

            $invoice->update([
                'customer_snapshot' => $payload['customer_snapshot'] ?? $invoice->customer_snapshot,
                'currency' => $payload['currency'] ?? $invoice->currency,
                'due_at' => $payload['due_at'] ?? $invoice->due_at,
                'shipping_fee' => isset($payload['shipping_fee']) ? number_format((float) $payload['shipping_fee'], 2, '.', '') : $invoice->shipping_fee,
                'other_fee' => isset($payload['other_fee']) ? number_format((float) $payload['other_fee'], 2, '.', '') : $invoice->other_fee,
                'updated_by' => $actor->id,
            ]);

            $this->syncItemsFromForm($invoice, (array) ($payload['items'] ?? []));

            $this->recalculateTotals($invoice, $actor);

            return $invoice->fresh(['items.finishings']);
        });
    }

    public function recalculateTotals(Invoice $invoice, User $actor): Invoice
    {
        $invoice = Invoice::query()->whereKey($invoice->id)->lockForUpdate()->firstOrFail();
        $invoice->load(['items.finishings']);

        $subtotal = '0.00';
        $discountTotal = '0.00';
        $taxTotal = '0.00';

        foreach ($invoice->items as $it) {
            $lineSubtotal = (string) ($it->line_subtotal ?? 0);
            $lineDiscount = (string) ($it->discount_amount ?? 0);
            $lineTax = (string) ($it->tax_amount ?? 0);

            $finishingsTotal = (string) $it->finishings->sum('total');
            $lineSubtotal = bcadd($lineSubtotal, $finishingsTotal, 2);

            $subtotal = bcadd($subtotal, $lineSubtotal, 2);
            $discountTotal = bcadd($discountTotal, $lineDiscount, 2);
            $taxTotal = bcadd($taxTotal, $lineTax, 2);
        }

        $grand = $subtotal;
        $grand = bcsub($grand, $discountTotal, 2);
        $grand = bcadd($grand, $taxTotal, 2);
        $grand = bcadd($grand, (string) ($invoice->shipping_fee ?? 0), 2);
        $grand = bcadd($grand, (string) ($invoice->other_fee ?? 0), 2);

        $invoice->update([
            'subtotal' => $subtotal,
            'discount_total' => $discountTotal,
            'tax_total' => $taxTotal,
            'grand_total' => $grand,
            'amount_paid' => '0.00',
            'amount_due' => $grand,
            'updated_by' => $actor->id,
        ]);

        return $invoice->fresh();
    }

    private function syncItemsFromForm(Invoice $invoice, array $items): void
    {
        $invoice = Invoice::query()->whereKey($invoice->id)->lockForUpdate()->firstOrFail();

        if (count($items) <= 0) {
            throw ValidationException::withMessages([
                'items' => 'An invoice must have at least 1 item.',
            ]);
        }

        $items = array_values($items);

        $productIds = collect($items)
            ->map(fn ($row) => isset($row['product_id']) ? (int) $row['product_id'] : 0)
            ->filter(fn ($id) => $id > 0)
            ->unique()
            ->values();

        $productsById = Product::query()
            ->whereIn('id', $productIds)
            ->get([
                'id',
                'status',
                'product_type',
                'requires_dimensions',
                'min_width_in',
                'max_width_in',
                'min_height_in',
                'max_height_in',
            ])
            ->keyBy('id');

        /** @var \Illuminate\Support\Collection<int,\App\Models\InvoiceItem> $existingById */
        $existingById = $invoice->items()->with('finishings')->get()->keyBy('id');

        $keptIds = [];

        foreach ($items as $idx => $row) {
            $itemId = isset($row['id']) ? (int) $row['id'] : null;

            $productId = (int) ($row['product_id'] ?? 0);
            /** @var Product|null $product */
            $product = $productsById->get($productId);
            if (! $product || $product->status !== 'active' || ! in_array($product->product_type, ['standard', 'dimension_based', 'service'], true)) {
                throw ValidationException::withMessages([
                    'items' => 'One or more selected products are not available.',
                ]);
            }

            $isDimensionBased = (bool) ($product->product_type === 'dimension_based' || $product->requires_dimensions);

            $width = $row['width'] ?? null;
            $height = $row['height'] ?? null;
            $unit = $row['unit'] ?? null;
            $areaSqft = $row['area_sqft'] ?? null;
            $offcutSqft = $row['offcut_sqft'] ?? 0;
            $rollId = isset($row['roll_id']) ? (int) $row['roll_id'] : null;

            if (! $isDimensionBased) {
                $width = null;
                $height = null;
                $unit = null;
                $areaSqft = null;
                $offcutSqft = 0;
                $rollId = null;
            } else {
                if ($width === null || $height === null || $unit === null) {
                    throw ValidationException::withMessages([
                        'items' => 'Width, height and unit are required for dimension-based products.',
                    ]);
                }
            }

            if ($rollId) {
                $allowed = $product->productRolls()
                    ->where('roll_id', $rollId)
                    ->where('is_active', true)
                    ->whereNull('deleted_at')
                    ->exists();

                if (! $allowed) {
                    throw ValidationException::withMessages([
                        'items' => 'Selected roll is not available for one or more items.',
                    ]);
                }
            }

            $qty = (int) ($row['qty'] ?? 1);
            $qty = max(1, $qty);

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

            $incomingOptions = $row['options'] ?? null;
            if (is_array($incomingOptions)) {
                $incomingSnapshot['options'] = $incomingOptions;
                $incomingSnapshot['input'] = array_merge((array) ($incomingSnapshot['input'] ?? []), [
                    'options' => $incomingOptions,
                ]);
            }

            $incomingFinishings = $row['finishings'] ?? null;
            if (is_array($incomingFinishings)) {
                $finishingsMap = [];
                foreach (array_values($incomingFinishings) as $fRow) {
                    if (! is_array($fRow)) continue;
                    if ((bool) ($fRow['remove'] ?? false)) continue;
                    if (array_key_exists('selected', $fRow) && ! (bool) $fRow['selected']) continue;
                    $fid = isset($fRow['finishing_product_id']) ? (int) $fRow['finishing_product_id'] : 0;
                    $qty = isset($fRow['qty']) ? (int) $fRow['qty'] : 0;
                    if ($fid > 0 && $qty > 0) {
                        $finishingsMap[(string) $fid] = $qty;
                    }
                }

                if (! empty($finishingsMap)) {
                    $incomingSnapshot['input'] = array_merge((array) ($incomingSnapshot['input'] ?? []), [
                        'finishings' => $finishingsMap,
                    ]);
                }
            }

            $payload = [
                'invoice_id' => $invoice->id,
                'working_group_id' => $invoice->working_group_id,

                'product_id' => $productId,
                'variant_set_item_id' => isset($row['variant_set_item_id']) ? (int) $row['variant_set_item_id'] : null,
                'roll_id' => $rollId ?: null,

                'title' => (string) ($row['title'] ?? ''),
                'description' => $row['description'] ?? null,

                'qty' => $qty,

                'width' => $width,
                'height' => $height,
                'unit' => $unit,
                'area_sqft' => $areaSqft,
                'offcut_sqft' => $offcutSqft,

                'unit_price' => $unitPrice,
                'line_subtotal' => $lineSubtotal,
                'discount_amount' => $discount,
                'tax_amount' => $tax,
                'line_total' => $lineTotal,

                'sort_order' => $idx,
            ];

            $payload['pricing_snapshot'] = array_merge($incomingSnapshot, [
                'source' => 'admin.invoices.form',
                'stored_at' => now()->toISOString(),
                'stored_by' => Auth::id(),
                'working_group_id' => (int) $invoice->working_group_id,
                'product_id' => (int) $productId,
                'roll_id' => $payload['roll_id'],
                'area_sqft' => $payload['area_sqft'],
                'offcut_sqft' => $payload['offcut_sqft'],
                'unit_price' => (float) $payload['unit_price'],
                'line_total' => (float) $payload['line_total'],
            ]);

            /** @var \App\Models\InvoiceItem|null $item */
            $item = null;
            if ($itemId) {
                $item = $existingById->get($itemId);
                if (! $item || (int) $item->invoice_id !== (int) $invoice->id) {
                    throw ValidationException::withMessages([
                        'items' => "Invalid invoice item id: {$itemId}",
                    ]);
                }
                // Never allow changing traceability fields from UI.
                $payload['order_item_id'] = $item->order_item_id;
                $item->update($payload);
                $keptIds[] = $item->id;
            } else {
                $payload['order_item_id'] = null;
                $created = InvoiceItem::create($payload);
                $item = $created;
                $keptIds[] = $created->id;
            }

            // Finishings: allow add/remove like estimate editor.
            if ($item && array_key_exists('finishings', $row) && is_array($row['finishings'])) {
                $finRows = array_values((array) $row['finishings']);

                /** @var \Illuminate\Support\Collection<int,\App\Models\InvoiceItemFinishing> $existingFinishingsById */
                $existingFinishingsById = $item->finishings()->get()->keyBy('id');

                $keptFinishingIds = [];

                foreach ($finRows as $fRow) {
                    if (! is_array($fRow)) continue;

                    $finId = isset($fRow['id']) ? (int) $fRow['id'] : 0;

                    $remove = (bool) ($fRow['remove'] ?? false);
                    if (array_key_exists('selected', $fRow) && ! (bool) $fRow['selected']) {
                        $remove = true;
                    }

                    if ($remove) {
                        continue; // deleted below by omission
                    }

                    $finishingProductId = isset($fRow['finishing_product_id']) ? (int) $fRow['finishing_product_id'] : 0;
                    if ($finishingProductId <= 0) {
                        continue;
                    }

                    $optionId = isset($fRow['option_id']) ? (int) $fRow['option_id'] : null;
                    $label = isset($fRow['label']) ? (string) $fRow['label'] : null;
                    $label = $label !== '' ? $label : null;

                    $fQty = max(1, (int) ($fRow['qty'] ?? 1));
                    $fUnit = (float) ($fRow['unit_price'] ?? 0);
                    $fTotal = round($fQty * $fUnit, 2);

                    $finSnapshot = $fRow['pricing_snapshot'] ?? null;
                    $finSnapshot = is_array($finSnapshot) ? $finSnapshot : null;

                    if ($finId) {
                        /** @var InvoiceItemFinishing|null $fin */
                        $fin = $existingFinishingsById->get($finId);
                        if (! $fin) {
                            throw ValidationException::withMessages([
                                "items.{$idx}.finishings" => "Invalid finishing id: {$finId}",
                            ]);
                        }

                        $fin->update([
                            'order_item_finishing_id' => $fin->order_item_finishing_id,
                            'finishing_product_id' => $finishingProductId,
                            'option_id' => $optionId ?: null,
                            'label' => $label ?? $fin->label,
                            'qty' => $fQty,
                            'unit_price' => number_format($fUnit, 2, '.', ''),
                            'total' => number_format($fTotal, 2, '.', ''),
                            'pricing_snapshot' => $finSnapshot ?? $fin->pricing_snapshot,
                        ]);

                        $keptFinishingIds[] = $fin->id;
                        continue;
                    }

                    $created = InvoiceItemFinishing::create([
                        'invoice_item_id' => $item->id,
                        'order_item_finishing_id' => null,
                        'finishing_product_id' => $finishingProductId,
                        'option_id' => $optionId ?: null,
                        'label' => $label,
                        'qty' => $fQty,
                        'unit_price' => number_format($fUnit, 2, '.', ''),
                        'total' => number_format($fTotal, 2, '.', ''),
                        'pricing_snapshot' => $finSnapshot,
                    ]);

                    $keptFinishingIds[] = $created->id;
                }

                $del = InvoiceItemFinishing::query()->where('invoice_item_id', $item->id);
                if (! empty($keptFinishingIds)) {
                    $del->whereNotIn('id', $keptFinishingIds);
                }
                $del->delete();
            }
        }

        $query = InvoiceItem::query()->where('invoice_id', $invoice->id);
        if (!empty($keptIds)) {
            $query->whereNotIn('id', $keptIds);
        }
        $query->delete();
    }
}

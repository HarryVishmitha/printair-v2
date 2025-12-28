<?php

namespace App\Services\Orders;

use App\Models\CartItem;
use App\Models\Customer;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\OrderItemFinishing;
use App\Models\Product;
use App\Models\ProductFinishingLink;
use App\Models\User;
use App\Notifications\NewOrderSubmitted;
use App\Services\Cart\CartService;
use App\Services\Pricing\PricingResolverService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class OrderPlacementService
{
    public function __construct(
        private CartService $cart,
        private PricingResolverService $pricing,
    ) {}

    public function placeDraft(array $payload): Order
    {
        $rawToken = Str::random(64);

        $order = DB::transaction(function () use ($payload, $rawToken) {
            $cart = $this->cart->getCart();

            $wgId = (int) ($payload['working_group_id'] ?? (\App\Models\WorkingGroup::getPublicId() ?: 1));

            $customer = $this->resolveCustomerForPlacement($payload);

            $orderNo = $this->generateOrderNo($wgId);

            $order = Order::query()->create([
                'uuid' => (string) Str::uuid(),
                'order_no' => $orderNo,
                'working_group_id' => $wgId,
                'customer_id' => $customer?->id,
                'estimate_id' => null,
                'customer_snapshot' => $payload['customer'] ?? ($customer ? [
                    'id' => $customer->id,
                    'full_name' => $customer->full_name,
                    'email' => $customer->email,
                    'phone' => $customer->phone,
                    'whatsapp_number' => $customer->whatsapp_number,
                ] : null),

                'currency' => 'LKR',

                // totals stay zero until admin confirms/invoices (we still store an initial subtotal)
                'subtotal' => 0,
                'discount_total' => 0,
                'tax_total' => 0,
                'shipping_fee' => 0,
                'other_fee' => 0,
                'grand_total' => 0,

                'status' => 'draft',
                'payment_status' => 'unpaid',
                'ordered_at' => now(),

                'meta' => [
                    'source' => Auth::check() ? 'checkout.auth' : 'checkout.guest',
                    'shipping' => $payload['shipping'] ?? null,
                    'notes' => $payload['notes'] ?? null,
                    'meta' => $payload['meta'] ?? null,
                ],

                'public_token_hash' => hash('sha256', $rawToken),
                'public_token_last_sent_at' => now(),
                'public_token_expires_at' => now()->addDays(14),

                'created_by' => Auth::id() ?: $this->fallbackSystemUserId(),
                'updated_by' => Auth::id() ?: $this->fallbackSystemUserId(),
            ]);

            $subtotal = 0.0;

            if (Auth::check() && !is_array($cart)) {
                $cart->load(['items.product', 'items.finishings.finishingProduct', 'items.files']);

                foreach ($cart->items as $idx => $ci) {
                    $subtotal += $this->copyDbCartItemToOrder($order, $ci, (int) $idx);
                }
            } else {
                foreach (($cart['items'] ?? []) as $idx => $ci) {
                    $subtotal += $this->copySessionCartItemToOrder($order, (array) $ci, (int) $idx);
                }
            }

            $order->update([
                'subtotal' => $subtotal,
                'grand_total' => $subtotal,
                'updated_by' => Auth::id() ?: $this->fallbackSystemUserId(),
            ]);

            $this->cart->clear();

            return $order;
        });

        $secureUrl = route('orders.public.show', ['order' => $order->id, 'token' => $rawToken]);

        $customerEmail = $order->customer_email
            ?? data_get($order->customer_snapshot, 'email')
            ?? null;

        if ($customerEmail) {
            try {
                Mail::send('emails.order-submitted', [
                    'order' => $order,
                    'secureUrl' => $secureUrl,
                ], function ($m) use ($customerEmail) {
                    $m->to($customerEmail)->subject('Printair Order Submitted');
                });
            } catch (\Throwable $e) {
                Log::warning('Order submitted email failed', [
                    'order_id' => $order->id,
                    'to' => $customerEmail,
                    'error' => $e->getMessage(),
                ]);
            }
        }

        try {
            foreach ($this->adminRecipients() as $u) {
                $u->notify(new NewOrderSubmitted($order));
            }
        } catch (\Throwable $e) {
            Log::warning('Order submitted notifications failed', [
                'order_id' => $order->id,
                'error' => $e->getMessage(),
            ]);
        }

        return $order;
    }

    private function copyDbCartItemToOrder(Order $order, CartItem $ci, int $sortOrder): float
    {
        $qty = max(1, (int) ($ci->qty ?? 1));
        $snapshot = is_array($ci->pricing_snapshot) ? $ci->pricing_snapshot : [];

        $grossTotal = $this->extractGrossTotal($snapshot, $qty);

        $title = $ci->product?->name ?? ('Product #'.$ci->product_id);
        $desc = $ci->notes ?: null;

        $artworkFiles = $ci->files?->map(fn ($x) => [
            'disk' => $x->disk,
            'path' => $x->path,
            'name' => $x->original_name,
            'mime' => $x->mime,
            'size_bytes' => $x->size_bytes,
        ])->values()->all();

        $meta = $snapshot['meta'] ?? [];
        if (is_array($artworkFiles) && count($artworkFiles) > 0) {
            $meta['artwork_files'] = $artworkFiles;
        }
        if (!empty($ci->meta['artwork_external_url'] ?? null)) {
            $meta['artwork_external_url'] = $ci->meta['artwork_external_url'];
        }

        $snapshot['meta'] = $meta ?: null;

        $product = $ci->product instanceof Product
            ? $ci->product
            : Product::query()->whereKey((int) $ci->product_id)->first();

        $finishingsMap = $ci->finishings
            ? $ci->finishings->mapWithKeys(fn ($f) => [(int) $f->finishing_product_id => (int) $f->qty])->all()
            : [];

        $fin = $product ? $this->quoteFinishings($order->working_group_id, $product, $finishingsMap) : ['rows' => [], 'total' => 0.0];
        $finishingsTotal = (float) ($fin['total'] ?? 0.0);

        if ($finishingsTotal > 0 && $grossTotal < $finishingsTotal) {
            $grossTotal = $finishingsTotal;
        }

        $lineSubtotal = max(0.0, round($grossTotal - $finishingsTotal, 2));
        $unitPrice = $qty > 0 ? round($lineSubtotal / $qty, 2) : 0.0;

        /** @var \App\Models\OrderItem $oi */
        $oi = OrderItem::create([
            'order_id' => $order->id,
            'working_group_id' => $order->working_group_id,

            'product_id' => $ci->product_id,
            'variant_set_item_id' => $ci->variant_set_item_id,
            'roll_id' => $ci->roll_id,

            'title' => $title,
            'description' => $desc,

            'qty' => $qty,

            'width' => $ci->width,
            'height' => $ci->height,
            'unit' => $ci->unit,
            'area_sqft' => $ci->area_sqft,
            'offcut_sqft' => $ci->offcut_sqft ?? 0,

            'unit_price' => $unitPrice,
            'line_subtotal' => $lineSubtotal,
            'discount_amount' => 0,
            'tax_amount' => 0,
            'line_total' => $lineSubtotal,

            'pricing_snapshot' => array_merge($snapshot ?: [], [
                'source' => 'checkout.order_placement',
                'stored_at' => now()->toISOString(),
                'line_subtotal' => $lineSubtotal,
                'finishings_total' => round($finishingsTotal, 2),
                'total' => round($grossTotal, 2),
            ]),
            'sort_order' => $sortOrder,
        ]);

        foreach (($fin['rows'] ?? []) as $row) {
            OrderItemFinishing::create([
                'order_item_id' => $oi->id,
                'finishing_product_id' => (int) $row['finishing_product_id'],
                'option_id' => null,
                'label' => (string) ($row['label'] ?? 'Finishing'),
                'qty' => (int) $row['qty'],
                'unit_price' => number_format((float) ($row['unit_price'] ?? 0), 2, '.', ''),
                'total' => number_format((float) ($row['total'] ?? 0), 2, '.', ''),
                'pricing_snapshot' => $row['pricing_snapshot'] ?? null,
            ]);
        }

        return (float) $grossTotal;
    }

    private function copySessionCartItemToOrder(Order $order, array $ci, int $sortOrder): float
    {
        $qty = max(1, (int) ($ci['qty'] ?? 1));
        $snapshot = is_array($ci['pricing_snapshot'] ?? null) ? $ci['pricing_snapshot'] : [];

        $grossTotal = $this->extractGrossTotal($snapshot, $qty);

        $title = (string) ($ci['title'] ?? '');
        if ($title === '') {
            $title = (string) \App\Models\Product::query()->whereKey((int) ($ci['product_id'] ?? 0))->value('name');
        }
        if ($title === '') {
            $title = 'Item';
        }

        $meta = $snapshot['meta'] ?? [];
        $metaFromItem = is_array($ci['meta'] ?? null) ? $ci['meta'] : [];
        if (!empty($metaFromItem['artwork_external_url'] ?? null)) {
            $meta['artwork_external_url'] = $metaFromItem['artwork_external_url'];
        }
        $snapshot['meta'] = $meta ?: null;

        $productId = (int) ($ci['product_id'] ?? 0);
        $product = $productId > 0 ? Product::query()->whereKey($productId)->first() : null;

        $finishingsMap = is_array($metaFromItem['finishings'] ?? null) ? (array) $metaFromItem['finishings'] : [];
        if (empty($finishingsMap) && is_array(data_get($snapshot, 'input.finishings'))) {
            $finishingsMap = (array) data_get($snapshot, 'input.finishings');
        }

        $fin = $product ? $this->quoteFinishings($order->working_group_id, $product, $finishingsMap) : ['rows' => [], 'total' => 0.0];
        $finishingsTotal = (float) ($fin['total'] ?? 0.0);

        if ($finishingsTotal > 0 && $grossTotal < $finishingsTotal) {
            $grossTotal = $finishingsTotal;
        }

        $lineSubtotal = max(0.0, round($grossTotal - $finishingsTotal, 2));
        $unitPrice = $qty > 0 ? round($lineSubtotal / $qty, 2) : 0.0;

        /** @var \App\Models\OrderItem $oi */
        $oi = OrderItem::create([
            'order_id' => $order->id,
            'working_group_id' => $order->working_group_id,

            'product_id' => (int) ($ci['product_id'] ?? 0),
            'variant_set_item_id' => isset($ci['variant_set_item_id']) ? (int) $ci['variant_set_item_id'] : null,
            'roll_id' => isset($ci['roll_id']) ? (int) $ci['roll_id'] : null,

            'title' => $title,
            'description' => $ci['notes'] ?? null,

            'qty' => $qty,

            'width' => $ci['width'] ?? null,
            'height' => $ci['height'] ?? null,
            'unit' => $ci['unit'] ?? null,
            'area_sqft' => $ci['area_sqft'] ?? null,
            'offcut_sqft' => $ci['offcut_sqft'] ?? 0,

            'unit_price' => $unitPrice,
            'line_subtotal' => $lineSubtotal,
            'discount_amount' => 0,
            'tax_amount' => 0,
            'line_total' => $lineSubtotal,

            'pricing_snapshot' => array_merge($snapshot ?: [], [
                'source' => 'checkout.order_placement',
                'stored_at' => now()->toISOString(),
                'line_subtotal' => $lineSubtotal,
                'finishings_total' => round($finishingsTotal, 2),
                'total' => round($grossTotal, 2),
            ]),
            'sort_order' => $sortOrder,
        ]);

        foreach (($fin['rows'] ?? []) as $row) {
            OrderItemFinishing::create([
                'order_item_id' => $oi->id,
                'finishing_product_id' => (int) $row['finishing_product_id'],
                'option_id' => null,
                'label' => (string) ($row['label'] ?? 'Finishing'),
                'qty' => (int) $row['qty'],
                'unit_price' => number_format((float) ($row['unit_price'] ?? 0), 2, '.', ''),
                'total' => number_format((float) ($row['total'] ?? 0), 2, '.', ''),
                'pricing_snapshot' => $row['pricing_snapshot'] ?? null,
            ]);
        }

        return (float) $grossTotal;
    }

    private function quoteFinishings(int $workingGroupId, Product $product, array $finishingsInput): array
    {
        $finishingsInput = is_array($finishingsInput) ? $finishingsInput : [];
        if (count($finishingsInput) === 0) {
            return ['rows' => [], 'total' => 0.0];
        }

        $requestedIds = collect($finishingsInput)
            ->keys()
            ->map(fn ($k) => is_numeric($k) ? (int) $k : 0)
            ->filter(fn ($id) => $id > 0)
            ->unique()
            ->values()
            ->all();

        if (count($requestedIds) === 0) {
            return ['rows' => [], 'total' => 0.0];
        }

        $validIds = ProductFinishingLink::query()
            ->where('product_id', $product->id)
            ->where('is_active', true)
            ->whereIn('finishing_product_id', $requestedIds)
            ->pluck('finishing_product_id')
            ->all();

        if (count($validIds) === 0) {
            return ['rows' => [], 'total' => 0.0];
        }

        $finishingProductsById = Product::query()
            ->whereIn('id', $validIds)
            ->get(['id', 'name', 'status', 'product_type'])
            ->keyBy('id');

        $rp = $this->pricing->resolve($product, $workingGroupId);

        $rows = [];
        $total = 0.0;

        foreach ($validIds as $fid) {
            $requestedQty = $finishingsInput[(string) $fid] ?? ($finishingsInput[$fid] ?? 0);
            $requestedQty = is_numeric($requestedQty) ? (int) $requestedQty : 0;
            if ($requestedQty <= 0) {
                continue;
            }

            $unit = null;
            $line = null;
            $mode = null;

            $usedFallback = true;

            if ($rp) {
                $fp = $this->pricing->finishingPricing($rp, (int) $fid);
                if ($fp && $fp->is_active) {
                    if ($fp->price_per_piece !== null) {
                        $unit = (float) $fp->price_per_piece;
                        $line = $unit * $requestedQty;
                        $mode = 'per_piece';
                        $usedFallback = false;
                    } elseif ($fp->price_per_side !== null) {
                        $unit = (float) $fp->price_per_side;
                        $line = $unit * $requestedQty;
                        $mode = 'per_side';
                        $usedFallback = false;
                    } elseif ($fp->flat_price !== null) {
                        $unit = (float) $fp->flat_price;
                        $line = (float) $fp->flat_price;
                        $mode = 'flat';
                        $usedFallback = false;
                    }
                }
            }

            if ($usedFallback) {
                $finishingProduct = $finishingProductsById->get((int) $fid);
                if (! $finishingProduct || $finishingProduct->status !== 'active') {
                    continue;
                }

                $frp = $this->pricing->resolve($finishingProduct, $workingGroupId);
                if (! $frp) {
                    continue;
                }

                $unitFallback = $this->pricing->baseUnitPrice($frp, $requestedQty);
                if ($unitFallback === null) {
                    continue;
                }

                $unit = (float) $unitFallback;
                $line = $unit * $requestedQty;
                $mode = 'fallback_unit';
            }

            $line = (float) ($line ?? 0);
            $unit = (float) ($unit ?? 0);

            $total += $line;
            $rows[] = [
                'finishing_product_id' => (int) $fid,
                'label' => (string) ($finishingProductsById->get((int) $fid)?->name ?? ('Finishing #' . $fid)),
                'qty' => $requestedQty,
                'unit_price' => round($unit, 2),
                'total' => round($line, 2),
                'pricing_snapshot' => [
                    'source' => 'checkout.order_placement.finishings',
                    'mode' => $mode,
                    'qty' => $requestedQty,
                    'unit_price' => $unit,
                    'total' => $line,
                    'captured_at' => now()->toISOString(),
                ],
            ];
        }

        return [
            'rows' => $rows,
            'total' => round($total, 2),
        ];
    }

    private function extractGrossTotal(array $snapshot, int $qty): float
    {
        $candidates = [
            $snapshot['total'] ?? null,
            data_get($snapshot, 'data.total'),
            $snapshot['line_total'] ?? null,
            data_get($snapshot, 'data.line_total'),
        ];

        foreach ($candidates as $c) {
            if ($c === null || $c === '') {
                continue;
            }
            if (is_numeric($c)) {
                return round((float) $c, 2);
            }
        }

        $unit = $snapshot['unit_price'] ?? data_get($snapshot, 'data.unit_price');
        if ($unit !== null && $unit !== '' && is_numeric($unit)) {
            return round((float) $unit * max(1, $qty), 2);
        }

        return 0.0;
    }

    private function generateOrderNo(int $wgId): string
    {
        $date = now()->format('Ymd');

        for ($i = 0; $i < 5; $i++) {
            $rand = str_pad((string) random_int(1, 999999), 6, '0', STR_PAD_LEFT);
            $no = "ORD-WG{$wgId}-{$date}-{$rand}";

            if (! Order::query()->where('working_group_id', $wgId)->where('order_no', $no)->exists()) {
                return $no;
            }
        }

        throw ValidationException::withMessages([
            'order_no' => 'Unable to generate a unique order number. Please try again.',
        ]);
    }

    private function fallbackSystemUserId(): int
    {
        $id = (int) User::query()->orderBy('id')->value('id');

        return $id > 0 ? $id : 1;
    }

    private function resolveCustomerForPlacement(array $payload): ?Customer
    {
        $email = (string) data_get($payload, 'customer.email', '');
        if ($email === '') {
            return null;
        }

        $guestCustomerId = (int) session()->get('guest_customer_id', 0);
        if (!Auth::check() && $guestCustomerId > 0) {
            $c = Customer::query()->whereKey($guestCustomerId)->first();
            if ($c) {
                return $c;
            }
        }

        return Customer::query()->where('email', $email)->orderByDesc('id')->first();
    }

    private function adminRecipients()
    {
        return User::query()
            ->where('status', 'active')
            ->whereHas('role', fn ($r) => $r->where('is_staff', true))
            ->get();
    }
}

<?php

namespace App\Services\Orders;

use App\Models\CartItem;
use App\Models\Customer;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\OrderItemFinishing;
use App\Models\User;
use App\Notifications\NewOrderSubmitted;
use App\Services\Cart\CartService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class OrderPlacementService
{
    public function __construct(private CartService $cart) {}

    public function placeDraft(array $payload): Order
    {
        return DB::transaction(function () use ($payload) {
            $cart = $this->cart->getCart();

            $wgId = (int) ($payload['working_group_id'] ?? (\App\Models\WorkingGroup::getPublicId() ?: 1));

            $customer = $this->resolveCustomerForPlacement($payload);

            $orderNo = $this->generateOrderNo($wgId);

            $rawToken = Str::random(64);
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

            $secureUrl = route('orders.public.show', ['order' => $order->id, 'token' => $rawToken]);

            if ($customer?->email) {
                Mail::send('emails.order-submitted', [
                    'order' => $order,
                    'secureUrl' => $secureUrl,
                ], function ($m) use ($customer) {
                    $m->to($customer->email)->subject('Printair Order Submitted');
                });
            }

            foreach ($this->adminRecipients() as $u) {
                $u->notify(new NewOrderSubmitted($order));
            }

            $this->cart->clear();

            return $order;
        });
    }

    private function copyDbCartItemToOrder(Order $order, CartItem $ci, int $sortOrder): float
    {
        $qty = max(1, (int) ($ci->qty ?? 1));
        $snapshot = is_array($ci->pricing_snapshot) ? $ci->pricing_snapshot : [];

        $lineTotal = $this->extractLineTotal($snapshot, $qty);
        $unitPrice = $qty > 0 ? round($lineTotal / $qty, 2) : 0.0;

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
            'line_subtotal' => $lineTotal,
            'discount_amount' => 0,
            'tax_amount' => 0,
            'line_total' => $lineTotal,

            'pricing_snapshot' => $snapshot ?: [],
            'sort_order' => $sortOrder,
        ]);

        foreach ($ci->finishings as $f) {
            $finishingName = $f->finishingProduct?->name ?? 'Finishing';

            OrderItemFinishing::create([
                'order_item_id' => $oi->id,
                'finishing_product_id' => $f->finishing_product_id,
                'option_id' => null,
                'label' => $finishingName,
                'qty' => (int) ($f->qty ?? 1),
                'unit_price' => 0,
                'total' => 0,
                'pricing_snapshot' => $f->pricing_snapshot ?? null,
            ]);
        }

        return (float) $lineTotal;
    }

    private function copySessionCartItemToOrder(Order $order, array $ci, int $sortOrder): float
    {
        $qty = max(1, (int) ($ci['qty'] ?? 1));
        $snapshot = is_array($ci['pricing_snapshot'] ?? null) ? $ci['pricing_snapshot'] : [];

        $lineTotal = $this->extractLineTotal($snapshot, $qty);
        $unitPrice = $qty > 0 ? round($lineTotal / $qty, 2) : 0.0;

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
            'line_subtotal' => $lineTotal,
            'discount_amount' => 0,
            'tax_amount' => 0,
            'line_total' => $lineTotal,

            'pricing_snapshot' => $snapshot ?: [],
            'sort_order' => $sortOrder,
        ]);

        $finishingsMap = [];
        $metaFromItem = is_array($ci['meta'] ?? null) ? $ci['meta'] : [];
        if (is_array($metaFromItem['finishings'] ?? null)) {
            $finishingsMap = $metaFromItem['finishings'];
        } elseif (is_array(data_get($snapshot, 'input.finishings'))) {
            $finishingsMap = (array) data_get($snapshot, 'input.finishings');
        }

        $finishingIds = collect($finishingsMap)
            ->keys()
            ->map(fn ($k) => is_numeric($k) ? (int) $k : 0)
            ->filter(fn ($id) => $id > 0)
            ->unique()
            ->values()
            ->all();

        if (count($finishingIds) > 0) {
            $names = \App\Models\Product::query()
                ->whereIn('id', $finishingIds)
                ->pluck('name', 'id')
                ->all();

            foreach ($finishingsMap as $fid => $qtyRaw) {
                $fid = is_numeric($fid) ? (int) $fid : 0;
                if ($fid <= 0) {
                    continue;
                }

                $qty = is_numeric($qtyRaw) ? (int) $qtyRaw : 0;
                if ($qty <= 0) {
                    continue;
                }

                $label = (string) ($names[$fid] ?? 'Finishing');

                OrderItemFinishing::create([
                    'order_item_id' => $oi->id,
                    'finishing_product_id' => $fid,
                    'option_id' => null,
                    'label' => $label,
                    'qty' => $qty,
                    'unit_price' => 0,
                    'total' => 0,
                    'pricing_snapshot' => null,
                ]);
            }
        }

        return (float) $lineTotal;
    }

    private function extractLineTotal(array $snapshot, int $qty): float
    {
        $candidates = [
            $snapshot['line_total'] ?? null,
            $snapshot['total'] ?? null,
            data_get($snapshot, 'data.line_total'),
            data_get($snapshot, 'data.total'),
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

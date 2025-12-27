<?php

namespace App\Services\Orders;

use App\Models\Estimate;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\OrderItemFinishing;
use App\Models\OrderStatusHistory;
use App\Models\User;
use App\Services\Invoices\InvoiceFlowService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class OrderFlowService
{
    public function __construct(
        private readonly InvoiceFlowService $invoices,
    ) {}

    public function createFromEstimate(Estimate $estimate, array $meta = []): Order
    {
        $actor = $this->actor();

        // Optional policy enforcement
        if (Gate::has('convertToOrder')) {
            Gate::authorize('convertToOrder', $estimate);
        }

        return DB::transaction(function () use ($estimate, $actor, $meta) {
            // Lock estimate row to prevent double conversions
            $estimate = Estimate::query()->whereKey($estimate->id)->lockForUpdate()->firstOrFail();

            if ($estimate->status !== 'accepted') {
                throw ValidationException::withMessages([
                    'status' => 'Only an accepted estimate can be converted to an order.',
                ]);
            }

            // One Estimate -> One Order (enforced)
            $order = Order::query()->where('estimate_id', $estimate->id)->first();
            $orderNo = $order?->order_no;

            if (! $order) {
                $orderNo = $this->generateOrderNo($estimate->working_group_id);

                $order = Order::create([
                    'uuid' => (string) Str::uuid(),
                    'order_no' => $orderNo,

                    'working_group_id' => $estimate->working_group_id,

                    'customer_id' => $estimate->customer_id,
                    'estimate_id' => $estimate->id,
                    'customer_snapshot' => $estimate->customer_snapshot,

                    'currency' => $estimate->currency,

                    'subtotal' => $estimate->subtotal,
                    'discount_total' => $estimate->discount_total,
                    'tax_total' => $estimate->tax_total,
                    'shipping_fee' => $estimate->shipping_fee,
                    'other_fee' => $estimate->other_fee,
                    'grand_total' => $estimate->grand_total,

                    'status' => 'draft',
                    'payment_status' => 'unpaid',

                    'ordered_at' => now(),

                    'meta' => $meta ?: null,

                    'created_by' => $actor->id,
                    'updated_by' => $actor->id,
                ]);

                // Copy items + finishings as immutable snapshots
                $estimate->load(['items.finishings']);

                foreach ($estimate->items as $ei) {
                    $oi = OrderItem::create([
                        'order_id' => $order->id,
                        'working_group_id' => $order->working_group_id,

                        'product_id' => $ei->product_id,
                        'variant_set_item_id' => $ei->variant_set_item_id,
                        'roll_id' => $ei->roll_id,

                        'title' => $ei->title,
                        'description' => $ei->description,

                        'qty' => $ei->qty,

                        'width' => $ei->width,
                        'height' => $ei->height,
                        'unit' => $ei->unit,
                        'area_sqft' => $ei->area_sqft,
                        'offcut_sqft' => $ei->offcut_sqft,

                        'unit_price' => $ei->unit_price,
                        'line_subtotal' => $ei->line_subtotal,
                        'discount_amount' => $ei->discount_amount,
                        'tax_amount' => $ei->tax_amount,
                        'line_total' => $ei->line_total,

                        'pricing_snapshot' => $ei->pricing_snapshot,
                        'sort_order' => $ei->sort_order,
                    ]);

                    foreach ($ei->finishings as $ef) {
                        OrderItemFinishing::create([
                            'order_item_id' => $oi->id,
                            'finishing_product_id' => $ef->finishing_product_id,
                            'option_id' => $ef->option_id,
                            'label' => $ef->label,
                            'qty' => $ef->qty,
                            'unit_price' => $ef->unit_price,
                            'total' => $ef->total,
                            'pricing_snapshot' => $ef->pricing_snapshot,
                        ]);
                    }
                }

                $this->logStatusChange($order, null, 'draft', $actor, 'Order created from estimate', [
                    'estimate_id' => $estimate->id,
                    'estimate_no' => $estimate->estimate_no,
                    'order_no' => $orderNo,
                ]);

                $this->activity('order.created', $actor, $order, [
                    'from_estimate_id' => $estimate->id,
                    'from_estimate_no' => $estimate->estimate_no,
                    'order_no' => $orderNo,
                ]);
            }

            // Update estimate status to converted (lock stays)
            $estimate->update([
                'status' => 'converted',
                'converted_at' => now(),
                'updated_by' => $actor->id,
            ]);

            $this->activity('estimate.converted', $actor, $estimate, [
                'order_id' => $order->id,
                'order_no' => $orderNo,
            ]);

            // Ensure a final invoice is created + issued (estimate is treated as final pricing).
            $invoice = $order->invoices()->where('type', 'final')->first();
            if (! $invoice) {
                $invoice = $this->invoices->createFromOrder($order, 'final', [
                    'source' => 'estimate_conversion',
                    'estimate_id' => $estimate->id,
                    'estimate_no' => $estimate->estimate_no,
                    'order_no' => $orderNo,
                ]);
            }

            if ((string) $invoice->status === 'draft') {
                $invoice = $this->invoices->issue($invoice, [
                    'reason' => $meta['reason'] ?? 'Auto-issued from estimate conversion',
                    'source' => 'estimate_conversion',
                    'estimate_id' => $estimate->id,
                    'estimate_no' => $estimate->estimate_no,
                    'order_no' => $orderNo,
                ]);
            }

            return $order->fresh(['items.finishings', 'invoices']);
        });
    }

    public function confirm(Order $order, array $meta = []): Order
    {
        $actor = $this->actor();

        if (Gate::has('confirm')) {
            Gate::authorize('confirm', $order);
        }

        return DB::transaction(function () use ($order, $actor, $meta) {
            $order = Order::query()->whereKey($order->id)->lockForUpdate()->firstOrFail();

            if (! in_array($order->status, ['draft'], true)) {
                throw ValidationException::withMessages([
                    'status' => 'Only draft orders can be confirmed.',
                ]);
            }

            $from = $order->status;

            $order->update([
                'status' => 'confirmed',
                'confirmed_at' => now(),
                'locked_at' => now(),
                'locked_by' => $actor->id,
                'updated_by' => $actor->id,
            ]);

            $this->logStatusChange($order, $from, 'confirmed', $actor, $meta['reason'] ?? 'Order confirmed', $meta);
            $this->activity('order.confirmed', $actor, $order, $meta);

            return $order->fresh();
        });
    }

    public function changeStatus(Order $order, string $toStatus, array $meta = []): Order
    {
        $actor = $this->actor();

        if (Gate::has('changeStatus')) {
            Gate::authorize('changeStatus', [$order, $toStatus]);
        }

        return DB::transaction(function () use ($order, $actor, $toStatus, $meta) {
            $order = Order::query()->whereKey($order->id)->lockForUpdate()->firstOrFail();

            $from = $order->status;

            if ($from === $toStatus) {
                return $order;
            }

            // Confirm is a dedicated workflow (sets confirmed_at + document lock)
            if ($from === 'draft') {
                throw ValidationException::withMessages([
                    'status' => 'Confirm the order before changing its production/delivery status.',
                ]);
            }

            // Basic guard: don’t change after cancelled/refunded/completed unless you allow it
            if (in_array($from, ['cancelled', 'refunded', 'completed'], true)) {
                throw ValidationException::withMessages([
                    'status' => 'This order is in a terminal state and cannot be changed.',
                ]);
            }

            $fromKey = (string) $from;
            if ($fromKey === 'processing') {
                // legacy alias
                $fromKey = 'in_production';
            }

            $transitions = [
                'confirmed' => ['in_production', 'cancelled', 'refunded'],
                'in_production' => ['confirmed', 'ready', 'cancelled', 'refunded'],
                'ready' => ['in_production', 'out_for_delivery', 'cancelled', 'refunded'],
                'out_for_delivery' => ['ready', 'completed', 'cancelled', 'refunded'],
            ];

            if (! isset($transitions[$fromKey]) || ! in_array($toStatus, $transitions[$fromKey], true)) {
                throw ValidationException::withMessages([
                    'status' => "Invalid status transition from {$from} to {$toStatus}.",
                ]);
            }

            $patch = [
                'status' => $toStatus,
                'updated_by' => $actor->id,
            ];

            if ($toStatus === 'completed') {
                $patch['completed_at'] = now();
            }
            if ($toStatus === 'cancelled') {
                $patch['cancelled_at'] = now();
            }

            $order->update($patch);

            $this->logStatusChange($order, $from, $toStatus, $actor, $meta['reason'] ?? null, $meta);
            $this->activity('order.status_changed', $actor, $order, [
                'from' => $from,
                'to' => $toStatus,
            ] + $meta);

            return $order->fresh();
        });
    }

    // ---------------- internals ----------------

    private function actor(): User
    {
        $u = Auth::user();
        if (! $u instanceof User) {
            throw ValidationException::withMessages(['auth' => 'Unauthorized.']);
        }
        return $u;
    }

    private function logStatusChange(Order $order, ?string $from, string $to, User $actor, ?string $reason = null, array $meta = []): void
    {
        OrderStatusHistory::create([
            'order_id' => $order->id,
            'from_status' => $from,
            'to_status' => $to,
            'changed_by' => $actor->id,
            'reason' => $reason,
            'meta' => $meta ?: null,
            'created_at' => now(),
        ]);
    }

    private function activity(string $action, User $actor, $subject, array $properties = []): void
    {
        // Wire into your activity_logs here later (same as EstimateFlowService).
        // Keep as hook so controllers remain thin.
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
}

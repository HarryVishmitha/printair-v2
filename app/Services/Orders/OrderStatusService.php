<?php

namespace App\Services\Orders;

use App\Models\Order;
use App\Models\OrderStatusHistory;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class OrderStatusService
{
    /**
     * Minimal transition map (single source of truth).
     */
    private array $transitions = [
        'draft' => ['confirmed', 'cancelled'],
        'confirmed' => ['in_production', 'cancelled'],
        'in_production' => ['ready', 'cancelled'],
        'ready' => ['out_for_delivery', 'completed', 'cancelled'],
        'out_for_delivery' => ['completed', 'refunded'],
        'completed' => ['refunded'],
        'cancelled' => [],
        'refunded' => [],
    ];

    public function nextStatusesFor(Order $order, User $actor): array
    {
        $order->loadMissing('invoices');

        $finalInvoice = $order->invoices?->firstWhere('type', 'final');
        $finalInvoiceIssued = $finalInvoice && ! in_array((string) $finalInvoice->status, ['draft', 'void', 'cancelled'], true);

        if ($finalInvoiceIssued && ! ($actor->can('orders.override_after_invoice') || $actor->can('manage-orderFlow'))) {
            return [];
        }

        return $this->nextStatuses($order);
    }

    public function nextStatuses(Order $order): array
    {
        $from = (string) ($order->status ?? 'draft');
        $from = $this->normalizeStatus($from);

        $next = $this->transitions[$from] ?? [];

        $shippingMethod = (string) data_get($order->meta, 'shipping.method', 'pickup');
        if ($shippingMethod === 'pickup') {
            $next = array_values(array_filter($next, fn ($s) => $s !== 'out_for_delivery'));
        }

        return $next;
    }

    public function changeStatus(Order $order, array $payload, User $actor): Order
    {
        return DB::transaction(function () use ($order, $payload, $actor) {
            $order = Order::query()->whereKey($order->id)->lockForUpdate()->firstOrFail();
            $order->loadMissing('invoices');

            $from = (string) ($order->status ?? 'draft');
            $fromNorm = $this->normalizeStatus($from);
            $to = (string) ($payload['status'] ?? '');

            if ($to === '') {
                throw ValidationException::withMessages(['status' => 'Status is required.']);
            }

            if ($fromNorm === $to) {
                return $order;
            }

            $shippingMethod = (string) data_get($order->meta, 'shipping.method', 'pickup');

            // Pickup orders cannot go out_for_delivery
            if ($shippingMethod === 'pickup' && $to === 'out_for_delivery') {
                throw ValidationException::withMessages(['status' => 'Pickup orders cannot be set to Out for delivery.']);
            }

            // Transition validation
            $allowed = $this->transitions[$fromNorm] ?? [];
            if (! in_array($to, $allowed, true)) {
                throw ValidationException::withMessages(['status' => "Invalid transition: {$from} -> {$to}"]);
            }

            $finalInvoice = $order->invoices?->firstWhere('type', 'final');
            $finalInvoiceIssued = $finalInvoice && ! in_array((string) $finalInvoice->status, ['draft', 'void', 'cancelled'], true);

            $why = $payload['why'] ?? null;
            $why = is_string($why) ? trim($why) : null;
            if ($why === '') $why = null;

            if (in_array($to, ['cancelled', 'refunded'], true) && ! $why) {
                throw ValidationException::withMessages(['why' => 'Why is required for Cancelled/Refunded.']);
            }

            if ($finalInvoiceIssued) {
                $canOverride = $actor->can('orders.override_after_invoice') || $actor->can('manage-orderFlow');
                if (! $canOverride) {
                    throw ValidationException::withMessages([
                        'status' => 'You are not allowed to change status after a final invoice is issued.',
                    ]);
                }
                if (! $why) {
                    throw ValidationException::withMessages(['why' => 'Why is required because a final invoice was issued.']);
                }
            }

            $trackingNo = $payload['tracking_no'] ?? null;
            $trackingNo = is_string($trackingNo) ? trim($trackingNo) : null;
            if ($trackingNo === '') $trackingNo = null;

            $vehicleNote = $payload['vehicle_note'] ?? null;
            $vehicleNote = is_string($vehicleNote) ? trim($vehicleNote) : null;
            if ($vehicleNote === '') $vehicleNote = null;

            $pickupNote = $payload['pickup_note'] ?? null;
            $pickupNote = is_string($pickupNote) ? trim($pickupNote) : null;
            if ($pickupNote === '') $pickupNote = null;

            if ($shippingMethod === 'delivery' && $to === 'out_for_delivery') {
                if (! $trackingNo && ! $vehicleNote) {
                    throw ValidationException::withMessages([
                        'tracking_no' => 'Tracking number or vehicle note is required for Out for delivery.',
                    ]);
                }
            }

            // Apply status
            $patch = [
                'status' => $to,
                'updated_by' => $actor->id,
            ];

            if ($fromNorm === 'draft' && $to === 'confirmed') {
                $patch['confirmed_at'] = now();
                $patch['locked_at'] = now();
                $patch['locked_by'] = $actor->id;
            }

            if ($to === 'completed' && empty($order->completed_at)) {
                $patch['completed_at'] = now();
            }
            if ($to === 'cancelled' && empty($order->cancelled_at)) {
                $patch['cancelled_at'] = now();
            }
            if ($to === 'refunded' && Schema::hasColumn('orders', 'refunded_at') && empty($order->refunded_at)) {
                $patch['refunded_at'] = now();
            }

            $order->update($patch);

            OrderStatusHistory::create([
                'order_id' => $order->id,
                'from_status' => $from,
                'to_status' => $to,
                'changed_by' => $actor->id,
                'reason' => $why ? Str::limit($why, 500) : null,
                'why' => $why,
                'tracking_no' => $trackingNo,
                'vehicle_note' => $vehicleNote,
                'pickup_note' => $pickupNote,
                'meta' => [
                    'shipping_method' => $shippingMethod,
                    'final_invoice_issued' => $finalInvoiceIssued,
                    'ip' => request()?->ip(),
                    'user_agent' => substr((string) request()?->userAgent(), 0, 255),
                ],
                'created_at' => now(),
            ]);

            return $order->fresh();
        });
    }

    private function normalizeStatus(string $status): string
    {
        // Legacy alias support
        if ($status === 'processing') {
            return 'in_production';
        }

        return $status;
    }
}


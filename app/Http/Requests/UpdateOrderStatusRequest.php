<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateOrderStatusRequest extends FormRequest
{
    public function authorize(): bool
    {
        $order = $this->route('order');
        return $order ? ($this->user()?->can('changeStatus', $order) ?? false) : false;
    }

    public function rules(): array
    {
        $statuses = ['draft', 'confirmed', 'in_production', 'ready', 'out_for_delivery', 'completed', 'cancelled', 'refunded'];

        return [
            'status' => ['required', 'string', Rule::in($statuses)],
            'why' => ['nullable', 'string', 'min:5', 'max:2000'],

            'tracking_no' => ['nullable', 'string', 'max:120'],
            'vehicle_note' => ['nullable', 'string', 'max:2000'],
            'pickup_note' => ['nullable', 'string', 'max:2000'],
        ];
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($v) {
            $order = $this->route('order');
            if (! $order) {
                return;
            }

            $to = (string) $this->input('status');

            $shippingMethod = (string) data_get($order->meta, 'shipping.method', 'pickup');

            $order->loadMissing('invoices');
            $finalInvoice = $order->invoices?->firstWhere('type', 'final');
            $finalInvoiceIssued = $finalInvoice && ! in_array((string) $finalInvoice->status, ['draft', 'void', 'cancelled'], true);

            // Pickup orders cannot go out_for_delivery
            if ($shippingMethod === 'pickup' && $to === 'out_for_delivery') {
                $v->errors()->add('status', 'Pickup orders cannot be set to Out for delivery.');
            }

            // Delivery out_for_delivery requires tracking no or vehicle note
            if ($shippingMethod === 'delivery' && $to === 'out_for_delivery') {
                if (! filled($this->input('tracking_no')) && ! filled($this->input('vehicle_note'))) {
                    $v->errors()->add('tracking_no', 'Tracking number or vehicle note is required for Out for delivery.');
                }
            }

            // Cancel/Refund must include why
            if (in_array($to, ['cancelled', 'refunded'], true) && ! filled($this->input('why'))) {
                $v->errors()->add('why', 'Why is required for Cancelled/Refunded.');
            }

            // After final invoice: require why + permission (fallback to manage-orderFlow)
            if ($finalInvoiceIssued) {
                if (! filled($this->input('why'))) {
                    $v->errors()->add('why', 'Why is required because a final invoice was issued.');
                }

                $u = $this->user();
                $canOverride = $u && ($u->can('orders.override_after_invoice') || $u->can('manage-orderFlow'));
                if (! $canOverride) {
                    $v->errors()->add('status', 'You are not allowed to change status after a final invoice is issued.');
                }
            }
        });
    }
}


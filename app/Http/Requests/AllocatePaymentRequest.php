<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class AllocatePaymentRequest extends FormRequest
{
    public function authorize(): bool
    {
        $payment = $this->route('payment');
        $invoice = $this->route('invoice');

        return ($payment && $invoice)
            ? ($this->user()?->can('allocate', [$payment, $invoice]) ?? false)
            : false;
    }

    public function rules(): array
    {
        return [
            'amount' => ['required', 'numeric', 'min:0.01'],
            'reason' => ['nullable', 'string', 'max:500'],
        ];
    }
}


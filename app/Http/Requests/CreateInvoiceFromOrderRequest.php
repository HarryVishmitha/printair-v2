<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CreateInvoiceFromOrderRequest extends FormRequest
{
    public function authorize(): bool
    {
        $order = $this->route('order');
        return $order ? ($this->user()?->can('createInvoice', $order) ?? false) : false;
    }

    public function rules(): array
    {
        return [
            'type' => ['nullable', 'string', 'max:20'], // final|partial|credit_note
            'reason' => ['nullable', 'string', 'max:500'],
        ];
    }
}


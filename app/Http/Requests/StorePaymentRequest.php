<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StorePaymentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('create', \App\Models\Payment::class) ?? false;
    }

    public function rules(): array
    {
        return [
            'working_group_id' => ['required', 'integer'],
            'customer_id' => ['nullable', 'integer'],

            'method' => ['required', 'string', 'max:40'],
            'status' => ['nullable', 'string', 'max:40'], // pending|confirmed|failed|void...
            'amount' => ['required', 'numeric', 'min:0.01'],
            'currency' => ['nullable', 'string', 'max:10'],

            'reference_no' => ['nullable', 'string', 'max:100'],
            'received_at' => ['nullable', 'date'],
            'received_by' => ['nullable', 'integer'],

            'meta' => ['nullable', 'array'],
        ];
    }
}

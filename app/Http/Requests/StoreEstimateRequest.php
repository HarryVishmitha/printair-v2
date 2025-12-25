<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreEstimateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('create', \App\Models\Estimate::class) ?? false;
    }

    public function rules(): array
    {
        return [
            'working_group_id' => ['required', 'integer'],
            'customer_id' => ['nullable', 'integer', 'exists:customers,id'],
            'customer_snapshot' => ['nullable', 'array'],

            'currency' => ['nullable', 'string', 'max:10'],
            'price_tier_id' => ['nullable', 'integer'],

            'tax_mode' => ['nullable', 'in:none,inclusive,exclusive'],
            'discount_mode' => ['nullable', 'in:none,percent,amount'],
            'discount_value' => ['nullable', 'numeric', 'min:0'],

            'valid_until' => ['nullable', 'date'],
            'notes_internal' => ['nullable', 'string'],
            'notes_customer' => ['nullable', 'string'],
            'terms' => ['nullable', 'string'],
            'meta' => ['nullable', 'array'],
        ];
    }
}

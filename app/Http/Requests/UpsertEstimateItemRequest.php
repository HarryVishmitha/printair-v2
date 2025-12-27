<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpsertEstimateItemRequest extends FormRequest
{
    public function authorize(): bool
    {
        $estimate = $this->route('estimate');
        return $estimate ? ($this->user()?->can('update', $estimate) ?? false) : false;
    }

    public function rules(): array
    {
        return [
            'product_id' => ['required', 'integer'],
            'options' => ['nullable', 'array', 'max:60'],
            'options.*' => ['nullable', 'integer'],
            // Back-compat (older UI)
            'variant_set_item_id' => ['nullable', 'integer'],
            'roll_id' => ['nullable', 'integer'],

            'title' => ['required', 'string', 'max:200'],
            'description' => ['nullable', 'string'],

            'qty' => ['required', 'integer', 'min:1'],

            'width' => ['nullable', 'numeric', 'min:0'],
            'height' => ['nullable', 'numeric', 'min:0'],
            'unit' => ['nullable', 'string', 'max:20'],
            'area_sqft' => ['nullable', 'numeric', 'min:0'],
            'offcut_sqft' => ['nullable', 'numeric', 'min:0'],

            'unit_price' => ['required', 'numeric', 'min:0'],
            'line_subtotal' => ['required', 'numeric', 'min:0'],
            'discount_amount' => ['nullable', 'numeric', 'min:0'],
            'tax_amount' => ['nullable', 'numeric', 'min:0'],
            'line_total' => ['required', 'numeric', 'min:0'],

            'pricing_snapshot' => ['nullable', 'array'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
        ];
    }
}

<?php

namespace App\Http\Requests;

use App\Models\ProductPricing;
use Illuminate\Foundation\Http\FormRequest;

class StoreProductFinishingPricingRequest extends FormRequest
{
    public function authorize(): bool
    {
        /** @var ProductPricing $pricing */
        $pricing = $this->route('pricing');

        return $this->user()?->can('manageFinishingPricing', $pricing) ?? false;
    }

    public function rules(): array
    {
        return [
            'finishing_product_id' => [
                'required',
                'integer',
                'exists:products,id',
            ],

            // Pricing modes
            'price_per_piece' => ['nullable', 'numeric', 'min:0'],
            'price_per_side' => ['nullable', 'numeric', 'min:0'],
            'flat_price' => ['nullable', 'numeric', 'min:0'],

            // Quantity constraints
            'min_qty' => ['nullable', 'integer', 'min:1'],
            'max_qty' => ['nullable', 'integer', 'min:1'],

            'is_active' => ['boolean'],
        ];
    }

    public function withValidator($validator)
    {
        $validator->after(function ($v) {
            /** @var ProductPricing $pricing */
            $pricing = $this->route('pricing');
            $data = $this->validated();

            /*
            |--------------------------------------------------------------------------
            | Override enforcement
            |--------------------------------------------------------------------------
            */
            if (! $pricing->override_finishings) {
                $v->errors()->add('finishing_product_id', 'Finishing pricing overrides are disabled.');
            }

            /*
            |--------------------------------------------------------------------------
            | At least one pricing mode required
            |--------------------------------------------------------------------------
            */
            $hasAny =
                ! empty($data['price_per_piece']) ||
                ! empty($data['price_per_side']) ||
                ! empty($data['flat_price']);

            if (! $hasAny) {
                $v->errors()->add('price_per_piece', 'At least one finishing pricing value must be provided.');
            }

            /*
            |--------------------------------------------------------------------------
            | Quantity integrity
            |--------------------------------------------------------------------------
            */
            $min = $data['min_qty'] ?? null;
            $max = $data['max_qty'] ?? null;

            if ($min !== null && $max !== null && $min > $max) {
                $v->errors()->add('min_qty', 'Min qty must be <= max qty.');
            }

            /*
            |--------------------------------------------------------------------------
            | Prevent duplicate finishing pricing
            |--------------------------------------------------------------------------
            */
            $exists = $pricing->finishingPricings()
                ->where('finishing_product_id', $data['finishing_product_id'])
                ->exists();

            if ($exists) {
                $v->errors()->add('finishing_product_id', 'Finishing pricing already exists for this product.');
            }
        });
    }

    protected function prepareForValidation(): void
    {
        if ($this->has('is_active')) {
            $this->merge([
                'is_active' => filter_var($this->input('is_active'), FILTER_VALIDATE_BOOLEAN),
            ]);
        }
    }
}


<?php

namespace App\Http\Requests;

use App\Models\ProductPricing;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreProductVariantPricingRequest extends FormRequest
{
    public function authorize(): bool
    {
        /** @var ProductPricing $pricing */
        $pricing = $this->route('pricing');

        return $this->user()?->can('manageVariantPricing', $pricing) ?? false;
    }

    public function rules(): array
    {
        return [
            'variant_set_id' => [
                'required',
                'integer',
                'exists:product_variant_sets,id',
            ],

            // Pricing values (one of these must exist)
            'fixed_price' => ['nullable', 'numeric', 'min:0'],
            'rate_per_sqft' => ['nullable', 'numeric', 'min:0'],
            'offcut_rate_per_sqft' => ['nullable', 'numeric', 'min:0'],
            'min_charge' => ['nullable', 'numeric', 'min:0'],

            'is_active' => ['boolean'],
        ];
    }

    public function withValidator($validator)
    {
        $validator->after(function ($v) {
            /** @var ProductPricing $pricing */
            $pricing = $this->route('pricing');
            $product = $pricing->product;
            $data = $this->validated();

            /*
            |--------------------------------------------------------------------------
            | Override flag enforcement
            |--------------------------------------------------------------------------
            */
            if (! $pricing->override_variants) {
                $v->errors()->add('variant_set_id', 'Variant pricing overrides are disabled for this pricing.');
            }

            /*
            |--------------------------------------------------------------------------
            | Variant pricing mode enforcement
            |--------------------------------------------------------------------------
            */
            $hasAnyPrice =
                ! empty($data['fixed_price']) ||
                ! empty($data['rate_per_sqft']) ||
                ! empty($data['min_charge']);

            if (! $hasAnyPrice) {
                $v->errors()->add('fixed_price', 'At least one pricing value must be provided.');
            }

            /*
            |--------------------------------------------------------------------------
            | Product-type-specific rules
            |--------------------------------------------------------------------------
            */
            if ($product->product_type === 'dimension_based') {
                if (empty($data['rate_per_sqft'])) {
                    $v->errors()->add('rate_per_sqft', 'Dimension-based products require a rate per sq.ft.');
                }
            }

            if ($product->product_type === 'standard') {
                if (empty($data['fixed_price'])) {
                    $v->errors()->add('fixed_price', 'Standard products require a fixed variant price.');
                }
            }

            /*
            |--------------------------------------------------------------------------
            | Duplicate variant pricing guard
            |--------------------------------------------------------------------------
            */
            $exists = $pricing->variantPricings()
                ->where('variant_set_id', $data['variant_set_id'])
                ->exists();

            if ($exists) {
                $v->errors()->add('variant_set_id', 'Pricing already exists for this variant combination.');
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


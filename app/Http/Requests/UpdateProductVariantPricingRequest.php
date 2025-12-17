<?php

namespace App\Http\Requests;

use App\Models\ProductVariantPricing;
use Illuminate\Foundation\Http\FormRequest;

class UpdateProductVariantPricingRequest extends FormRequest
{
    public function authorize(): bool
    {
        /** @var ProductVariantPricing $variantPricing */
        $variantPricing = $this->route('variantPricing');

        // Policy ability is on ProductPricing (parent)
        return $this->user()?->can('manageVariantPricing', $variantPricing->pricing) ?? false;
    }

    public function rules(): array
    {
        return [
            // variant_set_id must not change in update (it defines the combo)
            'variant_set_id' => ['prohibited'],

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
            /** @var ProductVariantPricing $variantPricing */
            $variantPricing = $this->route('variantPricing');
            $pricing = $variantPricing->pricing;
            $product = $pricing->product;
            $data = $this->validated();

            /*
            |--------------------------------------------------------------------------
            | Enforce override flag
            |--------------------------------------------------------------------------
            */
            if (! $pricing->override_variants) {
                $v->errors()->add('is_active', 'Variant pricing overrides are disabled for this pricing.');
            }

            /*
            |--------------------------------------------------------------------------
            | Prevent changing variant_set_id
            |--------------------------------------------------------------------------
            */
            if ($this->has('variant_set_id')) {
                $v->errors()->add('variant_set_id', 'Variant set cannot be changed.');
            }

            /*
            |--------------------------------------------------------------------------
            | At least one pricing value must exist (existing OR incoming)
            |--------------------------------------------------------------------------
            */
            $fixed = array_key_exists('fixed_price', $data) ? $data['fixed_price'] : $variantPricing->fixed_price;
            $rate = array_key_exists('rate_per_sqft', $data) ? $data['rate_per_sqft'] : $variantPricing->rate_per_sqft;
            $minCh = array_key_exists('min_charge', $data) ? $data['min_charge'] : $variantPricing->min_charge;

            $hasAny = ! is_null($fixed) || ! is_null($rate) || ! is_null($minCh);

            if (! $hasAny) {
                $v->errors()->add('fixed_price', 'At least one pricing value must exist.');
            }

            /*
            |--------------------------------------------------------------------------
            | Product-type-specific rules
            |--------------------------------------------------------------------------
            */
            if ($product->product_type === 'dimension_based') {
                if (is_null($rate) || $rate === '' || $rate == 0) {
                    $v->errors()->add('rate_per_sqft', 'Dimension-based products require a rate per sq.ft.');
                }
            }

            if ($product->product_type === 'standard') {
                if (is_null($fixed) || $fixed === '') {
                    $v->errors()->add('fixed_price', 'Standard products require a fixed variant price.');
                }
            }

            /*
            |--------------------------------------------------------------------------
            | Future-proof: if variants used in estimate_items/order_items, block edits
            |--------------------------------------------------------------------------
            */
            // Example (enable later):
            // if (\DB::table('estimate_items')->where('product_variant_pricing_id', $variantPricing->id)->exists()) {
            //     $v->errors()->add('fixed_price', 'This variant pricing has been used in estimates and cannot be edited.');
            // }
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


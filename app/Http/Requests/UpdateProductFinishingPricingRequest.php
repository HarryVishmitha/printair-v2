<?php

namespace App\Http\Requests;

use App\Models\ProductFinishingPricing;
use Illuminate\Foundation\Http\FormRequest;

class UpdateProductFinishingPricingRequest extends FormRequest
{
    public function authorize(): bool
    {
        /** @var ProductFinishingPricing $finishingPricing */
        $finishingPricing = $this->route('finishingPricing');

        return $this->user()?->can('manageFinishingPricing', $finishingPricing->pricing) ?? false;
    }

    public function rules(): array
    {
        return [
            // finishing_product_id must never change
            'finishing_product_id' => ['prohibited'],

            'price_per_piece' => ['nullable', 'numeric', 'min:0'],
            'price_per_side' => ['nullable', 'numeric', 'min:0'],
            'flat_price' => ['nullable', 'numeric', 'min:0'],

            'min_qty' => ['nullable', 'integer', 'min:1'],
            'max_qty' => ['nullable', 'integer', 'min:1'],

            'is_active' => ['boolean'],
        ];
    }

    public function withValidator($validator)
    {
        $validator->after(function ($v) {
            /** @var ProductFinishingPricing $finishingPricing */
            $finishingPricing = $this->route('finishingPricing');
            $pricing = $finishingPricing->pricing;
            $data = $this->validated();

            /*
            |--------------------------------------------------------------------------
            | Override enforcement
            |--------------------------------------------------------------------------
            */
            if (! $pricing->override_finishings) {
                $v->errors()->add('is_active', 'Finishing pricing overrides are disabled.');
            }

            /*
            |--------------------------------------------------------------------------
            | At least one pricing mode must exist (existing OR incoming)
            |--------------------------------------------------------------------------
            */
            $perPiece = array_key_exists('price_per_piece', $data)
                ? $data['price_per_piece']
                : $finishingPricing->price_per_piece;

            $perSide = array_key_exists('price_per_side', $data)
                ? $data['price_per_side']
                : $finishingPricing->price_per_side;

            $flat = array_key_exists('flat_price', $data)
                ? $data['flat_price']
                : $finishingPricing->flat_price;

            if (is_null($perPiece) && is_null($perSide) && is_null($flat)) {
                $v->errors()->add('price_per_piece', 'At least one finishing pricing value must exist.');
            }

            /*
            |--------------------------------------------------------------------------
            | Quantity integrity
            |--------------------------------------------------------------------------
            */
            $min = array_key_exists('min_qty', $data) ? $data['min_qty'] : $finishingPricing->min_qty;
            $max = array_key_exists('max_qty', $data) ? $data['max_qty'] : $finishingPricing->max_qty;

            if ($min !== null && $max !== null && $min > $max) {
                $v->errors()->add('min_qty', 'Min qty must be <= max qty.');
            }

            /*
            |--------------------------------------------------------------------------
            | Future-proof: block edits once used in estimates/orders
            |--------------------------------------------------------------------------
            */
            // Example:
            // if (\DB::table('estimate_finishing_items')->where('product_finishing_pricing_id', $finishingPricing->id)->exists()) {
            //     $v->errors()->add('price_per_piece', 'This finishing pricing has been used and cannot be edited.');
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


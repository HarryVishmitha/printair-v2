<?php

namespace App\Http\Requests;

use App\Models\Product;
use App\Models\ProductPricing;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreProductPricingRequest extends FormRequest
{
    public function authorize(): bool
    {
        /** @var Product $product */
        $product = $this->route('product');

        return $this->user()?->can('create', [ProductPricing::class, $product]) ?? false;
    }

    public function rules(): array
    {
        /** @var Product $product */
        $product = $this->route('product');

        return [
            // Context
            'context' => ['required', Rule::in(['public', 'working_group'])],
            'working_group_id' => [
                'nullable',
                'integer',
                'exists:working_groups,id',
                Rule::requiredIf(fn () => $this->input('context') === 'working_group'),
            ],

            // Override flags
            'override_base' => ['boolean'],
            'override_variants' => ['boolean'],
            'override_finishings' => ['boolean'],

            // Base pricing
            'base_price' => ['nullable', 'numeric', 'min:0'],
            'min_charge' => ['nullable', 'numeric', 'min:0'],

            // Dimension-based pricing
            'rate_per_sqft' => ['nullable', 'numeric', 'min:0'],
            'offcut_rate_per_sqft' => ['nullable', 'numeric', 'min:0'],

            // Tiered pricing
            'tiers' => ['nullable', 'array'],
            'tiers.*.min_qty' => ['required_with:tiers', 'integer', 'min:1'],
            'tiers.*.max_qty' => ['nullable', 'integer', 'min:1'],
            'tiers.*.price' => ['required_with:tiers', 'numeric', 'min:0'],

            // Status
            'is_active' => ['boolean'],
        ];
    }

    public function withValidator($validator)
    {
        $validator->after(function ($v) {
            /** @var Product $product */
            $product = $this->route('product');
            $data = $this->validated();

            /*
            |--------------------------------------------------------------------------
            | Context correctness
            |--------------------------------------------------------------------------
            */
            if (($data['context'] ?? null) === 'public' && ! empty($data['working_group_id'])) {
                $v->errors()->add('working_group_id', 'Public pricing must not be bound to a working group.');
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

            if ($product->product_type === 'service') {
                // Services must allow manual pricing
                if (! $product->allow_manual_pricing) {
                    $v->errors()->add('base_price', 'Service products must allow manual pricing.');
                }
            }

            /*
            |--------------------------------------------------------------------------
            | Tiered pricing integrity
            |--------------------------------------------------------------------------
            */

            if (! empty($data['tiers'])) {
                $ranges = [];

                foreach ($data['tiers'] as $index => $tier) {
                    $min = $tier['min_qty'];
                    $max = $tier['max_qty'] ?? null;

                    if ($max !== null && $min > $max) {
                        $v->errors()->add("tiers.$index.max_qty", 'Max qty must be greater than or equal to min qty.');
                    }

                    $ranges[] = [$min, $max];
                }

                // Detect overlapping tiers
                usort($ranges, fn ($a, $b) => $a[0] <=> $b[0]);

                for ($i = 1; $i < count($ranges); $i++) {
                    $prevMax = $ranges[$i - 1][1];
                    $currentMin = $ranges[$i][0];

                    if ($prevMax === null || $currentMin <= $prevMax) {
                        $v->errors()->add('tiers', 'Tier quantity ranges must not overlap.');
                        break;
                    }
                }
            }

            /*
            |--------------------------------------------------------------------------
            | Override logic sanity
            |--------------------------------------------------------------------------
            */

            if (! ($data['override_base'] ?? false) && ! empty($data['base_price'])) {
                $v->errors()->add('base_price', 'Base price provided but override_base is disabled.');
            }

            if (! ($data['override_variants'] ?? false) && ! empty($data['variant_pricing'] ?? null)) {
                $v->errors()->add('override_variants', 'Variant pricing override is disabled.');
            }

            if (! ($data['override_finishings'] ?? false) && ! empty($data['finishing_pricing'] ?? null)) {
                $v->errors()->add('override_finishings', 'Finishing pricing override is disabled.');
            }
        });
    }

    protected function prepareForValidation(): void
    {
        foreach ([
            'override_base',
            'override_variants',
            'override_finishings',
            'is_active',
        ] as $key) {
            if ($this->has($key)) {
                $this->merge([$key => filter_var($this->input($key), FILTER_VALIDATE_BOOLEAN)]);
            }
        }
    }
}


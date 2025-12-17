<?php

namespace App\Http\Requests;

use App\Models\Product;
use App\Models\ProductPricing;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\DB;

class UpdateProductPricingRequest extends FormRequest
{
    public function authorize(): bool
    {
        /** @var ProductPricing $pricing */
        $pricing = $this->route('pricing');

        return $this->user()?->can('update', $pricing) ?? false;
    }

    public function rules(): array
    {
        /** @var ProductPricing $pricing */
        $pricing = $this->route('pricing');
        $product = $pricing->product;

        return [
            // Context is immutable after creation (important!)
            'context' => ['prohibited'],
            'working_group_id' => ['prohibited'],

            // Override flags (editable)
            'override_base' => ['boolean'],
            'override_variants' => ['boolean'],
            'override_finishings' => ['boolean'],

            // Base pricing
            'base_price' => ['nullable', 'numeric', 'min:0'],
            'min_charge' => ['nullable', 'numeric', 'min:0'],

            // Dimension-based pricing
            'rate_per_sqft' => ['nullable', 'numeric', 'min:0'],
            'offcut_rate_per_sqft' => ['nullable', 'numeric', 'min:0'],

            // Tiered pricing (replace-all strategy)
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
            /** @var ProductPricing $pricing */
            $pricing = $this->route('pricing');
            $product = $pricing->product;
            $data = $this->validated();

            /*
            |--------------------------------------------------------------------------
            | Prevent destructive context changes
            |--------------------------------------------------------------------------
            */
            if ($this->has('context') || $this->has('working_group_id')) {
                $v->errors()->add('context', 'Pricing context cannot be changed after creation.');
            }

            /*
            |--------------------------------------------------------------------------
            | Product-type enforcement
            |--------------------------------------------------------------------------
            */
            if ($product->product_type === 'dimension_based') {
                if (array_key_exists('rate_per_sqft', $data) && empty($data['rate_per_sqft'])) {
                    $v->errors()->add('rate_per_sqft', 'Dimension-based products require a rate per sq.ft.');
                }
            }

            /*
            |--------------------------------------------------------------------------
            | Tier integrity (same logic as store)
            |--------------------------------------------------------------------------
            */
            if (!empty($data['tiers'])) {
                $ranges = [];

                foreach ($data['tiers'] as $index => $tier) {
                    $min = $tier['min_qty'];
                    $max = $tier['max_qty'] ?? null;

                    if ($max !== null && $min > $max) {
                        $v->errors()->add("tiers.$index.max_qty", 'Max qty must be >= min qty.');
                    }

                    $ranges[] = [$min, $max];
                }

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
            | Override sanity
            |--------------------------------------------------------------------------
            */
            if (array_key_exists('base_price', $data) && !($data['override_base'] ?? $pricing->override_base)) {
                $v->errors()->add('base_price', 'Base price override is disabled.');
            }

            /*
            |--------------------------------------------------------------------------
            | Historical safety guard (VERY IMPORTANT)
            |--------------------------------------------------------------------------
            | If pricing has already been used in estimates/orders later,
            | this is where we would block or force versioning.
            |--------------------------------------------------------------------------
            */
            // Placeholder for future:
            // if (DB::table('estimate_items')->where('product_pricing_id', $pricing->id)->exists()) {
            //     $v->errors()->add('base_price', 'Pricing already used in estimates and cannot be edited.');
            // }
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


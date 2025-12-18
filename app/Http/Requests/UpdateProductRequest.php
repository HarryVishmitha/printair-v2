<?php

namespace App\Http\Requests;

use App\Models\Product;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class UpdateProductRequest extends FormRequest
{
    public function authorize(): bool
    {
        /** @var \App\Models\Product $product */
        $product = $this->route('product');

        return $this->user()?->can('update', $product) ?? false;
    }

    public function rules(): array
    {
        /** @var \App\Models\Product $product */
        $product = $this->route('product');

        return [
            'category_id' => ['required', 'integer', 'exists:categories,id'],

            // product_code is important for printing workflow. Keep editable only if you want.
            // Here: allow update but unique-ignore current.
            'product_code' => [
                'required',
                'string',
                'max:60',
                'regex:/^[A-Z0-9\-_]+$/i',
                Rule::unique('products', 'product_code')->ignore($product->id),
            ],

            'name' => ['required', 'string', 'max:200'],
            'slug' => ['required', 'string', 'max:220', Rule::unique('products', 'slug')->ignore($product->id)],

            'short_description' => ['nullable', 'string', 'max:255'],
            'long_description' => ['nullable', 'string'],
            // Backward-compat for older forms/clients.
            'description' => ['nullable', 'string'],

            'product_type' => ['required', Rule::in(['standard','dimension_based','finishing','service'])],
            'visibility' => ['required', Rule::in(['public','internal'])],
            'status' => ['required', Rule::in(['active','inactive','draft'])],

            'min_qty' => ['nullable', 'integer', 'min:1'],
            'max_qty' => ['nullable', 'integer', 'min:1'],

            'requires_dimensions' => ['boolean'],
            'allow_custom_size' => ['boolean'],
            'allow_predefined_sizes' => ['boolean'],
            'allow_rotation_to_fit_roll' => ['boolean'],

            'min_width_in' => ['nullable', 'numeric', 'min:0'],
            'max_width_in' => ['nullable', 'numeric', 'min:0'],
            'min_height_in' => ['nullable', 'numeric', 'min:0'],
            'max_height_in' => ['nullable', 'numeric', 'min:0'],
            'roll_max_width_in' => ['nullable', 'numeric', 'min:0'],

            'allow_manual_pricing' => ['boolean'],

            'meta' => ['nullable', 'array'],
        ];
    }

    public function withValidator($validator)
    {
        $validator->after(function ($v) {
            /** @var \App\Models\Product $product */
            $product = $this->route('product');
            $data = $this->validated();

            // ---- Same guardrails as store ----
            if (($data['product_type'] ?? null) === 'dimension_based') {
                if (!($data['requires_dimensions'] ?? false)) {
                    $v->errors()->add('requires_dimensions', 'Dimension-based products must require dimensions.');
                }
            }

            $minW = $data['min_width_in'] ?? null;
            $maxW = $data['max_width_in'] ?? null;
            if ($minW !== null && $maxW !== null && $minW > $maxW) {
                $v->errors()->add('min_width_in', 'Min width must be <= max width.');
            }

            $minH = $data['min_height_in'] ?? null;
            $maxH = $data['max_height_in'] ?? null;
            if ($minH !== null && $maxH !== null && $minH > $maxH) {
                $v->errors()->add('min_height_in', 'Min height must be <= max height.');
            }

            $minQ = $data['min_qty'] ?? null;
            $maxQ = $data['max_qty'] ?? null;
            if ($minQ !== null && $maxQ !== null && $minQ > $maxQ) {
                $v->errors()->add('min_qty', 'Min qty must be <= max qty.');
            }

            // ---- Fool-proof: block risky type changes once the product is in-use ----
            // If pricing exists or variant sets exist, changing product_type can break calculations.
            $hasPricing = DB::table('product_pricings')
                ->whereNull('deleted_at')
                ->where('product_id', $product->id)
                ->exists();

            $hasVariantSets = DB::table('product_variant_sets')
                ->whereNull('deleted_at')
                ->where('product_id', $product->id)
                ->exists();

            $hasFinishLinks = DB::table('product_finishing_links')
                ->whereNull('deleted_at')
                ->where('product_id', $product->id)
                ->exists();

            $incomingType = $data['product_type'] ?? $product->product_type;

            if (($incomingType !== $product->product_type) && ($hasPricing || $hasVariantSets || $hasFinishLinks)) {
                $v->errors()->add('product_type', 'Product type cannot be changed after pricing/variants/finishings are configured.');
            }

            // ---- Optional: lock product_code after it’s used anywhere ----
            // You can keep this ON (recommended) because product_code will be used on invoices/estimates later.
            $incomingCode = $data['product_code'] ?? $product->product_code;
            if ($incomingCode !== $product->product_code) {
                // Future-proof: if you later add estimate/order tables, add checks here.
                // For now, if any pricing exists, we treat it as “in use”.
                if ($hasPricing) {
                    $v->errors()->add('product_code', 'Product code cannot be changed after pricing has been created.');
                }
            }
        });
    }

    protected function prepareForValidation(): void
    {
        foreach ([
            'requires_dimensions',
            'allow_custom_size',
            'allow_predefined_sizes',
            'allow_rotation_to_fit_roll',
            'allow_manual_pricing',
        ] as $key) {
            if ($this->has($key)) {
                $this->merge([$key => filter_var($this->input($key), FILTER_VALIDATE_BOOLEAN)]);
            }
        }
    }
}

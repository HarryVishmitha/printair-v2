<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreProductRequest extends FormRequest
{
    public function authorize(): bool
    {
        // Uses ProductPolicy
        return $this->user()?->can('create', \App\Models\Product::class) ?? false;
    }

    public function rules(): array
    {
        return [
            'category_id' => ['required', 'integer', 'exists:categories,id'],

            'product_code' => ['required', 'string', 'max:60', 'regex:/^[A-Z0-9\-_]+$/i', 'unique:products,product_code'],
            'name' => ['required', 'string', 'max:200'],
            'slug' => ['required', 'string', 'max:220', 'unique:products,slug'],
            'short_description' => ['nullable', 'string', 'max:255'],
            'long_description' => ['nullable', 'string'],
            // Backward-compat for older forms/clients.
            'description' => ['nullable', 'string'],

            'product_type' => ['required', Rule::in(['standard','dimension_based','finishing','service'])],
            'visibility' => ['required', Rule::in(['public','internal'])],
            'status' => ['required', Rule::in(['active','inactive','draft'])],

            // Standard rules
            'min_qty' => ['nullable', 'integer', 'min:1'],
            'max_qty' => ['nullable', 'integer', 'min:1'],

            // Dimension-based config
            'requires_dimensions' => ['boolean'],
            'allow_custom_size' => ['boolean'],
            'allow_predefined_sizes' => ['boolean'],
            'allow_rotation_to_fit_roll' => ['boolean'],

            // stored as inches (your standard)
            'min_width_in' => ['nullable', 'numeric', 'min:0'],
            'max_width_in' => ['nullable', 'numeric', 'min:0'],
            'min_height_in' => ['nullable', 'numeric', 'min:0'],
            'max_height_in' => ['nullable', 'numeric', 'min:0'],
            'roll_max_width_in' => ['nullable', 'numeric', 'min:0'],

            // Service config
            'allow_manual_pricing' => ['boolean'],

            // json meta
            'meta' => ['nullable', 'array'],
        ];
    }

    public function withValidator($validator)
    {
        $validator->after(function ($v) {
            $data = $this->validated();

            // Guardrails:
            // If dimension_based => requires_dimensions must be true
            if (($data['product_type'] ?? null) === 'dimension_based') {
                if (!($data['requires_dimensions'] ?? false)) {
                    $v->errors()->add('requires_dimensions', 'Dimension-based products must require dimensions.');
                }
            }

            // Finishing products must be internal-only
            if (($data['product_type'] ?? null) === 'finishing') {
                if (($data['visibility'] ?? null) !== 'internal') {
                    $v->errors()->add('visibility', 'Finishing products must have visibility set to Internal.');
                }
            }

            // min <= max checks
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

            // qty range check (if both provided)
            $minQ = $data['min_qty'] ?? null;
            $maxQ = $data['max_qty'] ?? null;
            if ($minQ !== null && $maxQ !== null && $minQ > $maxQ) {
                $v->errors()->add('min_qty', 'Min qty must be <= max qty.');
            }
        });
    }

    protected function prepareForValidation(): void
    {
        // Normalize booleans (checkboxes)
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

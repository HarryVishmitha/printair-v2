<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateInvoiceRequest extends FormRequest
{
    public function authorize(): bool
    {
        $invoice = $this->route('invoice');
        return $invoice ? ($this->user()?->can('update', $invoice) ?? false) : false;
    }

    public function rules(): array
    {
        return [
            'customer_snapshot' => ['nullable', 'array'],
            'currency' => ['nullable', 'string', 'max:10'],

            'due_at' => ['nullable', 'date'],
            'shipping_fee' => ['nullable', 'numeric', 'min:0'],
            'other_fee' => ['nullable', 'numeric', 'min:0'],

            'items' => ['required', 'array', 'min:1'],
            'items.*.id' => ['nullable', 'integer'],

            'items.*.product_id' => ['required', 'integer', 'exists:products,id'],
            'items.*.variant_set_item_id' => ['nullable', 'integer', 'exists:product_variant_set_items,id'],
            'items.*.roll_id' => ['nullable', 'integer', 'exists:rolls,id'],

            'items.*.title' => ['required', 'string', 'max:255'],
            'items.*.description' => ['nullable', 'string'],
            'items.*.qty' => ['required', 'integer', 'min:1'],

            'items.*.width' => ['nullable', 'numeric', 'min:0.01'],
            'items.*.height' => ['nullable', 'numeric', 'min:0.01'],
            'items.*.unit' => ['nullable', 'string', 'in:in,ft,mm,cm,m'],
            'items.*.area_sqft' => ['nullable', 'numeric', 'min:0'],
            'items.*.offcut_sqft' => ['nullable', 'numeric', 'min:0'],

            // Variant option selections (drives quoting + stored in pricing_snapshot).
            'items.*.options' => ['nullable', 'array', 'max:60'],
            'items.*.options.*' => ['nullable', 'integer'],

            'items.*.pricing_snapshot' => ['nullable', 'array'],
            'items.*.unit_price' => ['required', 'numeric', 'min:0'],
            'items.*.line_subtotal' => ['required', 'numeric', 'min:0'],
            'items.*.discount_amount' => ['nullable', 'numeric', 'min:0'],
            'items.*.tax_amount' => ['nullable', 'numeric', 'min:0'],
            'items.*.line_total' => ['required', 'numeric', 'min:0'],

            // Finishings (allow add/remove like estimate editor).
            'items.*.finishings' => ['nullable', 'array'],
            'items.*.finishings.*.id' => ['nullable', 'integer'],
            'items.*.finishings.*.finishing_product_id' => ['required_with:items.*.finishings', 'integer', 'exists:products,id'],
            'items.*.finishings.*.option_id' => ['nullable', 'integer', 'exists:options,id'],
            'items.*.finishings.*.label' => ['nullable', 'string', 'max:255'],
            'items.*.finishings.*.selected' => ['nullable', 'boolean'],
            'items.*.finishings.*.remove' => ['nullable', 'boolean'],
            'items.*.finishings.*.qty' => ['nullable', 'integer', 'min:1'],
            'items.*.finishings.*.unit_price' => ['nullable', 'numeric', 'min:0'],
            'items.*.finishings.*.total' => ['nullable', 'numeric', 'min:0'],
            'items.*.finishings.*.pricing_snapshot' => ['nullable', 'array'],
        ];
    }
}

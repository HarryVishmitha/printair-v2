<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ChangeOrderStatusRequest extends FormRequest
{
    public function authorize(): bool
    {
        $order = $this->route('order');
        return $order ? ($this->user()?->can('changeStatus', $order) ?? false) : false;
    }

    public function rules(): array
    {
        $allowed = ['confirmed', 'in_production', 'ready', 'out_for_delivery', 'completed', 'cancelled', 'refunded'];

        return [
            'status' => ['required', 'string', 'max:40', Rule::in($allowed)],
            'reason' => [
                Rule::requiredIf(fn () => in_array((string) $this->input('status'), ['cancelled', 'refunded'], true)),
                'nullable',
                'string',
                'max:500',
            ],
        ];
    }
}

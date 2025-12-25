<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ChangeOrderStatusRequest extends FormRequest
{
    public function authorize(): bool
    {
        $order = $this->route('order');
        return $order ? ($this->user()?->can('changeStatus', $order) ?? false) : false;
    }

    public function rules(): array
    {
        return [
            'status' => ['required', 'string', 'max:40'],
            'reason' => ['nullable', 'string', 'max:500'],
        ];
    }
}


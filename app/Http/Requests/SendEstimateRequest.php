<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class SendEstimateRequest extends FormRequest
{
    public function authorize(): bool
    {
        $estimate = $this->route('estimate');
        return $estimate ? ($this->user()?->can('send', $estimate) ?? false) : false;
    }

    public function rules(): array
    {
        return [
            'reason' => ['nullable', 'string', 'max:500'],
        ];
    }
}


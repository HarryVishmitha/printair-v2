<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class RejectEstimateRequest extends FormRequest
{
    public function authorize(): bool
    {
        $estimate = $this->route('estimate');
        return $estimate ? ($this->user()?->can('reject', $estimate) ?? false) : false;
    }

    public function rules(): array
    {
        return [
            'reason' => ['required', 'string', 'max:500'],
        ];
    }
}


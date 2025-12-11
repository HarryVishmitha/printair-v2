<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreWorkingGroupRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('manage-working-groups') ?? false;
    }

    public function rules(): array
    {
        return [
            'name'         => ['required', 'string', 'max:100', 'unique:working_groups,name'],
            'slug'         => ['nullable', 'string', 'max:100', 'alpha_dash', 'unique:working_groups,slug'],
            'description'  => ['nullable', 'string', 'max:255'],
            'is_shareable' => ['sometimes', 'boolean'],
            'is_restricted'=> ['sometimes', 'boolean'],
            'is_staff_group'=> ['sometimes', 'boolean'],
        ];
    }
}

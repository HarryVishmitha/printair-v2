<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use App\Models\WorkingGroup;

class UpdateWorkingGroupRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('manage-working-groups') ?? false;
    }

    public function rules(): array
    {
        /** @var \App\Models\WorkingGroup|null $group */
        $group = $this->route('workingGroup') ?? $this->route('working_group');
        $id = $group?->id ?? null;

        return [
            'name'         => ['required', 'string', 'max:100', "unique:working_groups,name,{$id}"],
            'slug'         => ['nullable', 'string', 'max:100', 'alpha_dash', "unique:working_groups,slug,{$id}"],
            'description'  => ['nullable', 'string', 'max:255'],
            'is_shareable' => ['sometimes', 'boolean'],
            'is_restricted'=> ['sometimes', 'boolean'],
            'is_staff_group'=> ['sometimes', 'boolean'],
        ];
    }
}

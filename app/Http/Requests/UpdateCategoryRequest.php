<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class UpdateCategoryRequest extends FormRequest
{
    public function authorize(): bool
    {
        return Auth::check();
    }

    public function rules(): array
    {
        $categoryId = $this->route('category')?->id ?? $this->route('category'); // supports model binding or id
        $workingGroupId = Auth::user()?->working_group_id;

        return [
            'working_group_id' => ['nullable', 'integer'],
            'parent_id' => [
                'nullable',
                'integer',
                'exists:categories,id',
                'not_in:'.(string) $categoryId, // prevent self-parenting
            ],

            'name' => ['required', 'string', 'max:160'],
            'slug' => [
                'required',
                'string',
                'max:200',
                Rule::unique('categories', 'slug')
                    ->ignore($categoryId)
                    ->where(fn ($q) => $q->where('working_group_id', $workingGroupId)),
            ],

            'code' => ['nullable', 'string', 'max:50'],

            'short_description' => ['nullable', 'string', 'max:255'],
            'description' => ['nullable', 'string'],

            'icon_path' => ['nullable', 'string', 'max:500'],
            'cover_image_path' => ['nullable', 'string', 'max:500'],

            'sort_order' => ['nullable', 'integer', 'min:0'],
            'is_active' => ['nullable', 'boolean'],
            'is_featured' => ['nullable', 'boolean'],
            'show_in_menu' => ['nullable', 'boolean'],
            'show_in_navbar' => ['nullable', 'boolean'],

            'seo_title' => ['nullable', 'string', 'max:160'],
            'seo_description' => ['nullable', 'string', 'max:255'],
            'seo_keywords' => ['nullable', 'string', 'max:255'],
            'og_image_path' => ['nullable', 'string', 'max:500'],
            'is_indexable' => ['nullable', 'boolean'],

            'meta' => ['nullable', 'array'],
            'settings' => ['nullable', 'array'],
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'is_active' => filter_var($this->input('is_active', true), FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE) ?? true,
            'is_featured' => filter_var($this->input('is_featured', false), FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE) ?? false,
            'show_in_menu' => filter_var($this->input('show_in_menu', true), FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE) ?? true,
            'show_in_navbar' => filter_var($this->input('show_in_navbar', true), FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE) ?? true,
            'is_indexable' => filter_var($this->input('is_indexable', true), FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE) ?? true,
        ]);
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Category extends Model
{
    use SoftDeletes;

    protected static function booted(): void
    {
        static::saved(static fn () => \App\Services\Public\NavbarDataService::bustCache());
        static::deleted(static fn () => \App\Services\Public\NavbarDataService::bustCache());
        static::restored(static fn () => \App\Services\Public\NavbarDataService::bustCache());
        static::forceDeleted(static fn () => \App\Services\Public\NavbarDataService::bustCache());
    }

    protected $fillable = [
        'working_group_id',
        'parent_id',
        'name',
        'slug',
        'code',
        'short_description',
        'description',
        'icon_path',
        'cover_image_path',
        'sort_order',
        'is_active',
        'is_featured',
        'show_in_menu',
        'show_in_navbar',
        'seo_title',
        'seo_description',
        'seo_keywords',
        'og_image_path',
        'is_indexable',
        'meta',
        'settings',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'is_featured' => 'boolean',
        'show_in_menu' => 'boolean',
        'show_in_navbar' => 'boolean',
        'is_indexable' => 'boolean',
        'meta' => 'array',
        'settings' => 'array',
    ];

    public function parent()
    {
        return $this->belongsTo(self::class, 'parent_id');
    }

    public function children()
    {
        return $this->hasMany(self::class, 'parent_id')->orderBy('sort_order');
    }

    public function products(): HasMany
    {
        return $this->hasMany(Product::class, 'category_id');
    }

    public function workingGroup()
    {
        return $this->belongsTo(WorkingGroup::class);
    }
}

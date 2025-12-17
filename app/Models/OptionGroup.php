<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\{HasMany, BelongsToMany};

class OptionGroup extends Model
{
    protected $guarded = ['id'];

    public function options(): HasMany
    {
        return $this->hasMany(Option::class);
    }

    public function productOptionGroups(): HasMany
    {
        return $this->hasMany(ProductOptionGroup::class);
    }

    public function products(): BelongsToMany
    {
        return $this->belongsToMany(Product::class, 'product_option_groups')
            ->withPivot(['is_required', 'sort_index', 'deleted_at'])
            ->wherePivotNull('deleted_at')
            ->orderBy('product_option_groups.sort_index');
    }
}


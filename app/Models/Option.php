<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\{BelongsTo, BelongsToMany, HasMany};

class Option extends Model
{
    protected $guarded = ['id'];

    protected $casts = [
        'meta' => 'array',
    ];

    public function group(): BelongsTo
    {
        return $this->belongsTo(OptionGroup::class, 'option_group_id');
    }

    public function productOptions(): HasMany
    {
        return $this->hasMany(ProductOption::class);
    }

    public function products(): BelongsToMany
    {
        return $this->belongsToMany(Product::class, 'product_options')
            ->withPivot(['is_active', 'sort_index', 'deleted_at'])
            ->wherePivotNull('deleted_at')
            ->orderBy('product_options.sort_index');
    }
}


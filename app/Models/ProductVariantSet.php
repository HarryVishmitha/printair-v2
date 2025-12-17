<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\{BelongsTo, HasMany, BelongsToMany};

class ProductVariantSet extends Model
{
    use SoftDeletes;

    protected $guarded = ['id'];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(ProductVariantSetItem::class, 'variant_set_id');
    }

    public function options(): BelongsToMany
    {
        return $this->belongsToMany(Option::class, 'product_variant_set_items', 'variant_set_id', 'option_id');
    }

    public function variantPricings(): HasMany
    {
        return $this->hasMany(ProductVariantPricing::class, 'variant_set_id');
    }

    public function availabilityOverrides(): HasMany
    {
        return $this->hasMany(ProductVariantAvailabilityOverride::class, 'variant_set_id');
    }
}


<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProductVariantPricing extends Model
{
    use SoftDeletes;

    protected $table = 'product_variant_pricings';
    protected $guarded = ['id'];

    protected $casts = [
        'fixed_price' => 'decimal:2',
        'rate_per_sqft' => 'decimal:4',
        'offcut_rate_per_sqft' => 'decimal:4',
        'min_charge' => 'decimal:2',
        'is_active' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    /*
     |--------------------------------------------------------------------------
     | Relationships
     |--------------------------------------------------------------------------
     */

    public function pricing(): BelongsTo
    {
        return $this->belongsTo(ProductPricing::class, 'product_pricing_id');
    }

    public function variantSet(): BelongsTo
    {
        return $this->belongsTo(ProductVariantSet::class, 'variant_set_id');
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updatedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    /*
     |--------------------------------------------------------------------------
     | Scopes
     |--------------------------------------------------------------------------
     */

    public function scopeActive(Builder $q): Builder
    {
        return $q->where('is_active', 1)->whereNull('deleted_at');
    }

    /*
     |--------------------------------------------------------------------------
     | Helpers
     |--------------------------------------------------------------------------
     */

    public function getModeAttribute(): string
    {
        // Useful for UI: show what kind of pricing is stored on this row
        if (! is_null($this->rate_per_sqft)) {
            return 'dimension_based';
        }
        if (! is_null($this->fixed_price)) {
            return 'fixed';
        }
        return 'unset';
    }
}


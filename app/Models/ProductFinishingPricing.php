<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProductFinishingPricing extends Model
{
    use SoftDeletes;

    protected $table = 'product_finishing_pricings';
    protected $guarded = ['id'];

    protected $casts = [
        'price_per_piece' => 'decimal:2',
        'price_per_side' => 'decimal:2',
        'flat_price' => 'decimal:2',
        'min_qty' => 'integer',
        'max_qty' => 'integer',
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

    public function finishingProduct(): BelongsTo
    {
        return $this->belongsTo(Product::class, 'finishing_product_id');
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

    public function scopeForQty(Builder $q, int $qty): Builder
    {
        return $q->where(function ($qq) use ($qty) {
            $qq->whereNull('min_qty')->orWhere('min_qty', '<=', $qty);
        })->where(function ($qq) use ($qty) {
            $qq->whereNull('max_qty')->orWhere('max_qty', '>=', $qty);
        });
    }

    /*
     |--------------------------------------------------------------------------
     | Helpers
     |--------------------------------------------------------------------------
     */

    public function getSupportedModesAttribute(): array
    {
        // Which charging modes have a configured price?
        $modes = [];
        if (! is_null($this->price_per_piece)) {
            $modes[] = 'per_piece';
        }
        if (! is_null($this->price_per_side)) {
            $modes[] = 'per_side';
        }
        if (! is_null($this->flat_price)) {
            $modes[] = 'flat';
        }
        return $modes;
    }
}


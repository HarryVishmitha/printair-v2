<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProductPriceTier extends Model
{
    use SoftDeletes;

    protected $table = 'product_price_tiers';

    // Safer than fillable for admin-heavy writes
    protected $guarded = ['id'];

    protected $casts = [
        'min_qty' => 'integer',
        'max_qty' => 'integer',
        'price' => 'decimal:2',
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

    public function scopeOrdered(Builder $q): Builder
    {
        return $q->orderBy('min_qty')->orderByRaw('ISNULL(max_qty) ASC')->orderBy('max_qty');
    }

    public function scopeForQty(Builder $q, int $qty): Builder
    {
        // matches: min_qty <= qty AND (max_qty is null OR max_qty >= qty)
        return $q->where('min_qty', '<=', $qty)
            ->where(function ($qq) use ($qty) {
                $qq->whereNull('max_qty')->orWhere('max_qty', '>=', $qty);
            });
    }

    /*
     |--------------------------------------------------------------------------
     | Helpers
     |--------------------------------------------------------------------------
     */

    public function coversQty(int $qty): bool
    {
        if ($qty < (int) $this->min_qty) {
            return false;
        }
        if ($this->max_qty === null) {
            return true;
        }

        return $qty <= (int) $this->max_qty;
    }
}


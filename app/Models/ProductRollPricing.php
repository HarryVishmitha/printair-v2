<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProductRollPricing extends Model
{
    use SoftDeletes;

    protected $table = 'product_roll_pricings';

    protected $fillable = [
        'product_pricing_id',
        'product_id',
        'roll_id',

        'rate_per_sqft',
        'offcut_rate_per_sqft',
        'min_charge',

        'is_active',
        'meta',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'rate_per_sqft' => 'decimal:2',
        'offcut_rate_per_sqft' => 'decimal:2',
        'min_charge' => 'decimal:2',
        'meta' => 'array',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    // ---------- Relations ----------

    public function pricing(): BelongsTo
    {
        return $this->belongsTo(ProductPricing::class, 'product_pricing_id');
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function roll(): BelongsTo
    {
        return $this->belongsTo(Roll::class);
    }

    // ---------- Scopes ----------

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}


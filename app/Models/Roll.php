<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Roll extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'name',
        'slug',
        'material_type',
        'width_in',
        'is_active',
        'meta',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'width_in' => 'decimal:3',
        'meta' => 'array',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    // ---------- Relations ----------

    public function productLinks(): HasMany
    {
        return $this->hasMany(ProductRoll::class, 'roll_id');
    }

    public function rollPricings(): HasMany
    {
        return $this->hasMany(ProductRollPricing::class, 'roll_id');
    }

    // ---------- Scopes ----------

    public function scopeActive($query)
    {
        // Qualify column to avoid ambiguity when joined via product_rolls pivot.
        return $query->where($this->getTable().'.is_active', true);
    }
}

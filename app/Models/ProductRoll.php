<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProductRoll extends Model
{
    use SoftDeletes;

    protected $table = 'product_rolls';

    protected $fillable = [
        'product_id',
        'roll_id',
        'is_active',
        'min_height_in',
        'max_height_in',
        'meta',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'min_height_in' => 'decimal:3',
        'max_height_in' => 'decimal:3',
        'meta' => 'array',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    // ---------- Relations ----------

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


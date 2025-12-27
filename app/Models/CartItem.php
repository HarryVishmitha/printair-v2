<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CartItem extends Model
{
    use SoftDeletes;

    protected $guarded = ['id'];

    protected $casts = [
        'pricing_snapshot' => 'array',
        'meta' => 'array',
        'width' => 'decimal:3',
        'height' => 'decimal:3',
        'area_sqft' => 'decimal:4',
        'offcut_sqft' => 'decimal:4',
    ];

    public function cart(): BelongsTo
    {
        return $this->belongsTo(Cart::class, 'cart_id');
    }

    public function finishings(): HasMany
    {
        return $this->hasMany(CartItemFinishing::class, 'cart_item_id');
    }

    public function files(): HasMany
    {
        return $this->hasMany(CartItemFile::class, 'cart_item_id');
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class, 'product_id');
    }

    public function variantSetItem(): BelongsTo
    {
        return $this->belongsTo(ProductVariantSetItem::class, 'variant_set_item_id');
    }

    public function roll(): BelongsTo
    {
        return $this->belongsTo(Roll::class, 'roll_id');
    }
}


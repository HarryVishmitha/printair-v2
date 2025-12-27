<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CartItemFinishing extends Model
{
    use SoftDeletes;

    protected $guarded = ['id'];

    protected $casts = [
        'pricing_snapshot' => 'array',
        'meta' => 'array',
    ];

    public function item(): BelongsTo
    {
        return $this->belongsTo(CartItem::class, 'cart_item_id');
    }

    public function finishingProduct(): BelongsTo
    {
        return $this->belongsTo(Product::class, 'finishing_product_id');
    }
}


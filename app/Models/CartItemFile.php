<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CartItemFile extends Model
{
    use SoftDeletes;

    protected $guarded = ['id'];

    protected $casts = [
        'meta' => 'array',
        'is_customer_artwork' => 'boolean',
    ];

    public function item(): BelongsTo
    {
        return $this->belongsTo(CartItem::class, 'cart_item_id');
    }
}


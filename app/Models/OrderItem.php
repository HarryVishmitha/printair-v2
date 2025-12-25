<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\{BelongsTo, HasMany};
use Illuminate\Database\Eloquent\SoftDeletes;

class OrderItem extends Model
{
    use HasFactory, SoftDeletes;

    protected $guarded = ['id'];

    protected $casts = [
        'pricing_snapshot' => 'array',

        'width' => 'decimal:3',
        'height' => 'decimal:3',
        'area_sqft' => 'decimal:4',
        'offcut_sqft' => 'decimal:4',

        'unit_price' => 'decimal:2',
        'line_subtotal' => 'decimal:2',
        'discount_amount' => 'decimal:2',
        'tax_amount' => 'decimal:2',
        'line_total' => 'decimal:2',
    ];

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class, 'order_id');
    }

    public function workingGroup(): BelongsTo
    {
        return $this->belongsTo(WorkingGroup::class, 'working_group_id');
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

    public function finishings(): HasMany
    {
        return $this->hasMany(OrderItemFinishing::class, 'order_item_id');
    }
}


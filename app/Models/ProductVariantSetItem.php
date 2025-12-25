<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\{BelongsTo, HasMany};

class ProductVariantSetItem extends Model
{
    public $timestamps = false;

    protected $guarded = ['id'];

    public function variantSet(): BelongsTo
    {
        return $this->belongsTo(ProductVariantSet::class, 'variant_set_id');
    }

    public function option(): BelongsTo
    {
        return $this->belongsTo(Option::class);
    }

    public function estimateItems(): HasMany
    {
        return $this->hasMany(EstimateItem::class, 'variant_set_item_id');
    }

    public function orderItems(): HasMany
    {
        return $this->hasMany(OrderItem::class, 'variant_set_item_id');
    }

    public function invoiceItems(): HasMany
    {
        return $this->hasMany(InvoiceItem::class, 'variant_set_item_id');
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

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
}


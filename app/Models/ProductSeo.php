<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProductSeo extends Model
{
    protected $table = 'product_seo';

    protected $guarded = ['id'];

    protected $casts = [
        'is_indexable' => 'boolean',
    ];

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }
}

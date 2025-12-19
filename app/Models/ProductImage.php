<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProductImage extends Model
{
    use SoftDeletes;

    protected static function booted(): void
    {
        static::saved(static fn () => \App\Services\Public\NavbarDataService::bustCache());
        static::deleted(static fn () => \App\Services\Public\NavbarDataService::bustCache());
        static::restored(static fn () => \App\Services\Public\NavbarDataService::bustCache());
        static::forceDeleted(static fn () => \App\Services\Public\NavbarDataService::bustCache());
    }

    protected $guarded = ['id'];

    protected $casts = [
        'is_featured' => 'boolean',
        'sort_index' => 'integer',
        'meta' => 'array',
    ];

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }
}

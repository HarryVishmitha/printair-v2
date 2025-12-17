<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PricingAudit extends Model
{
    protected $table = 'pricing_audits';

    // pricing_audits uses created_at only (no updated_at)
    public $timestamps = false;

    protected $guarded = ['id'];

    protected $casts = [
        'old_values' => 'array',
        'new_values' => 'array',
        'created_at' => 'datetime',
    ];

    /*
     |--------------------------------------------------------------------------
     | Relationships
     |--------------------------------------------------------------------------
     */

    public function pricing(): BelongsTo
    {
        return $this->belongsTo(ProductPricing::class, 'product_pricing_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /*
     |--------------------------------------------------------------------------
     | Scopes
     |--------------------------------------------------------------------------
     */

    public function scopeRecent(Builder $q, int $limit = 50): Builder
    {
        return $q->orderByDesc('id')->limit($limit);
    }

    public function scopeForPricing(Builder $q, int $productPricingId): Builder
    {
        return $q->where('product_pricing_id', $productPricingId);
    }

    /*
     |--------------------------------------------------------------------------
     | Helpers
     |--------------------------------------------------------------------------
     */

    public function getSummaryAttribute(): string
    {
        $action = (string) ($this->action ?? 'updated');
        $who = $this->user?->name ?: 'System';
        return "{$action} by {$who}";
    }
}


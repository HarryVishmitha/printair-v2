<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ProductPricing extends Model
{
    use SoftDeletes;

    protected $table = 'product_pricings';

    // Safer than fillable in admin-heavy modules
    protected $guarded = ['id', 'working_group_key'];

    protected $casts = [
        'override_base' => 'boolean',
        'override_variants' => 'boolean',
        'override_finishings' => 'boolean',
        'is_active' => 'boolean',

        'base_price' => 'decimal:2',
        'rate_per_sqft' => 'decimal:4',
        'offcut_rate_per_sqft' => 'decimal:4',
        'min_charge' => 'decimal:2',
    ];

    /*
     |--------------------------------------------------------------------------
     | Relationships
     |--------------------------------------------------------------------------
     */

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function workingGroup(): BelongsTo
    {
        return $this->belongsTo(WorkingGroup::class);
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updatedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    public function tiers(): HasMany
    {
        return $this->hasMany(ProductPriceTier::class, 'product_pricing_id')
            ->whereNull('deleted_at')
            ->orderBy('min_qty');
    }

    public function variantPricings(): HasMany
    {
        return $this->hasMany(ProductVariantPricing::class, 'product_pricing_id')
            ->whereNull('deleted_at')
            ->orderBy('id');
    }

    public function finishingPricings(): HasMany
    {
        return $this->hasMany(ProductFinishingPricing::class, 'product_pricing_id')
            ->whereNull('deleted_at')
            ->orderBy('id');
    }

    public function audits(): HasMany
    {
        return $this->hasMany(PricingAudit::class, 'product_pricing_id')
            ->orderByDesc('id');
    }

    public function rollPricings(): HasMany
    {
        return $this->hasMany(ProductRollPricing::class, 'product_pricing_id');
    }

    /*
     |--------------------------------------------------------------------------
     | Scopes (for controllers/services)
     |--------------------------------------------------------------------------
     */

    public function scopeActive(Builder $q): Builder
    {
        return $q->where('is_active', 1)->whereNull('deleted_at');
    }

    public function scopePublic(Builder $q): Builder
    {
        return $q->where('context', 'public')->whereNull('working_group_id');
    }

    public function scopeForWorkingGroup(Builder $q, WorkingGroup|int $wg): Builder
    {
        $wgId = $wg instanceof WorkingGroup ? $wg->id : $wg;

        return $q->where('context', 'working_group')
            ->where('working_group_id', $wgId);
    }

    /*
     |--------------------------------------------------------------------------
     | Helpers / Computed attributes
     |--------------------------------------------------------------------------
     */

    public function isPublic(): bool
    {
        return $this->context === 'public' && $this->working_group_id === null;
    }

    public function isWorkingGroup(): bool
    {
        return $this->context === 'working_group' && $this->working_group_id !== null;
    }

    public function hasAnyOverrides(): bool
    {
        return (bool) ($this->override_base || $this->override_variants || $this->override_finishings);
    }

    /**
     * Determine pricing mode based on stored fields.
     * Useful for UI: standard vs dimension-based vs service.
     */
    public function getPricingModeAttribute(): string
    {
        if (! is_null($this->rate_per_sqft)) {
            return 'dimension_based';
        }

        if (! is_null($this->base_price)) {
            return 'fixed_base';
        }

        return 'unset';
    }
}

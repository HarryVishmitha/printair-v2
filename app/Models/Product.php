<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\{
    BelongsTo,
    HasMany,
    HasOne,
    BelongsToMany
};

class Product extends Model
{
    use SoftDeletes;

    protected $guarded = ['id'];

    protected $casts = [
        'requires_dimensions' => 'boolean',
        'allow_custom_size' => 'boolean',
        'allow_predefined_sizes' => 'boolean',
        'allow_rotation_to_fit_roll' => 'boolean',
        'allow_manual_pricing' => 'boolean',
        'meta' => 'array',
        'min_width_in' => 'decimal:3',
        'max_width_in' => 'decimal:3',
        'min_height_in' => 'decimal:3',
        'max_height_in' => 'decimal:3',
        'roll_max_width_in' => 'decimal:3',
    ];

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function seo(): HasOne
    {
        return $this->hasOne(ProductSeo::class);
    }

    public function images(): HasMany
    {
        return $this->hasMany(ProductImage::class)->orderBy('sort_index');
    }

    public function featuredImage(): HasOne
    {
        return $this->hasOne(ProductImage::class)->where('is_featured', 1);
    }

    public function primaryImage(): HasOne
    {
        return $this->hasOne(ProductImage::class)->where('is_featured', 1);
    }

    public function files(): HasMany
    {
        return $this->hasMany(ProductFile::class)->orderBy('id', 'desc');
    }

    public function attachments(): HasMany
    {
        return $this->hasMany(ProductFile::class)->orderBy('id', 'desc');
    }

    public function specGroups(): HasMany
    {
        return $this->hasMany(ProductSpecGroup::class)->orderBy('sort_index');
    }

    public function specs(): HasMany
    {
        return $this->hasMany(ProductSpec::class)->orderBy('sort_index');
    }

    public function productOptionGroups(): HasMany
    {
        return $this->hasMany(ProductOptionGroup::class)->orderBy('sort_index');
    }

    public function optionGroups(): BelongsToMany
    {
        return $this->belongsToMany(OptionGroup::class, 'product_option_groups')
            ->withPivot(['is_required', 'sort_index', 'deleted_at'])
            ->wherePivotNull('deleted_at')
            ->orderBy('product_option_groups.sort_index');
    }

    public function productOptions(): HasMany
    {
        return $this->hasMany(ProductOption::class)->orderBy('sort_index');
    }

    public function options(): BelongsToMany
    {
        return $this->belongsToMany(Option::class, 'product_options')
            ->withPivot(['is_active', 'sort_index', 'deleted_at'])
            ->wherePivotNull('deleted_at')
            ->orderBy('product_options.sort_index');
    }

    public function variantSets(): HasMany
    {
        return $this->hasMany(ProductVariantSet::class);
    }

    public function activeVariantSets(): HasMany
    {
        return $this->variantSets()
            ->where('is_active', 1)
            ->whereNull('deleted_at');
    }

    public function finishingLinks(): HasMany
    {
        return $this->hasMany(ProductFinishingLink::class)
            ->where('is_active', 1)
            ->whereNull('deleted_at');
    }

    public function finishings(): BelongsToMany
    {
        return $this->belongsToMany(Product::class, 'product_finishing_links', 'product_id', 'finishing_product_id')
            ->withPivot(['is_required', 'default_qty', 'min_qty', 'max_qty', 'is_active', 'deleted_at'])
            ->wherePivotNull('deleted_at')
            ->wherePivot('is_active', 1);
    }

    public function pricings(): HasMany
    {
        return $this->hasMany(ProductPricing::class);
    }

    public function publicPricing(): HasOne
    {
        return $this->hasOne(ProductPricing::class)
            ->where('context', 'public')
            ->whereNull('working_group_id')
            ->where('is_active', 1)
            ->whereNull('deleted_at');
    }

    public function workingGroupPricing(WorkingGroup|int $wg): HasOne
    {
        $wgId = $wg instanceof WorkingGroup ? $wg->id : $wg;

        return $this->hasOne(ProductPricing::class)
            ->where('context', 'working_group')
            ->where('working_group_id', $wgId)
            ->where('is_active', 1)
            ->whereNull('deleted_at');
    }

    public function productRolls(): HasMany
    {
        return $this->hasMany(ProductRoll::class);
    }

    public function allowedRolls(): BelongsToMany
    {
        return $this->belongsToMany(Roll::class, 'product_rolls')
            ->withPivot(['id', 'is_active', 'min_height_in', 'max_height_in', 'meta', 'deleted_at'])
            ->wherePivotNull('deleted_at')
            ->wherePivot('is_active', 1);
    }

    public function scopeActive(Builder $q): Builder
    {
        return $q->where('status', 'active')->whereNull('deleted_at');
    }

    public function scopeVisibleToPublic(Builder $q): Builder
    {
        return $q->where('visibility', 'public');
    }

    public function scopeType(Builder $q, string $type): Builder
    {
        return $q->where('product_type', $type);
    }

    public function getIsDimensionBasedAttribute(): bool
    {
        return $this->product_type === 'dimension_based';
    }
}

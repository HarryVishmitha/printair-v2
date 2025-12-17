<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProductVariantAvailabilityOverride extends Model
{
    protected $table = 'product_variant_availability_overrides';

    protected $guarded = ['id'];

    protected $casts = [
        'is_enabled' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function variantSet(): BelongsTo
    {
        return $this->belongsTo(ProductVariantSet::class, 'variant_set_id');
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
}


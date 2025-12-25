<?php

namespace App\Models;

use App\Models\Concerns\HasDocumentLock;
use App\Models\Concerns\HasWorkingGroupScope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\{BelongsTo, HasMany, HasOne};
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class Estimate extends Model
{
    use HasFactory, SoftDeletes;
    use HasDocumentLock;
    use HasWorkingGroupScope;

    protected $guarded = ['id'];

    protected $casts = [
        'customer_snapshot' => 'array',
        'meta' => 'array',

        'subtotal' => 'decimal:2',
        'discount_total' => 'decimal:2',
        'tax_total' => 'decimal:2',
        'shipping_fee' => 'decimal:2',
        'other_fee' => 'decimal:2',
        'grand_total' => 'decimal:2',
        'discount_value' => 'decimal:2',

        'valid_until' => 'datetime',
        'sent_at' => 'datetime',
        'accepted_at' => 'datetime',
        'rejected_at' => 'datetime',
        'converted_at' => 'datetime',
        'locked_at' => 'datetime',
    ];

    protected static function booted(): void
    {
        static::creating(function (self $estimate) {
            if (empty($estimate->uuid)) {
                $estimate->uuid = (string) Str::uuid();
            }
        });
    }

    public function workingGroup(): BelongsTo
    {
        return $this->belongsTo(WorkingGroup::class, 'working_group_id');
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class, 'customer_id');
    }

    public function priceTier(): BelongsTo
    {
        return $this->belongsTo(ProductPriceTier::class, 'price_tier_id');
    }

    public function parentEstimate(): BelongsTo
    {
        return $this->belongsTo(self::class, 'parent_estimate_id');
    }

    public function revisions(): HasMany
    {
        return $this->hasMany(self::class, 'parent_estimate_id')->orderByDesc('revision');
    }

    public function items(): HasMany
    {
        return $this->hasMany(EstimateItem::class, 'estimate_id')->orderBy('sort_order');
    }

    public function statusHistories(): HasMany
    {
        return $this->hasMany(EstimateStatusHistory::class, 'estimate_id')->latest('created_at');
    }

    public function shares(): HasMany
    {
        return $this->hasMany(EstimateShare::class, 'estimate_id')->latest('created_at');
    }

    public function order(): HasOne
    {
        return $this->hasOne(Order::class, 'estimate_id');
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updatedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    public function lockedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'locked_by');
    }

    public function scopeStatus(Builder $query, ?string $status): Builder
    {
        if (! $status) {
            return $query;
        }

        return $query->where('status', $status);
    }
}

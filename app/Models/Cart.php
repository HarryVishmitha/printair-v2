<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Cart extends Model
{
    use SoftDeletes;

    protected $guarded = ['id'];

    protected $casts = [
        'meta' => 'array',
    ];

    public function items(): HasMany
    {
        return $this->hasMany(CartItem::class, 'cart_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function workingGroup(): BelongsTo
    {
        return $this->belongsTo(WorkingGroup::class, 'working_group_id');
    }
}


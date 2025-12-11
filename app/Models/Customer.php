<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;

class Customer extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'user_id',
        'working_group_id',
        'customer_code',
        'full_name',
        'email',
        'phone',
        'whatsapp_number',
        'company_name',
        'company_phone',
        'company_reg_no',
        'type',
        'status',
        'email_notifications',
        'sms_notifications',
        'notes',
    ];

    protected $casts = [
        'email_notifications' => 'boolean',
        'sms_notifications'   => 'boolean',
    ];

    /* =======================
     | Relationships
     |======================= */

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function workingGroup()
    {
        return $this->belongsTo(WorkingGroup::class);
    }

    // Later: orders(), estimates(), jobs() etc.

    /* =======================
     | Scopes / Helpers
     |======================= */

    public function scopeSearch($query, ?string $term)
    {
        if (! $term) {
            return $query;
        }

        $like = '%'.$term.'%';

        return $query->where(function ($q) use ($like) {
            $q->where('full_name', 'like', $like)
              ->orWhere('email', 'like', $like)
              ->orWhere('phone', 'like', $like)
              ->orWhere('whatsapp_number', 'like', $like)
              ->orWhere('company_name', 'like', $like);
        });
    }

    public function scopeType($query, ?string $type)
    {
        if ($type) {
            $query->where('type', $type);
        }

        return $query;
    }

    public function scopeStatus($query, ?string $status)
    {
        if ($status) {
            $query->where('status', $status);
        }

        return $query;
    }

    public function scopeWorkingGroup($query, ?int $wgId)
    {
        if ($wgId) {
            $query->where('working_group_id', $wgId);
        }

        return $query;
    }

    public function isWalkIn(): bool
    {
        return $this->type === 'walk_in';
    }

    public function isCorporate(): bool
    {
        return $this->type === 'corporate';
    }

    public function isAccount(): bool
    {
        return $this->type === 'account';
    }

    public function isActive(): bool
    {
        return $this->status === 'active';
    }

    public function hasLinkedUser(): bool
    {
        return $this->user_id !== null;
    }
}

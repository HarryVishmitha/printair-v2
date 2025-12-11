<?php

namespace App\Models;

use App\Models\ActivityLog;
use App\Notifications\VerifyEmailNotification;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable implements MustVerifyEmail
{
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'first_name',
        'last_name',
        'name',                // keep for Laravel defaults
        'email',
        'password',
        'whatsapp_number',
        'role_id',
        'working_group_id',
        'status',
        'provider_name',
        'provider_id',
        'avatar',
        'last_logged_in_at',
        'login_status'
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'last_logged_in_at' => 'datetime',
            'password' => 'hashed',
            'login_status' => 'boolean',
            'force_password_change' => 'boolean',
        ];
    }

    /* =======================
     |  Relationships
     |======================= */

    public function role()
    {
        return $this->belongsTo(Role::class);
    }

    public function workingGroup()
    {
        return $this->belongsTo(WorkingGroup::class);
    }

    public function addresses()
    {
        return $this->hasMany(Address::class);
    }

    public function primaryAddress()
    {
        return $this->hasOne(Address::class)->where('is_primary', true);
    }

    public function activityLogs()
    {
        return $this->hasMany(ActivityLog::class);
    }

    /**
     * Get the customer profile linked to this user (if any).
     */
    public function customer()
    {
        return $this->hasOne(Customer::class);
    }

    /* =======================
     |  Accessors / Helpers
     |======================= */

    // Always have a usable display name
    public function getNameAttribute($value): string
    {
        if (! empty($value)) {
            return $value;
        }

        return trim(($this->first_name ?? '').' '.($this->last_name ?? ''));
    }

    public function getFullNameAttribute(): string
    {
        return trim(($this->first_name ?? '').' '.($this->last_name ?? ''));
    }

    public function isActive(): bool
    {
        return $this->status === 'active';
    }

    public function isSuspended(): bool
    {
        return $this->status === 'suspended';
    }

    /**
     * Check if user is a Super Admin.
     */
    public function isSuperAdmin(): bool
    {
        return $this->role?->name === 'Super Admin';
    }

    /**
     * Check if user is an Admin.
     */
    public function isAdmin(): bool
    {
        return $this->role?->name === 'Admin';
    }

    /**
     * Check if user is either Admin or Super Admin.
     */
    public function isAdminOrSuperAdmin(): bool
    {
        return $this->isSuperAdmin() || $this->isAdmin();
    }

    public function sendEmailVerificationNotification(): void
    {
        $this->notify(new VerifyEmailNotification);
    }
}

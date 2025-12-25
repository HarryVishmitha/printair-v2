<?php

namespace App\Models;

use App\Models\ActivityLog;
use App\Notifications\VerifyEmailNotification;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
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

    public function createdEstimates(): HasMany
    {
        return $this->hasMany(Estimate::class, 'created_by');
    }

    public function updatedEstimates(): HasMany
    {
        return $this->hasMany(Estimate::class, 'updated_by');
    }

    public function lockedEstimates(): HasMany
    {
        return $this->hasMany(Estimate::class, 'locked_by');
    }

    public function createdOrders(): HasMany
    {
        return $this->hasMany(Order::class, 'created_by');
    }

    public function updatedOrders(): HasMany
    {
        return $this->hasMany(Order::class, 'updated_by');
    }

    public function lockedOrders(): HasMany
    {
        return $this->hasMany(Order::class, 'locked_by');
    }

    public function createdInvoices(): HasMany
    {
        return $this->hasMany(Invoice::class, 'created_by');
    }

    public function updatedInvoices(): HasMany
    {
        return $this->hasMany(Invoice::class, 'updated_by');
    }

    public function lockedInvoices(): HasMany
    {
        return $this->hasMany(Invoice::class, 'locked_by');
    }

    public function receivedPayments(): HasMany
    {
        return $this->hasMany(Payment::class, 'received_by');
    }

    public function createdPayments(): HasMany
    {
        return $this->hasMany(Payment::class, 'created_by');
    }

    public function updatedPayments(): HasMany
    {
        return $this->hasMany(Payment::class, 'updated_by');
    }

    public function estimateStatusChanges(): HasMany
    {
        return $this->hasMany(EstimateStatusHistory::class, 'changed_by');
    }

    public function orderStatusChanges(): HasMany
    {
        return $this->hasMany(OrderStatusHistory::class, 'changed_by');
    }

    public function invoiceStatusChanges(): HasMany
    {
        return $this->hasMany(InvoiceStatusHistory::class, 'changed_by');
    }

    public function createdEstimateShares(): HasMany
    {
        return $this->hasMany(EstimateShare::class, 'created_by');
    }

    public function paymentAllocationsCreated(): HasMany
    {
        return $this->hasMany(PaymentAllocation::class, 'created_by');
    }

    public function pricingAudits()
    {
        return $this->hasMany(PricingAudit::class);
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

    //has role or permission
    public function hasRoleOrPermission($ability): bool
    {
        return $this->hasRole($ability) || $this->hasPermissionTo($ability);
    }
}

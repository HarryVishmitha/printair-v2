<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class WorkingGroup extends Model
{
    use HasFactory;

    /**
     * Constant slug for the system default public working group.
     * Prevents unintended edits/deletions.
     */
    public const PUBLIC_SLUG = 'public';

    /**
     * Mass assignable fields.
     */
    protected $fillable = [
        'slug',
        'name',
        'description',
        'is_shareable',
        'is_restricted',
        'is_staff_group',
    ];

    /**
     * Attribute casting.
     */
    protected function casts(): array
    {
        return [
            'is_shareable' => 'boolean',
            'is_restricted' => 'boolean',
            'is_staff_group' => 'boolean',
        ];
    }

    /**
     * Relationships
     * -----------------------------
     */
    public function users()
    {
        return $this->hasMany(User::class);
    }

    public function customers()
    {
        return $this->hasMany(Customer::class);
    }

    /**
     * Booted Model Events
     * -----------------------------
     */
    protected static function booted()
    {
        // Auto-generate slug when creating.
        static::creating(function (self $group) {
            if (empty($group->slug)) {
                $group->slug = Str::slug($group->name);
            }
        });

        // Prevent modifications to the public group slug.
        static::updating(function (self $group) {
            if ($group->getOriginal('slug') === self::PUBLIC_SLUG) {
                $group->slug = self::PUBLIC_SLUG;
            }
        });

        // Prevent deletion of the public group.
        static::deleting(function (self $group) {
            if ($group->slug === self::PUBLIC_SLUG) {
                throw new \Exception('The public working group cannot be deleted.');
            }
        });
    }

    /**
     * Scopes
     * -----------------------------
     */

    /**
     * Search across name, slug, description.
     */
    public function scopeSearch($query, ?string $term)
    {
        if (! $term) {
            return $query;
        }

        $term = trim($term);

        return $query->where(function ($q) use ($term) {
            $q->where('name', 'like', "%{$term}%")
                ->orWhere('slug', 'like', "%{$term}%")
                ->orWhere('description', 'like', "%{$term}%");
        });
    }

    /**
     * Only shareable groups.
     */
    public function scopeShareable($query)
    {
        return $query->where('is_shareable', true);
    }

    /**
     * Only restricted groups.
     */
    public function scopeRestricted($query)
    {
        return $query->where('is_restricted', true);
    }

    /**
     * Only staff groups.
     */
    public function scopeStaff($query)
    {
        return $query->where('is_staff_group', true);
    }

    /**
     * Scope to get the public working group.
     */
    public function scopePublic($query)
    {
        return $query->where('slug', self::PUBLIC_SLUG);
    }

    /**
     * Get the public working group instance.
     */
    public static function getPublic(): ?self
    {
        return static::where('slug', self::PUBLIC_SLUG)->first();
    }

    /**
     * Get the public working group ID.
     */
    public static function getPublicId(): ?int
    {
        return static::where('slug', self::PUBLIC_SLUG)->value('id');
    }
}

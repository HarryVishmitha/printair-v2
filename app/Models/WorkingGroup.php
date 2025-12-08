<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WorkingGroup extends Model
{
    use HasFactory;

    protected $fillable = [
        'slug',
        'name',
        'description',
        'is_shareable',
        'is_restricted',
        'is_staff_group',
    ];

    protected function casts(): array
    {
        return [
            'is_shareable'   => 'boolean',
            'is_restricted'  => 'boolean',
            'is_staff_group' => 'boolean',
        ];
    }

    public function users()
    {
        return $this->hasMany(User::class);
    }
}

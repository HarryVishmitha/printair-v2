<?php

namespace App\Models\Concerns;

use Illuminate\Database\Eloquent\Builder;

trait HasWorkingGroupScope
{
    public function scopeForWorkingGroup(Builder $query, int $wgId): Builder
    {
        return $query->where('working_group_id', $wgId);
    }
}


<?php

namespace App\Models\Concerns;

use Illuminate\Database\Eloquent\Builder;

trait HasDocumentLock
{
    public function scopeUnlocked(Builder $query): Builder
    {
        return $query->whereNull('locked_at');
    }

    public function scopeLocked(Builder $query): Builder
    {
        return $query->whereNotNull('locked_at');
    }

    public function isLocked(): bool
    {
        return ! is_null($this->locked_at);
    }
}


<?php

namespace App\Traits;

trait HasCommonScopes
{
    public function scopeActive($query)
    {
        return $query->whereNull('deleted_at');
    }

    public function scopeRecent($query, $days = 7)
    {
        return $query->where('created_at', '>=', now()->subDays($days));
    }

    public function scopePopular($query)
    {
        // نضيف منطق المشاهدة لاحقاً
        return $query;
    }
}

<?php

namespace App\Models;

class Manuscript extends Entity
{
    protected $table = 'manuscripts';

    protected $fillable = [
        'title', 'slug', 'author', 'century',
        'language', 'pages', 'publisher', 'location',
        'created_at', 'updated_at'
    ];

    /**
     * خصائص إضافية للمخطوطة
     */

    public function getCenturyDisplayAttribute(): string
    {
        $century = $this->century;

        if ($century <= 0) {
            return "قبل الميلاد";
        }

        $hijriCentury = $century - 600; // تقدير تقريبي
        return "القرن {$century} الميلادي (القرن {$hijriCentury} الهجري تقريباً)";
    }

    public function getAgeAttribute(): int
    {
        $currentYear = date('Y');
        $centuryStart = ($this->century - 1) * 100 + 1;
        return $currentYear - $centuryStart;
    }

    public function getPagesFormattedAttribute(): string
    {
        return "{$this->pages} صفحة";
    }

    public function isAncient(): bool
    {
        return $this->century < 15; // قبل القرن 15
    }

    public function isModern(): bool
    {
        return $this->century >= 19; // بعد القرن 19
    }
}

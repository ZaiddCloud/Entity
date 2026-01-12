<?php

namespace App\Models;

/**
 * @property string $id
 * @property string $title
 * @property string $slug
 * @property string|null $author
 * @property int|null $century
 * @property string|null $language
 * @property int|null $pages
 * @property string|null $publisher
 * @property string|null $location
 * @property string|null $description
 * @property string|null $cover_path
 * @property string|null $file_path
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read string $century_display
 * @property-read int $age
 * @property-read string $pages_formatted
 */
class Manuscript extends Entity
{
    protected $table = 'manuscripts';

    protected $fillable = [
        'title',
        'slug',
        'author',
        'century',
        'language',
        'pages',
        'publisher',
        'location',
        'description',
        'cover_path',
        'file_path',
        'created_at',
        'updated_at'
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

    /**
     * العلاقة مع صفحات المخطوطة في MongoDB
     */
    public function children()
    {
        return $this->hasMany(ManuscriptPage::class, 'manuscript_id', 'id');
    }
}

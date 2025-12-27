<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Relations\MorphToMany;
use Illuminate\Support\Str;

use Illuminate\Database\Eloquent\SoftDeletes;

class Category extends Model
{
    /** @use HasFactory<\Database\Factories\CategoryFactory> */
    use HasFactory, HasUuids, SoftDeletes, \App\Traits\TaxonomyRelationships;

    protected function getTaxonomyPivotTable(): string
    {
        return 'categorizables';
    }

    protected $fillable = [
        'name',
        'slug',
        'description',
        'parent_id',
    ];

    /**
     * The "booted" method of the model.
     */
    protected static function booted()
    {
        static::creating(function ($category) {
            if (empty($category->slug)) {
                $category->slug = static::generateUniqueSlug($category->name);
            }
        });
    }


    /**
     * Generate a unique slug for the category.
     */
    protected static function generateUniqueSlug($name)
    {
        $slug = Str::slug($name, '-', null);
        $originalSlug = $slug;
        $count = 1;

        // التحقق من وجود slug مشابه
        while (static::where('slug', $slug)->exists()) {
            $slug = $originalSlug . '-' . $count;
            $count++;
        }

        return $slug;
    }

    /**
     * علاقة مع الكاتيجوري الأب.
     */
    public function parent(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Category::class, 'parent_id');
    }

    /**
     * علاقة مع الكاتيجوريات الفرعية.
     */
    public function children(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(Category::class, 'parent_id');
    }

    // app/Models/Category.php
    public function getHierarchyPath(): array
    {
        $path = [];
        $current = $this;

        while ($current) {
            $path[] = [
                'id' => $current->id,
                'name' => $current->name,
                'slug' => $current->slug,
            ];
            $current = $current->parent;
        }

        return array_reverse($path);
    }

    public function getBreadcrumbs(): string
    {
        $path = $this->getHierarchyPath();
        return collect($path)->pluck('name')->join(' > ');
    }
}

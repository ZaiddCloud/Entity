<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Relations\MorphToMany;
use Illuminate\Support\Str;
use App\Models\Entity;
/**
 * @property string $id
 * @property string $name
 * @property string $slug
 * @property string $type
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Support\Collection $entities
 * @property-read int $entities_count
 */
class Tag extends Model
{
    use \App\Traits\HasPolymorphicRelations;
    use \Illuminate\Database\Eloquent\Factories\HasFactory;
    use HasUuids, \App\Traits\TaxonomyRelationships;

    protected function getTaxonomyPivotTable(): string
    {
        return 'taggables';
    }

    protected $fillable = ['name', 'slug', 'type'];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($tag) {
            if (empty($tag->slug)) {
                $tag->slug = \App\Helpers\SlugHelper::generate($tag->name);
            }
        });
    }


    /**
     * Accessor للـ Entities
     */
    public function getEntitiesAttribute()
    {
        return $this->getEntitiesFromTags();
    }

    /**
     * alias للتوافق
     */
    public function getTaggablesAttribute()
    {
        return $this->entities;
    }

    /**
     * عدد الـ Entities
     */
    public function getEntitiesCountAttribute(): int
    {
        return $this->entities->count();
    }

    /**
     * علاقة مع جميع الـ Entities (accessor بدلاً من relationship)
     */
    /*public function getTaggablesAttribute()
    {
        $results = collect();

        $types = [
            Book::class,
            Video::class,
            Audio::class,
            Manuscript::class,
        ];

        foreach ($types as $type) {
            if (class_exists($type)) {
                $entities = $type::whereHas('tags', function ($query) {
                    $query->where('tags.id', $this->id);
                })->get();

                $results = $results->merge($entities);
            }
        }

        return $results;
    }*/

    /**
     * alias للتوافق
     */
    /*public function getEntitiesAttribute()
    {
        return $this->taggables;
    }*/

    // ... باقي الدوال تبقى لكن تعدّل لاستخدام $this->taggables
    /*public function getEntitiesCountAttribute(): int
    {
        return $this->taggables->count();
    }*/

    /*public function getEntitiesByTypeAttribute(): array
    {
        $grouped = [];

        foreach ($this->taggables as $entity) {
            $type = class_basename($entity);
            if (!isset($grouped[$type])) {
                $grouped[$type] = [];
            }
            $grouped[$type][] = $entity;
        }

        return $grouped;
    }*/

    /**
     * فلترة حسب نوع معين
     */
    /*public function entitiesOfType(string $entityClass)
    {
        return $this->taggables->filter(function ($entity) use ($entityClass) {
            return get_class($entity) === $entityClass;
        });
    }*/

    /**
     * هل الـ Tag مرتبط بنوع معين
     */
    /*public function hasEntityType(string $entityClass): bool
    {
        return $this->taggables->contains(function ($entity) use ($entityClass) {
            return get_class($entity) === $entityClass;
        });
    }*/

    /*public function entities(): MorphToMany
    {
        return $this->morphedByMany(Entity::class, 'entity', 'taggables');
    }*/
}

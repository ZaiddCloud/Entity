<?php

namespace App\Models;

use App\Traits\HasCommonScopes;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;
use App\Traits\HasPolymorphicRelations;

abstract class Entity extends Model
{
    use HasFactory, SoftDeletes, HasPolymorphicRelations, HasCommonScopes, HasUuids;


    /**
     * الخصائص المشتركة لجميع Entities
     */
    protected $fillable = ['title', 'slug'];

    protected $dates = ['deleted_at'];

    protected static function booted()
    {
        static::saving(function ($entity) {
            if (empty($entity->slug)) {
                $entity->slug = Str::slug($entity->title) ?: Str::uuid()->toString();
            }
        });
    }

    /**
     * Use slug instead of UUID for routing.
     */
    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    /**
     * الخصائص المشتركة
     */
    public function getTypeAttribute(): string
    {
        return class_basename($this);
    }

    public static function getCached($id)
    {
        $cacheKey = "entity." . static::class . ".{$id}";

        return Cache::remember($cacheKey, now()->addHours(1), function () use ($id) {
            return static::find($id);
        });
    }

    /**
     * جلب الـ Entity مع العلاقات من الكاش
     */
    public function getCachedWithRelations()
    {
        $cacheKey = "entity." . $this->getMorphClass() . ".{$this->getKey()}.with_relations";

        return Cache::remember($cacheKey, now()->addHours(1), function () {
            return $this->load(['tags', 'categories', 'activities', 'comments', 'reviews']);
        });
    }

    /**
     * الحصول على الإحصائيات من الكاش
     */
    public function getCachedStats()
    {
        $cacheKey = "entity." . $this->getMorphClass() . ".{$this->getKey()}.stats";

        return Cache::remember($cacheKey, now()->addMinutes(30), function () {
            return [
                'views_count' => $this->views()->count(),
                'favourites_count' => $this->favourites()->count(),
                'comments_count' => $this->comments()->count(),
                'reviews_count' => $this->reviews()->count(),
                'average_rating' => $this->reviews()->avg('rating') ?? 0,
                'tags_count' => $this->tags()->count(),
                'categories_count' => $this->categories()->count(),
                'last_activity' => $this->activities()->latest()->first()?->created_at,
            ];
        });
    }
}

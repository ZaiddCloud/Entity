<?php

namespace App\Services;

use App\Models\Book;
use App\Models\Video;
use App\Models\Audio;
use App\Models\Manuscript;
use App\Models\Entity;
use Illuminate\Support\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

class EntityQueryService
{
    /**
     * البحث عبر جميع الـ Entities
     */
    public function search(string $query): Collection
    {
        $results = new Collection();

        foreach ($this->getEntityClasses() as $entityClass) {
            $entities = $entityClass::where('title', 'like', "%{$query}%")
                ->orWhere(function ($q) use ($query) {
                    // يمكن إضافة حقول أخرى لاحقاً
                })
                ->get();

            $results = $results->merge($entities);
        }

        return $results;
    }

    /**
     * فلترة الـ Entities
     */
    public function filter(array $filters = []): Collection
    {
        $results = new Collection();
        $entityClasses = $this->getEntityClasses();

        // فلترة حسب النوع إذا كان محدداً
        if (isset($filters['type'])) {
            $entityClasses = [$this->resolveEntityClass($filters['type'])];
            unset($filters['type']);
        }

        foreach ($entityClasses as $entityClass) {
            $query = $entityClass::query();

            // بحث نصي
            if (isset($filters['search'])) {
                $query->where('title', 'like', "%{$filters['search']}%");
                unset($filters['search']);
            }

            // تطبيق باقي الفلاتر
            foreach ($filters as $key => $value) {
                if (in_array($key, (new $entityClass())->getFillable())) {
                    $query->where($key, $value);
                }
            }

            $results = $results->merge($query->get());
        }

        return $results;
    }

    /**
     * Pagination
     */
    public function paginate(int $perPage = 15): LengthAwarePaginator
    {
        $results = new Collection();

        foreach ($this->getEntityClasses() as $entityClass) {
            $results = $results->merge($entityClass::all());
        }

        // Paginate manually since we have merged collections
        $page = request()->get('page', 1);
        $offset = ($page - 1) * $perPage;

        $paginated = $results->slice($offset, $perPage)->values();

        return new LengthAwarePaginator(
            $paginated,
            $results->count(),
            $perPage,
            $page,
            ['path' => request()->url(), 'query' => request()->query()]
        );
    }

    /**
     * البحث حسب Tag
     */
    public function searchByTag(string $tagName): Collection
    {
        $results = new Collection();

        foreach ($this->getEntityClasses() as $entityClass) {
            $entities = $entityClass::whereHas('tags', function ($query) use ($tagName) {
                $query->where('name', $tagName);
            })->get();

            $results = $results->merge($entities);
        }

        return $results;
    }

    /**
     * الحصول على أحدث الـ Entities
     */
    public function recent(int $days = 7): Collection
    {
        $results = new Collection();
        $date = now()->subDays($days);

        foreach ($this->getEntityClasses() as $entityClass) {
            $entities = $entityClass::where('created_at', '>=', $date)->get();
            $results = $results->merge($entities);
        }

        return $results;
    }

    /**
     * الحصول على أشهر الـ Entities
     */
    public function popular(int $limit = 10): Collection
    {
        $results = new Collection();

        foreach ($this->getEntityClasses() as $entityClass) {
            $entities = $entityClass::withCount('activities')
                ->orderBy('activities_count', 'desc')
                ->limit($limit)
                ->get();

            $results = $results->merge($entities);
        }

        return $results->sortByDesc('activities_count')->take($limit);
    }

    /**
     * الحصول على جميع أنواع الـ Entities
     */
    private function getEntityClasses(): array
    {
        return [
            Book::class,
            Video::class,
            Audio::class ?? null,      // إذا كان موجوداً
            Manuscript::class ?? null, // إذا كان موجوداً
        ];
    }

    /**
     * تحويل type إلى entity class
     */
    private function resolveEntityClass(string $type): string
    {
        return match($type) {
            'book' => Book::class,
            'video' => Video::class,
            'audio' => Audio::class,
            'manuscript' => Manuscript::class,
            default => throw new \InvalidArgumentException("نوع غير معروف: {$type}")
        };
    }
}

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
        return $this->queryAllEntities(function ($q) use ($query) {
            $q->where('title', 'like', "%{$query}%");
            // يمكن إضافة حقول أخرى لاحقاً
        });
    }

    /**
     * فلترة الـ Entities
     */
    public function filter(array $filters = []): Collection
    {
        $entityClasses = $this->getEntityClasses();

        // فلترة حسب النوع إذا كان محدداً
        if (isset($filters['type'])) {
            $entityClasses = [$this->resolveEntityClass($filters['type'])];
            unset($filters['type']);
        }

        $results = new Collection();
        foreach ($entityClasses as $entityClass) {
            $query = $entityClass::query();

            // بحث نصي
            if (isset($filters['search'])) {
                $query->where('title', 'like', "%{$filters['search']}%");
            }

            // تطبيق باقي الفلاتر
            foreach ($filters as $key => $value) {
                if ($key !== 'search' && in_array($key, (new $entityClass())->getFillable())) {
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
        $results = $this->queryAllEntities(fn($q) => $q);

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
        return $this->queryAllEntities(function ($query) use ($tagName) {
            $query->whereHas('tags', function ($q) use ($tagName) {
                $q->where('name', $tagName);
            });
        });
    }

    /**
     * الحصول على أحدث الـ Entities
     */
    public function recent(int $days = 7): Collection
    {
        $date = now()->subDays($days);
        return $this->queryAllEntities(fn($q) => $q->where('created_at', '>=', $date));
    }

    /**
     * الحصول على أشهر الـ Entities
     */
    public function popular(int $limit = 10): Collection
    {
        $results = $this->queryAllEntities(function ($query) use ($limit) {
            $query->withCount('activities')
                ->orderBy('activities_count', 'desc')
                ->limit($limit);
        });

        return $results->sortByDesc('activities_count')->take($limit);
    }

    /**
     * دالة مساعدة لتنفيذ الاستعلام على جميع أنواع الـ Entities
     */
    protected function queryAllEntities(callable $callback): Collection
    {
        $results = new Collection();

        foreach ($this->getEntityClasses() as $entityClass) {
            $query = $entityClass::query();
            $callback($query);
            $results = $results->merge($query->get());
        }

        return $results;
    }

    /**
     * الحصول على جميع أنواع الـ Entities
     */
    private function getEntityClasses(): array
    {
        return [
            Book::class,
            Video::class,
            Audio::class,
            Manuscript::class,
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

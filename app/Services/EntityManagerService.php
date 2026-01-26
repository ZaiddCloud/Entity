<?php

namespace App\Services;

use App\Enums\EntityType;
use App\Models\Entity;
use App\Models\Book;
use App\Models\Video;
use App\Models\Audio;
use App\Models\Manuscript;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class EntityManagerService
{
    /**
     * إنشاء entity جديد
     */
    public function create(array $data): Entity
    {
        $this->validateCreation($data);

        $type = $data['type'];
        /** @var class-string<Entity> $entityClass */
        $entityClass = $this->resolveEntityClass($type);

        /** @var Entity $entity */
        $entity = $entityClass::query()->create($data);

        $this->logActivity($entity, 'created', "تم إنشاء {$type} جديد: {$entity->title}", [
            'after' => $entity->toArray()
        ]);

        return $entity;
    }

    /**
     * تحديث entity موجود
     */
    public function update(Entity $entity, array $data): bool
    {
        $this->validateUpdate($entity, $data);

        $oldData = $entity->only(array_keys($data));
        $success = $entity->update($data);

        if ($success) {
            $this->logActivity($entity, 'updated', "تم تحديث الـ " . class_basename($entity) . ": {$entity->title}", [
                'before' => $oldData,
                'after' => $entity->only(array_keys($data))
            ]);
        }

        return $success;
    }

    /**
     * حذف entity (soft delete)
     */
    public function delete(Entity $entity): bool
    {
        \App\Models\Deletion::query()->create([
            'entity_id' => $entity->id,
            'entity_type' => get_class($entity),
            'user_id' => \Illuminate\Support\Facades\Auth::id() ?? \App\Models\User::first()?->id,
            'reason' => 'حذف من لوحة التحكم',
            'data' => $entity->toArray()
        ]);

        return $entity->delete();
    }

    /**
     * استعادة entity محذوف
     */
    public function restore(Entity $entity): bool
    {
        $success = $entity->restore();
        if ($success) {
            $this->logActivity($entity, 'restored', "تم استعادة الـ " . class_basename($entity) . ": {$entity->title}");
        }
        return $success;
    }

    /**
     * تسجيل النشاط في قاعدة البيانات
     */
    protected function logActivity(Entity $entity, string $type, string $description, array $changes = []): void
    {
        \App\Models\Activity::query()->create([
            'user_id' => \Illuminate\Support\Facades\Auth::id() ?? \App\Models\User::first()?->id,
            'entity_id' => $entity->id,
            'entity_type' => get_class($entity),
            'activity_type' => $type,
            'description' => $description,
            'changes' => $changes
        ]);
    }

    /**
     * التحقق من صحة بيانات الإنشاء
     */
    private function validateCreation(array $data): void
    {
        $rules = [
            'title' => 'required|string|max:255',
            'type' => 'required|in:' . implode(',', EntityType::values())
        ];

        // إضافة شروط خاصة بكل نوع
        if (isset($data['type'])) {
            $entityType = EntityType::tryFrom($data['type']);
            
            if ($entityType === EntityType::MANUSCRIPT) {
                $rules['century'] = 'nullable|integer';
            } elseif ($entityType === EntityType::BOOK) {
                $rules['author'] = 'nullable|string|max:255';
            }
        }

        $validator = Validator::make($data, $rules);

        if ($validator->fails()) {
            throw new ValidationException($validator);
        }
    }

    /**
     * التحقق من صحة بيانات التحديث
     */
    private function validateUpdate(Entity $entity, array $data): void
    {
        $validator = Validator::make($data, [
            'title' => 'sometimes|string|max:255'
        ]);

        if ($validator->fails()) {
            throw new ValidationException($validator);
        }
    }

    /**
     * تحديد class الـ entity بناءً على النوع
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

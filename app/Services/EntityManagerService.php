<?php

namespace App\Services;

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

        $entityClass = $this->resolveEntityClass($data['type']);

        $entity = $entityClass::create($data);

        \App\Models\Activity::create([
            'user_id' => auth()->id() ?? \App\Models\User::first()?->id,
            'entity_id' => $entity->id,
            'entity_type' => get_class($entity),
            'activity_type' => 'created',
            'description' => "تم إنشاء " . $data['type'] . " جديد: " . $entity->title,
            'changes' => ['after' => $entity->toArray()]
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
            \App\Models\Activity::create([
                'user_id' => auth()->id() ?? \App\Models\User::first()?->id,
                'entity_id' => $entity->id,
                'entity_type' => get_class($entity),
                'activity_type' => 'updated',
                'description' => "تم تحديث الـ " . class_basename($entity) . ": " . $entity->title,
                'changes' => [
                    'before' => $oldData,
                    'after' => $entity->only(array_keys($data))
                ]
            ]);
        }

        return $success;
    }

    /**
     * حذف entity (soft delete)
     */
    public function delete(Entity $entity): bool
    {
        \App\Models\Deletion::create([
            'entity_id' => $entity->id,
            'entity_type' => get_class($entity),
            'user_id' => auth()->id() ?? \App\Models\User::first()?->id,
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
        return $entity->restore();
    }

    /**
     * التحقق من صحة بيانات الإنشاء
     */
    private function validateCreation(array $data): void
    {
        $rules = [
            'title' => 'required|string|max:255',
            'type' => 'required|in:book,video,audio,manuscript'
        ];

        // إضافة شروط خاصة بكل نوع إذا لزم الأمر
        if (isset($data['type'])) {
            if ($data['type'] === 'manuscript') {
                $rules['century'] = 'nullable|integer';
            } elseif ($data['type'] === 'book') {
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

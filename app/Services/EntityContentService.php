<?php

namespace App\Services;

use App\Models\Entity;
use App\Models\EntityContent;
use Illuminate\Validation\ValidationException;

class EntityContentService
{
    /**
     * الأنواع المسموحة لكل كيان (Allowed Content Types)
     * هذا هو "الدستور" الذي يمنع الفوضى في قاعدة البيانات
     */
    protected array $allowedTypes = [
        'book' => ['chapter', 'page', 'section', 'part'],
        'manuscript' => ['page', 'folio', 'section'],
        'audio' => ['segment', 'track', 'marker'],
        'video' => ['segment', 'scene', 'shot'],
    ];

    /**
     * إنشاء محتوى جديد للكيان
     */
    public function createNode(Entity $entity, array $data): EntityContent
    {
        $entityType = strtolower(class_basename($entity));
        $contentType = $data['type'] ?? null;

        if (!$contentType) {
            throw ValidationException::withMessages(['type' => 'Content type is required']);
        }

        // التحقق من أن نوع المحتوى مسموح لهذا الكيان
        $allowed = $this->allowedTypes[$entityType] ?? [];

        // إذا كان الكيان غير معرف في القائمة، نمنع الإضافة مبدئياً لضمان الأمان
        if (empty($allowed)) {
            throw ValidationException::withMessages([
                'entity_type' => "Entity type '{$entityType}' is not configured for content creation."
            ]);
        }

        if (!in_array($contentType, $allowed)) {
            throw ValidationException::withMessages([
                'type' => "Content type '{$contentType}' is not allowed for '{$entityType}'. Allowed: " . implode(', ', $allowed)
            ]);
        }

        // إنشاء المحتوى
        // نستخدم array_merge لضمان أن entity_id و entity_type هما الصحيحين
        return EntityContent::create(array_merge($data, [
            'entity_id' => $entity->id,
            'entity_type' => $entityType,
        ]));
    }

    /**
     * تحضير بيانات المحرر (Data Preparation)
     */
    public function prepareEditorData(Entity $entity, string $slug): array
    {
        $node = EntityContent::where('entity_id', $entity->id)
            ->where('entity_type', strtolower(class_basename($entity)))
            ->where('slug', $slug)
            ->firstOrFail();

        // 2. جلب الهيكلية (Hierarchy)
        $hierarchy = EntityContent::where('entity_id', $entity->id)
            ->where('entity_type', strtolower(class_basename($entity)))
            ->orderBy('order')
            ->get(['_id', 'title', 'slug', 'type', 'order', 'parent_id']);

        // 3. حساب الملاحة (Navigation)
        $prev = EntityContent::where('entity_id', $entity->id)
            ->where('entity_type', strtolower(class_basename($entity)))
            ->where('order', '<', $node->order)
            ->orderBy('order', 'desc')
            ->first(['slug', 'title']);

        $next = EntityContent::where('entity_id', $entity->id)
            ->where('entity_type', strtolower(class_basename($entity)))
            ->where('order', '>', $node->order)
            ->orderBy('order', 'asc')
            ->first(['slug', 'title']);

        // 4. تحضير بيانات المصدر (Resource Data)
        $resourceData = [
            'id' => $entity->id,
            'title' => $entity->title,
            'type' => strtolower(class_basename($entity)),
            'url' => $entity->file_path ? asset('storage/' . $entity->file_path) : null,
        ];

        // بيانات خاصة حسب النوع
        if (class_basename($entity) === 'Audio' || class_basename($entity) === 'Video') {
            $resourceData['duration'] = $entity->duration ?? 0;
        } elseif (in_array(class_basename($entity), ['Manuscript', 'Audio', 'Video'])) {
             // Load versions for Manuscript, Audio, and Video Viewers
             $versions = $entity->versions()->with('publisher')->get(); 
             
             $resourceData['versions'] = $versions->map(function($v) {
                 // Title construction logic
                 $title = "الإصدار " . ($v->edition_number ?? '1'); // Generic fallback
                 
                 // Specific logic per type if needed
                 if ($v->publisher) {
                     $title .= " - " . $v->publisher->name;
                 } elseif ($v->title) {
                     $title = $v->title;
                 }
                 
                 return [
                     'title' => $title,
                     'url' => $v->file_path ? asset('storage/' . $v->file_path) : null
                 ];
             })->toArray();
             
             // Fallback: If no versions, add the main entity file as "Original"
             if (empty($resourceData['versions']) && $entity->file_path) {
                 $resourceData['versions'][] = [
                     'title' => 'الملف الأساسي',
                     'url' => asset('storage/' . $entity->file_path)
                 ];
             }
        }

        return [
            'entity' => $entity,
            'contentNode' => $node,
            'hierarchy' => $hierarchy,
            'navigation' => [
                'prev' => $prev,
                'next' => $next,
            ],
            'editor_mode' => strtolower(class_basename($entity)),
            'resource_data' => $resourceData
        ];
    }
}

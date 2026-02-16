<?php

namespace App\Services;

use App\Models\Entity;
use App\Models\EntityContent;
use App\Models\BookChild;
use App\Models\ManuscriptPage;
use App\Models\AudioSegment;
use App\Models\VideoSegment;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Illuminate\Validation\ValidationException;

class EntityContentService
{
    /**
     * الأنواع المسموحة لكل كيان (Allowed Content Types)
     * هذا هو "الدستور" الذي يمنع الفوضى في قاعدة البيانات
     */
    protected array $allowedTypes = [
        'book' => ['sub-book', 'part', 'bab', 'chapter', 'masalah', 'page', 'section'],
        'manuscript' => ['sub-book', 'part', 'bab', 'chapter', 'masalah', 'page', 'section', 'folio'],
        'audio' => ['segment', 'track', 'marker'],
        'video' => ['segment', 'scene', 'shot'],
    ];

    /**
     * تحديد Model المناسب حسب نوع الـ Entity
     */
    protected function getContentModel(Entity $entity): string
    {
        return match (class_basename($entity)) {
            'Book' => BookChild::class,
            'Manuscript' => ManuscriptPage::class,
            'Audio' => AudioSegment::class,
            'Video' => VideoSegment::class,
            default => EntityContent::class,
        };
    }

    /**
     * تحديد حقل الـ ID المناسب
     */
    protected function getEntityIdField(Entity $entity): string
    {
        return match (class_basename($entity)) {
            'Book' => 'book_id',
            'Manuscript' => 'manuscript_id',
            'Audio' => 'audio_id',
            'Video' => 'video_id',
            default => 'entity_id',
        };
    }

    /**
     * إضافة عقدة جديدة (Add Node Wrapper)
     * تقوم بتجهيز البيانات (Slug, Order) ثم استدعاء createNode
     */
    public function addNode(Entity $entity, string $type, string $title, $time = null, ?string $parentId = null): Model
    {
        $maxOrder = $this->getMaxOrder($entity);
        
        $data = [
            'type' => $type,
            'title' => $title,
            'order' => $maxOrder + 1,
            'slug' => \Illuminate\Support\Str::slug($title) . '-' . uniqid(), // Ensure uniqueness
            'parent_id' => $parentId,
        ];
        
        if ($time !== null && in_array(class_basename($entity), ['Audio', 'Video'])) {
            $data['start_time'] = (float) $time;
            $data['end_time'] = (float) $time + 10; // Default duration 10s
        }
        
        return $this->createNode($entity, $data);
    }

    /**
     * إنشاء محتوى جديد للكيان
     */
    public function createNode(Entity $entity, array $data): Model
    {
        $entityType = strtolower(class_basename($entity));
        $contentType = $data['type'] ?? null;

        if (!$contentType) {
            throw ValidationException::withMessages(['type' => 'Content type is required']);
        }

        // التحقق من أن نوع المحتوى مسموح لهذا الكيان
        $allowed = $this->allowedTypes[$entityType] ?? [];

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
        /** @var class-string<Model> $model */
        $model = $this->getContentModel($entity);
        $idField = $this->getEntityIdField($entity);

        return $model::query()->create(array_merge($data, [
            $idField => $entity->id,
            'entity_type' => $entityType,
            'last_updated' => now(),
        ]));
    }

    /**
     * جلب صفحة محددة
     */
    public function getNode(Entity $entity, string $identifier): ?Model
    {
        /** @var class-string<Model> $model */
        $model = $this->getContentModel($entity);
        $idField = $this->getEntityIdField($entity);

        /** @var Model|null $node */
        $node = $model::query()
            ->where($idField, $entity->id)
            ->where(function ($query) use ($identifier) {
                $query->where('slug', $identifier)
                    ->orWhere('_id', $identifier)
                    ->orWhere('id', $identifier);
            })
            ->first();

        return $node;
    }

    /**
     * Get Node by ID
     */
    public function getNodeById(Entity $entity, string $id): Model
    {
        /** @var class-string<Model> $model */
        $model = $this->getContentModel($entity);
        $idField = $this->getEntityIdField($entity);

        return $model::query()
            ->where($idField, $entity->id)
            ->where('_id', $id)
            ->firstOrFail();
    }

    /**
     * جلب الهيكلية (Hierarchy)
     */
    public function getHierarchy(Entity $entity, ?int $limit = null): Collection
    {
        /** @var class-string<Model> $model */
        $model = $this->getContentModel($entity);
        $idField = $this->getEntityIdField($entity);

        $query = $model::query()
            ->where($idField, $entity->id)
            ->select(['_id', 'title', 'slug', 'type', 'order', 'parent_id', 'start_time']);

        if (in_array(class_basename($entity), ['Audio', 'Video'])) {
            $query->orderBy('start_time', 'asc')->orderBy('order', 'asc');
        } else {
            $query->orderBy('order', 'asc');
        }

        if ($limit) {
            $query->limit($limit);
        }

        return $query->get();
    }

    /**
     * جلب التنقل (prev/next)
     */
    public function getNavigation(Entity $entity, Model $currentNode): array
    {
        /** @var class-string<Model> $model */
        $model = $this->getContentModel($entity);
        $idField = $this->getEntityIdField($entity);

        $order = $currentNode->getAttribute('order');
        if ($order === null) {
            return ['prev' => null, 'next' => null];
        }

        $prev = $model::query()
            ->where($idField, $entity->id)
            ->where('order', '<', (int) $order)
            ->orderBy('order', 'desc')
            ->first(['slug', 'title']);

        $next = $model::query()
            ->where($idField, $entity->id)
            ->where('order', '>', (int) $order)
            ->orderBy('order', 'asc')
            ->first(['slug', 'title']);

        return ['prev' => $prev, 'next' => $next];
    }

    /**
     * تحضير بيانات المحرر (Data Preparation)
     */
    /**
     * تحضير بيانات المحرر (Data Preparation)
     */
    public function prepareEditorData(Entity $entity, ?string $slug = null): array
    {
        $node = null;
        if ($slug) {
            try {
                $node = $this->getNode($entity, $slug);
            } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
                // If specific slug not found, try fallback to first child
                $node = $this->getFirstChild($entity);
            }
        } else {
            // Default to first child if no slug provided
            $node = $this->getFirstChild($entity);
        }

        $hierarchy = $this->getHierarchy($entity, 500);

        // Navigation relies on node, if no node exists (empty entity), nav is null
        $navigation = $node ? $this->getNavigation($entity, $node) : ['prev' => null, 'next' => null];

        $resourceData = [
            'id' => $entity->id,
            'title' => $entity->title,
            'slug' => $entity->slug, // Parent slug
            'type' => strtolower(class_basename($entity)),
            'url' => $entity->file_path ? asset('storage/' . $entity->file_path) : null,
        ];

        // بيانات خاصة حسب النوع
        $type = class_basename($entity);
        if ($type === 'Audio' || $type === 'Video') {
            $resourceData['duration'] = $entity->duration ?? 0;
        }

        if (in_array($type, ['Manuscript', 'Audio', 'Video'])) {
            $versions = $entity->versions()->with('publisher')->get();

            $resourceData['versions'] = $versions->map(function ($v) {
                $title = "الإصدار " . ($v->edition_number ?? '1');
                if ($v->title && (str_contains($v->title, 'النسخة') || str_contains($v->title, 'تسجيل'))) {
                    $title = $v->title;
                }

                if ($v->publisher) {
                    $title .= " - " . $v->publisher->name;
                }

                return [
                    'title' => $title,
                    'url' => $v->file_path ? asset('storage/' . $v->file_path) : null
                ];
            })->toArray();

            if (empty($resourceData['versions']) && $entity->file_path && $type !== 'Manuscript') {
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
            'navigation' => $navigation,
            'editor_mode' => strtolower($type),
            'resource_data' => $resourceData
        ];
    }

    /**
     * Get first child node for an entity
     */
    public function getFirstChild(Entity $entity): ?Model
    {
        /** @var class-string<Model> $model */
        $model = $this->getContentModel($entity);
        $idField = $this->getEntityIdField($entity);

        $query = $model::query()
            ->where($idField, $entity->id);

        if (in_array(class_basename($entity), ['Audio', 'Video'])) {
            $query->orderBy('start_time', 'asc')->orderBy('order', 'asc');
        } else {
            $query->orderBy('order', 'asc');
        }

        return $query->first();
    }

    /**
     * Get maximum order value for entity's content nodes
     */
    public function getMaxOrder(Entity $entity): int
    {
        /** @var class-string<Model> $model */
        $model = $this->getContentModel($entity);
        $idField = $this->getEntityIdField($entity);

        $maxOrder = $model::query()
            ->where($idField, $entity->id)
            ->max('order');

        return $maxOrder ?? 0;
    }

    /**
     * تجميع كافة محتويات الأبناء في نص واحد (Full Transcript)
     */
    public function aggregateFullContent(Entity $entity): string
    {
        $children = $this->getHierarchy($entity);

        // Load full data for children (since getHierarchy might be light)
        $modelClass = $this->getContentModel($entity);
        $foreignKey = $this->getEntityIdField($entity);
        
        // Check if this model supports hierarchical structure
        $supportsHierarchy = in_array(class_basename($entity), ['Book', 'Manuscript']);
        
        if ($supportsHierarchy) {
            // For hierarchical models: only get root nodes, then recurse
            $rootNodes = $entity->children()
                ->whereNull('parent_id')
                ->orderBy('order', 'asc')
                ->get();

            $fullTranscript = '';
            foreach ($rootNodes as $node) {
            $fullTranscript .= $this->renderNodeWithDescendants($entity, $node);
        }
    } else {
            // For flat models (Audio/Video): get all children and render them directly
            $allNodes = $entity->children()->orderBy('order', 'asc')->get();
            
            $fullTranscript = '';
            $type = strtolower(class_basename($entity));
            
            foreach ($allNodes as $node) {
                $title = $node->title ?: "قسم";
                $level = 'h4'; // Use H4 for flat segments/scenes as per standard
                
                $marker = "<{$level} class=\"structure-marker\" ";
                $marker .= "data-segment-link=\"true\" ";
                $marker .= "data-id=\"{$node->id}\" ";
                $marker .= "data-type=\"{$node->type}\" ";
                if (isset($node->start_time)) $marker .= "data-start-time=\"{$node->start_time}\" ";
                $marker .= ">{$title}</{$level}>";
                
                $fullTranscript .= $marker;
                $content = $node->content ?: '';
                
                if ($type === 'video' && empty($content) && $node->description) {
                    $content = "<p>{$node->description}</p>";
                }
                
                $fullTranscript .= $content;
                $fullTranscript .= "<p><br/></p>";
            }
        }

        return $fullTranscript;
    }

    protected function renderNodeWithDescendants(Entity $entity, Model $node): string
    {
        $type = strtolower(class_basename($entity));
        $title = $node->title ?: "قسم";
        
        $level = $this->getHeadingLevel($node);
        $tagClass = ($node->type === 'paragraph') ? 'structure-marker-text' : 'structure-marker';
        
        $marker = "<{$level} class=\"{$tagClass}\" ";
        $marker .= "data-id=\"{$node->id}\" ";
        $marker .= "data-type=\"{$node->type}\" ";
        $marker .= ">";
        $marker .= $node->title ?: $node->content;
        $marker .= "</{$level}>";
        
        $html = $marker;
        $content = $node->content ?: '';
        
        if ($type === 'video' && empty($content) && $node->description) {
            $content = "<p>{$node->description}</p>";
        }
        
        $html .= $content;
        $html .= "<p><br/></p>";

        // Recurse
        $children = $node->children()->orderBy('order', 'asc')->get();
        foreach ($children as $child) {
            $html .= $this->renderNodeWithDescendants($entity, $child);
        }

        return $html;
    }

    /**
     * تحديث محتوى عقدة معينة
     */
    public function updateContent(Entity $entity, $payloadOrContent): bool
    {
        // This method is a placeholder if Controller does direct update.
        // However, if Controller delegates to service as per recent plan:
        // We expect payload to be passed here, but wait...
        // The Controller logic I wrote in step 3817 actually does NOT call service->updateContent
        // It calls $node->update($updateData) directly.
        // So this method is technically not called by my previous controller code.
        // BUT, for consistency with the Interface/Plan, I should add it and maybe refactor Controller later
        // or just leave it as a utility.

        return true;
    }

    /**
     * حذف عقدة محتوى (Node)
     */
    public function deleteNode(Entity $entity, string $nodeId): bool
    {
        $modelClass = $this->getContentModel($entity);
        $foreignKey = $this->getEntityIdField($entity);

        $node = $modelClass::where($foreignKey, $entity->id)
            ->where(function ($query) use ($nodeId) {
                $query->where('slug', $nodeId)
                    ->orWhere('_id', $nodeId)
                    ->orWhere('id', $nodeId);
            })
            ->firstOrFail();

        return $node->delete();
    }

    /**
     * حساب عمق العقدة في الهيكل الشجري
     */
    public function getDepth(Model $node): int
    {
        $depth = 0;
        $current = $node;

        while ($current->parent_id) {
            $depth++;
            $parent = $current->parent;
            if (!$parent) break;
            $current = $parent;
        }

        return $depth;
    }

    /**
     * تحديد مستوى العنوان (H2-H6) بناءً على العمق والنوع
     */
    public function getHeadingLevel(Model $node): string
    {
        if ($node->type === 'paragraph') {
            return 'p';
        }

        $depth = $this->getDepth($node);
        $level = $depth + 2;

        return $level > 6 ? 'h6' : "h{$level}";
    }

    /**
     * جلب كافة المحتوى لفرع شجري بالكامل (Node + Descendants)
     */
    public function getBranchContent(Entity $entity, string $nodeId): string
    {
        $rootNode = $this->getNode($entity, $nodeId);
        if (!$rootNode) return '';

        $allNodes = $this->collectDescendants($rootNode);
        $allNodes->prepend($rootNode);

        $html = '';
        foreach ($allNodes as $node) {
            $level = $this->getHeadingLevel($node);
            $tagClass = $level === 'p' ? 'structure-marker-text' : 'structure-marker';
            
            $marker = "<{$level} class=\"{$tagClass}\" ";
            $marker .= "data-segment-link=\"true\" ";
            $marker .= "data-id=\"{$node->id}\" ";
            $marker .= "data-type=\"{$node->type}\" ";
            if ($node->start_time !== null) {
                $marker .= "data-start-time=\"{$node->start_time}\" ";
            }
            $marker .= ">{$node->title}</{$level}>";

            \Illuminate\Support\Facades\Log::info('[EntityContentService] Branch Marker generated:', ['html' => $marker, 'id' => $node->id]);

            $html .= $marker;
            $html .= $node->content ?? '';
            $html .= "<p><br/></p>";
        }

        return $html;
    }

    /**
     * جمع كافة الأبناء والأحفاد تتابعياً (Recursive Collection)
     */
    protected function collectDescendants(Model $node): Collection
    {
        $descendants = collect();

        foreach ($node->children as $child) {
            $descendants->push($child);
            $descendants = $descendants->merge($this->collectDescendants($child));
        }

        return $descendants;
    }
}

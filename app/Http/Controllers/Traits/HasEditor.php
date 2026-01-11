<?php

namespace App\Http\Controllers\Traits;

use App\Models\Entity;
use App\Services\EntityContentService;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;

trait HasEditor
{
    /**
     * The editor route handler
     */
    public function editor(\Illuminate\Http\Request $request, $entity, $child)
    {
        $model = $this->resolveModel($request);
        return $this->renderEditor($model, $child);
    }

    /**
     * عرض صفحة المحرر الموحدة
     */
    protected function renderEditor(\Illuminate\Database\Eloquent\Model $entity, string $childSlug)
    {
        // التحقق من الصلاحية
        Gate::authorize('update', $entity);

        // استخدام الخدمة الموحدة لتحضير البيانات
        $service = app(EntityContentService::class);
        $data = $service->prepareEditorData($entity, $childSlug);

        // توجيه الاستجابة إلى صفحة المحرر العامة
        return Inertia::render('Editor/EditorPage', $data);
    }
}

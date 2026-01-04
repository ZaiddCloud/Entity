<?php

namespace App\Http\Controllers\Traits;

use App\Models\Entity;
use App\Services\EntityContentService;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;

trait HasEditor
{
    /**
     * عرض صفحة المحرر الموحدة
     * يتم استدعاؤها من داخل دالة editor() في المتحكمات
     */
    protected function renderEditor(Entity $entity, string $childSlug)
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

<?php

namespace App\Http\Controllers;

use App\Models\Series;

/**
 * SeriesController - Refactored to use EntityController Hooks
 */
class SeriesController extends EntityController
{
    //Configuration
    protected function getModelClass(): string { return Series::class; }
    protected function getViewPath(): string { return 'Series'; }
    protected function getRouteName(): string { return 'series'; }
    protected function getStoreRequestClass(): ?string { return \App\Http\Requests\StoreSeriesRequest::class; }
    protected function getUpdateRequestClass(): ?string { return \App\Http\Requests\UpdateSeriesRequest::class; }

    //Customization
    protected function getRelations(): array { return ['books', 'videos', 'audio', 'manuscripts']; }
    protected function getSearchFields(): array { return ['title', 'description']; }
    protected function getPerPage(): int { return 15; }

    protected function getCreateSuccessMessage(): string { return 'تم إنشاء السلسلة بنجاح'; }
    protected function getUpdateSuccessMessage(): string { return 'تم تحديث السلسلة بنجاح'; }
    protected function getDeleteSuccessMessage(): string { return 'تم حذف السلسلة بنجاح'; }
}

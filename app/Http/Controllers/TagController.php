<?php

namespace App\Http\Controllers;

use App\Models\Tag;

/**
 * TagController - Refactored to use EntityController Hooks
 */
class TagController extends EntityController
{
    //Configuration
    protected function getModelClass(): string { return Tag::class; }
    protected function getViewPath(): string { return 'Tags'; }
    protected function getRouteName(): string { return 'tags'; }
    protected function getStoreRequestClass(): ?string { return \App\Http\Requests\StoreTagRequest::class; }
    protected function getUpdateRequestClass(): ?string { return \App\Http\Requests\UpdateTagRequest::class; }

    //Customization
    protected function getRelations(): array { return ['books', 'videos', 'audio', 'manuscripts']; }
    protected function getSearchFields(): array { return ['name']; }
    protected function getPerPage(): int { return 30; }

    protected function getCreateSuccessMessage(): string { return 'تم إنشاء الوسم بنجاح'; }
    protected function getUpdateSuccessMessage(): string { return 'تم تحديث الوسم بنجاح'; }
    protected function getDeleteSuccessMessage(): string { return 'تم حذف الوسم بنجاح'; }
}

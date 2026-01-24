<?php

namespace App\Http\Controllers;

use App\Models\Publisher;

/**
 * PublisherController - Refactored to use EntityController Hooks
 */
class PublisherController extends EntityController
{
    //Configuration
    protected function getModelClass(): string { return Publisher::class; }
    protected function getViewPath(): string { return 'Publishers'; }
    protected function getRouteName(): string { return 'publishers'; }
    protected function getStoreRequestClass(): ?string { return \App\Http\Requests\StorePublisherRequest::class; }
    protected function getUpdateRequestClass(): ?string { return \App\Http\Requests\UpdatePublisherRequest::class; }

    //Customization
    protected function getRelations(): array { return ['books', 'videos', 'audios', 'manuscripts']; }
    protected function getSearchFields(): array { return ['name']; }
    protected function getPerPage(): int { return 12; }
    protected function getFileUploads(): array { return ['logo' => 'logos']; }

    protected function getCreateSuccessMessage(): string { return 'تم إنشاء دار النشر بنجاح'; }
    protected function getUpdateSuccessMessage(): string { return 'تم تحديث دار النشر بنجاح'; }
    protected function getDeleteSuccessMessage(): string { return 'تم حذف دار النشر بنجاح'; }
}

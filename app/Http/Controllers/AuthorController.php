<?php

namespace App\Http\Controllers;

use App\Models\Author;
use Illuminate\Http\Request;

/**
 * AuthorController - Refactored to use EntityController Hooks
 */
class AuthorController extends EntityController
{
    //Configuration
    protected function getModelClass(): string { return Author::class; }
    protected function getViewPath(): string { return 'Authors'; }
    protected function getRouteName(): string { return 'authors'; }
    protected function getStoreRequestClass(): ?string { return \App\Http\Requests\StoreAuthorRequest::class; }
    protected function getUpdateRequestClass(): ?string { return \App\Http\Requests\UpdateAuthorRequest::class; }

    //Customization
    protected function getRelations(): array { return ['books', 'videos', 'audios', 'manuscripts']; }
    protected function getWithCount(): array { return ['books', 'videos', 'audios', 'manuscripts']; }
    protected function getSearchFields(): array { return ['name', 'bio']; }
    protected function getPerPage(): int { return 12; }

    protected function getCreateSuccessMessage(): string { return 'تم إضافة المؤلف بنجاح'; }
    protected function getUpdateSuccessMessage(): string { return 'تم تحديث بيانات المؤلف بنجاح'; }
    protected function getDeleteSuccessMessage(): string { return 'تم حذف المؤلف بنجاح'; }
}

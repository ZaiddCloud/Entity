<?php

namespace App\Http\Controllers;

use App\Models\Category;
use Illuminate\Http\Request;

/**
 * CategoryController - Refactored to use EntityController Hooks
 */
class CategoryController extends EntityController
{
    //Configuration
    protected function getModelClass(): string { return Category::class; }
    protected function getViewPath(): string { return 'Categories'; }
    protected function getRouteName(): string { return 'categories'; }
    protected function getStoreRequestClass(): ?string { return \App\Http\Requests\StoreCategoryRequest::class; }
    protected function getUpdateRequestClass(): ?string { return \App\Http\Requests\UpdateCategoryRequest::class; }

    //Customization
    protected function getRelations(): array { return ['parent', 'books', 'videos', 'audio', 'manuscripts']; }
    protected function getSearchFields(): array { return ['name', 'description']; }
    protected function getPerPage(): int { return 20; }
    
    protected function getCreateSuccessMessage(): string { return 'تم إنشاء التصنيف بنجاح'; }
    protected function getUpdateSuccessMessage(): string { return 'تم تحديث التصنيف بنجاح'; }
    protected function getDeleteSuccessMessage(): string { return 'تم حذف التصنيف بنجاح'; }

    protected function getFormData(): array
    {
        $category = request()->route('category');
        $id = ($category instanceof Category) ? $category->id : $category;

        $categories = Category::when($id, fn($q) => $q->where('id', '!=', $id))
            ->select('id', 'name')
            ->get();

        return [
            'parentCategories' => $categories
        ];
    }
}

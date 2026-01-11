<?php

namespace App\Http\Controllers;

use App\Models\Topic;
use Illuminate\Http\Request;

/**
 * TopicController - Refactored to use EntityController Hooks
 */
class TopicController extends EntityController
{
    //Configuration
    protected function getModelClass(): string { return Topic::class; }
    protected function getViewPath(): string { return 'Topics'; }
    protected function getRouteName(): string { return 'topics'; }
    protected function getStoreRequestClass(): ?string { return \App\Http\Requests\StoreTopicRequest::class; }
    protected function getUpdateRequestClass(): ?string { return \App\Http\Requests\UpdateTopicRequest::class; }

    //Customization
    protected function getRelations(): array { return ['parent', 'children', 'books', 'videos', 'audios', 'manuscripts']; }
    protected function getSearchFields(): array { return ['name']; }
    protected function getPerPage(): int { return 12; }
    
    protected function getCreateSuccessMessage(): string { return 'تم إضافة الموضوع بنجاح'; }
    protected function getUpdateSuccessMessage(): string { return 'تم تحديث الموضوع بنجاح'; }
    protected function getDeleteSuccessMessage(): string { return 'تم حذف الموضوع بنجاح'; }

    protected function getFormData(): array
    {
        $topic = request()->route('topic');
        $id = ($topic instanceof Topic) ? $topic->id : $topic;

        $topics = Topic::when($id, fn($q) => $q->where('id', '!=', $id))
            ->select('id', 'name')
            ->get();

        return [
            'parentTopics' => $topics
        ];
    }
}

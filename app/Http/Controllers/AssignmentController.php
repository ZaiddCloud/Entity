<?php

namespace App\Http\Controllers;

use App\Models\Assignment;
use App\Models\User;
use App\Models\Book;
use App\Models\Manuscript;
use App\Models\Audio;
use App\Models\Video;
use App\Services\EntityRelationService;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class AssignmentController extends Controller
{
    public function __construct(
        protected EntityRelationService $relationService
    ) {}

    /**
     * عرض صفحة إدارة المهام
     */
    public function index(): Response
    {
        $assignments = Assignment::with(['user', 'assigner', 'entity'])
            ->latest()
            ->paginate(20);

        $users = User::select('id', 'name', 'email')->get();
        
        // Get all entities for assignment dropdown
        $entities = collect([
            'manuscripts' => Manuscript::select('id', 'title', 'slug')->get()->map(fn($e) => [
                'id' => $e->id,
                'title' => $e->title,
                'type' => 'Manuscript',
                'type_class' => Manuscript::class
            ]),
            'books' => Book::select('id', 'title', 'slug')->get()->map(fn($e) => [
                'id' => $e->id,
                'title' => $e->title,
                'type' => 'Book',
                'type_class' => Book::class
            ]),
            'audios' => Audio::select('id', 'title', 'slug')->get()->map(fn($e) => [
                'id' => $e->id,
                'title' => $e->title,
                'type' => 'Audio',
                'type_class' => Audio::class
            ]),
            'videos' => Video::select('id', 'title', 'slug')->get()->map(fn($e) => [
                'id' => $e->id,
                'title' => $e->title,
                'type' => 'Video',
                'type_class' => Video::class
            ]),
        ])->flatten(1);

        return Inertia::render('Assignments/Index', [
            'assignments' => $assignments,
            'users' => $users,
            'entities' => $entities
        ]);
    }

    /**
     * إسناد مهمة جديدة
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'user_id' => 'required|exists:users,id',
            'entity_type' => 'required|string',
            'entity_id' => 'required|uuid',
            'notes' => 'nullable|string',
            'due_at' => 'nullable|date'
        ]);

        $entityClass = $validated['entity_type'];
        $entity = $entityClass::findOrFail($validated['entity_id']);
        $user = User::findOrFail($validated['user_id']);

        $assignment = $this->relationService->assignUser(
            $entity,
            $user,
            $request->user(),
            $validated['notes'] ?? null
        );

        if (isset($validated['due_at'])) {
            $assignment->update(['due_at' => $validated['due_at']]);
        }

        return redirect()->back()->with('success', 'تم إسناد المهمة بنجاح');
    }

    /**
     * تحديث حالة المهمة
     */
    public function update(Request $request, Assignment $assignment)
    {
        $validated = $request->validate([
            'status' => 'required|in:pending,in_progress,completed,cancelled',
            'notes' => 'nullable|string',
            'due_at' => 'nullable|date'
        ]);

        $assignment->update($validated);

        return redirect()->back()->with('success', 'تم تحديث المهمة بنجاح');
    }

    /**
     * إلغاء إسناد
     */
    public function destroy(Assignment $assignment)
    {
        $entity = $assignment->entity;
        $user = $assignment->user;

        $this->relationService->revokeAssignment($entity, $user);

        return redirect()->back()->with('success', 'تم إلغاء الإسناد بنجاح');
    }
}

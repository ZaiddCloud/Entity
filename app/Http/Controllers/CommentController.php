<?php

namespace App\Http\Controllers;


use App\Models\Comment;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use Illuminate\Http\RedirectResponse;

class CommentController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): Response
    {
        $comments = Comment::with(['user', 'entity'])
            ->when($request->search, function ($query, $search) {
                $query->where('content', 'like', "%{$search}%");
            })
            ->latest()
            ->paginate($request->get('per_page', 10))
            ->withQueryString();

        return Inertia::render('Comments/Index', [
            'comments' => $comments,
            'filters' => $request->only(['search']),
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(): Response
    {
        return Inertia::render('Comments/Create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'content' => 'required|string',
            'entity_id' => 'required|uuid',
            'entity_type' => 'required|string',
        ]);

        Comment::create([
            'content' => $request->content,
            'user_id' => auth()->id(),
            'entity_id' => $request->entity_id,
            'entity_type' => $request->entity_type,
        ]);

        return redirect()->route('comments.index')
            ->with('message', 'تم إضافة التعليق بنجاح');
    }

    /**
     * Display the specified resource.
     */
    public function show(Comment $comment): Response
    {
        return Inertia::render('Comments/Show', [
            'comment' => $comment->load(['user', 'entity']),
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Comment $comment): Response
    {
        return Inertia::render('Comments/Edit', [
            'comment' => $comment,
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Comment $comment): RedirectResponse
    {
        $request->validate([
            'content' => 'required|string'
        ]);

        $comment->update($request->only('content'));

        return redirect()->route('comments.show', $comment)
            ->with('message', 'تم تحديث التعليق بنجاح');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Comment $comment): RedirectResponse
    {
        $comment->delete();

        return redirect()->route('comments.index')
            ->with('message', 'تم حذف التعليق بنجاح');
    }
}

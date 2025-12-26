<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;

use App\Models\Comment;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class CommentController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(): JsonResponse
    {
        $comments = Comment::with(['user', 'commentable'])->latest()->get();

        return response()->json($comments);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'content' => 'required|string',
            'commentable_id' => 'required|uuid',
            'commentable_type' => 'required|string', // e.g., 'book', 'video'
        ]);

        $comment = Comment::create([
            'content' => $request->content,
            'user_id' => auth()->id() ?? \App\Models\User::first()->id,
            'commentable_id' => $request->commentable_id,
            'commentable_type' => $request->commentable_type,
        ]);

        return response()->json([
            'message' => 'تم إضافة التعليق بنجاح',
            'data' => $comment
        ], 210);
    }

    /**
     * Display the specified resource.
     */
    public function show(Comment $comment): JsonResponse
    {
        return response()->json($comment->load(['user', 'commentable']));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Comment $comment): JsonResponse
    {
        $request->validate([
            'content' => 'required|string'
        ]);

        $comment->update($request->only('content'));

        return response()->json([
            'message' => 'تم تحديث التعليق بنجاح',
            'data' => $comment
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Comment $comment): JsonResponse
    {
        $comment->delete();

        return response()->json([
            'message' => 'تم حذف التعليق بنجاح'
        ]);
    }
}

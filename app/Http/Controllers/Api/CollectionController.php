<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;

use App\Models\Collection;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class CollectionController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(): JsonResponse
    {
        $collections = Collection::withCount(['books', 'videos', 'audio', 'manuscripts'])
            ->with('user')
            ->get();

        return response()->json($collections);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'is_public' => 'boolean'
        ]);

        $collection = Collection::create([
            'name' => $request->name,
            'description' => $request->description,
            'is_public' => $request->is_public ?? false,
            'user_id' => auth()->id() ?? \App\Models\User::first()->id, // Fallback for dev
        ]);

        return response()->json([
            'message' => 'تم إنشاء المجموعة بنجاح',
            'data' => $collection
        ], 210);
    }

    /**
     * Display the specified resource.
     */
    public function show(Collection $collection): JsonResponse
    {
        return response()->json($collection->load(['books', 'videos', 'audio', 'manuscripts', 'user']));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Collection $collection): JsonResponse
    {
        $request->validate([
            'name' => 'sometimes|string|max:255',
            'description' => 'nullable|string',
            'is_public' => 'boolean'
        ]);

        $collection->update($request->only(['name', 'description', 'is_public']));

        return response()->json([
            'message' => 'تم تحديث المجموعة بنجاح',
            'data' => $collection
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Collection $collection): JsonResponse
    {
        // Detach all entities implicitly via table cascade if configured, or manually
        $collection->delete();

        return response()->json([
            'message' => 'تم حذف المجموعة بنجاح'
        ]);
    }
}

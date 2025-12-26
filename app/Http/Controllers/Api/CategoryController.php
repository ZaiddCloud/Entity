<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;

use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Str;

class CategoryController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(): JsonResponse
    {
        $categories = Category::withCount(['books', 'videos', 'audio', 'manuscripts'])
            ->with(['parent', 'children'])
            ->get();

        return response()->json($categories);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'parent_id' => 'nullable|uuid|exists:categories,id',
            'description' => 'nullable|string'
        ]);

        $category = Category::create([
            'name' => $request->name,
            'slug' => Str::slug($request->name, '-', null),
            'parent_id' => $request->parent_id,
            'description' => $request->description,
        ]);

        return response()->json([
            'message' => 'تم إنشاء التصنيف بنجاح',
            'data' => $category
        ], 210);
    }

    /**
     * Display the specified resource.
     */
    public function show(Category $category): JsonResponse
    {
        return response()->json($category->load(['parent', 'children', 'entities']));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Category $category): JsonResponse
    {
        $request->validate([
            'name' => 'sometimes|string|max:255',
            'parent_id' => 'nullable|uuid|exists:categories,id',
            'description' => 'nullable|string'
        ]);

        $category->update($request->only(['name', 'parent_id', 'description']));

        return response()->json([
            'message' => 'تم تحديث التصنيف بنجاح',
            'data' => $category
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Category $category): JsonResponse
    {
        // Reassign children to parent before deletion if needed, but here we just delete
        $category->delete();

        return response()->json([
            'message' => 'تم حذف التصنيف بنجاح'
        ]);
    }
}

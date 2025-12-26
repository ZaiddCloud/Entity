<?php

namespace App\Http\Controllers;


use App\Models\Category;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Str;

class CategoryController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): Response
    {
        $filters = $request->only(['search']);

        $categories = Category::withCount(['books', 'videos', 'audio', 'manuscripts'])
            ->with(['parent'])
            ->when($request->search, function ($query, $search) {
                $query->where('name', 'like', "%{$search}%")
                      ->orWhere('description', 'like', "%{$search}%");
            })
            ->paginate(20)
            ->withQueryString();

        return Inertia::render('Categories/Index', [
            'categories' => $categories,
            'filters' => $filters,
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(): Response
    {
        $categories = Category::select('id', 'name')->get();
        return Inertia::render('Categories/Create', [
            'parentCategories' => $categories,
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'parent_id' => 'nullable|uuid|exists:categories,id',
            'description' => 'nullable|string'
        ]);

        Category::create([
            'name' => $request->name,
            'slug' => Str::slug($request->name, '-', null),
            'parent_id' => $request->parent_id,
            'description' => $request->description,
        ]);

        return redirect()->route('categories.index')
            ->with('message', 'تم إنشاء التصنيف بنجاح');
    }

    /**
     * Display the specified resource.
     */
    public function show(Category $category): Response
    {
        return Inertia::render('Categories/Show', [
            'category' => $category->load(['parent', 'children', 'books', 'videos', 'audio', 'manuscripts']),
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Category $category): Response
    {
        $categories = Category::where('id', '!=', $category->id)->select('id', 'name')->get();
        return Inertia::render('Categories/Edit', [
            'category' => $category,
            'parentCategories' => $categories,
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Category $category): RedirectResponse
    {
        $request->validate([
            'name' => 'sometimes|string|max:255',
            'parent_id' => 'nullable|uuid|exists:categories,id',
            'description' => 'nullable|string'
        ]);

        $category->update($request->only(['name', 'parent_id', 'description']));

        return redirect()->route('categories.index')
            ->with('message', 'تم تحديث التصنيف بنجاح');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Category $category): RedirectResponse
    {
        $category->delete();

        return redirect()->route('categories.index')
            ->with('message', 'تم حذف التصنيف بنجاح');
    }

    /**
     * Remove multiple resources from storage.
     */
    public function bulkDestroy(Request $request): RedirectResponse
    {
        $ids = $request->input('ids', []);
        Category::whereIn('id', $ids)->delete();

        return redirect()->route('categories.index')
            ->with('message', 'تم حذف التصنيفات المحددة بنجاح');
    }
}

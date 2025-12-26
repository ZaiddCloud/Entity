<?php

namespace App\Http\Controllers;


use App\Models\Tag;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Str;

class TagController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): Response
    {
        $filters = $request->only(['search']);

        $tags = Tag::withCount(['books', 'videos', 'audio', 'manuscripts'])
            ->when($request->search, function ($query, $search) {
                $query->where('name', 'like', "%{$search}%");
            })
            ->paginate(30)
            ->withQueryString();

        return Inertia::render('Tags/Index', [
            'tags' => $tags,
            'filters' => $filters,
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(): Response
    {
        return Inertia::render('Tags/Create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'type' => 'nullable|string'
        ]);

        Tag::create([
            'name' => $request->name,
            'slug' => Str::slug($request->name, '-', null),
            'type' => $request->type
        ]);

        return redirect()->route('tags.index')
            ->with('message', 'تم إنشاء الوسم بنجاح');
    }

    /**
     * Display the specified resource.
     */
    public function show(Tag $tag): Response
    {
        return Inertia::render('Tags/Show', [
            'tag' => $tag->load(['books', 'videos', 'audio', 'manuscripts']),
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Tag $tag): Response
    {
        return Inertia::render('Tags/Edit', [
            'tag' => $tag,
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Tag $tag): RedirectResponse
    {
        $request->validate([
            'name' => 'sometimes|string|max:255',
            'type' => 'nullable|string'
        ]);

        $tag->update($request->only(['name', 'type']));

        return redirect()->route('tags.index')
            ->with('message', 'تم تحديث الوسم بنجاح');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Tag $tag): RedirectResponse
    {
        $tag->delete();

        return redirect()->route('tags.index')
            ->with('message', 'تم حذف الوسم بنجاح');
    }

    /**
     * Remove multiple resources from storage.
     */
    public function bulkDestroy(Request $request): RedirectResponse
    {
        $ids = $request->input('ids', []);
        Tag::whereIn('id', $ids)->delete();

        return redirect()->route('tags.index')
            ->with('message', 'تم حذف الوسوم المحددة بنجاح');
    }
}

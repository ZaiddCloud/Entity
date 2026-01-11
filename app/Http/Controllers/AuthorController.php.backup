<?php

namespace App\Http\Controllers;

use App\Models\Author;
use Illuminate\Http\Request;
use Inertia\Inertia;

class AuthorController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = Author::query();

        if ($request->has('search')) {
            $query->where('name', 'like', '%' . $request->search . '%');
        }

        $authors = $query->withCount(['books', 'videos', 'audios', 'manuscripts'])
            ->latest()
            ->paginate(12)
            ->withQueryString();

        return Inertia::render('Authors/Index', [
            'authors' => $authors,
            'filters' => $request->only(['search']),
        ]);
    }

    /**
     * Bulk destroy resource.
     */
    public function bulkDestroy(Request $request)
    {
        $request->validate([
            'ids' => 'required|array',
            'ids.*' => 'required|exists:authors,id',
        ]);

        Author::whereIn('id', $request->ids)->delete();

        return back()->with('success', 'تم حذف المؤلفين بنجاح.');
    }

    /**
     * Show the form for creating a new resource.
     */
    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return Inertia::render('Authors/Create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'bio' => 'nullable|string',
            'birth_year' => 'nullable|integer|min:-2000|max:' . (date('Y') + 1),
            'death_year' => 'nullable|integer|min:-2000|max:' . (date('Y') + 1),
        ]);

        Author::create($validated);

        return redirect()->route('authors.index')
            ->with('success', 'تم إضافة المؤلف بنجاح');
    }

    /**
     * Display the specified resource.
     */
    public function show(Author $author)
    {
        $author->loadCount(['books', 'videos', 'audios', 'manuscripts']);
        
        return Inertia::render('Authors/Show', [
            'author' => $author,
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Author $author)
    {
        return Inertia::render('Authors/Edit', [
            'author' => $author,
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Author $author)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'bio' => 'nullable|string',
            'birth_year' => 'nullable|integer|min:-2000|max:' . (date('Y') + 1),
            'death_year' => 'nullable|integer|min:-2000|max:' . (date('Y') + 1),
        ]);

        $author->update($validated);

        return redirect()->route('authors.index')
            ->with('success', 'تم تحديث بيانات المؤلف بنجاح');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Author $author)
    {
        $author->delete();

        return redirect()->route('authors.index')
            ->with('success', 'تم حذف المؤلف بنجاح');
    }
}

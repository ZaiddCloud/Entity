<?php

namespace App\Http\Controllers;


use App\Models\Collection;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use Illuminate\Http\RedirectResponse;

class CollectionController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): Response
    {
        $filters = $request->only(['search']);
        
        $collections = Collection::withCount(['books', 'videos', 'audio', 'manuscripts'])
            ->with('user')
            ->when($request->search, function ($query, $search) {
                $query->where('name', 'like', "%{$search}%")
                      ->orWhere('description', 'like', "%{$search}%");
            })
            ->latest()
            ->paginate(15)
            ->withQueryString();

        return Inertia::render('Collections/Index', [
            'collections' => $collections,
            'filters' => $filters,
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(): Response
    {
        return Inertia::render('Collections/Create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'is_public' => 'boolean'
        ]);

        Collection::create([
            'name' => $request->name,
            'description' => $request->description,
            'is_public' => $request->is_public ?? false,
            'user_id' => auth()->id() ?? \App\Models\User::first()->id, // Fallback for dev
        ]);

        return redirect()->route('collections.index')
            ->with('message', 'تم إنشاء المجموعة بنجاح');
    }

    /**
     * Display the specified resource.
     */
    public function show(Collection $collection): Response
    {
        return Inertia::render('Collections/Show', [
            'collection' => $collection->load(['books', 'videos', 'audio', 'manuscripts', 'user']),
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Collection $collection): Response
    {
        return Inertia::render('Collections/Edit', [
            'collection' => $collection,
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Collection $collection): RedirectResponse
    {
        $request->validate([
            'name' => 'sometimes|string|max:255',
            'description' => 'nullable|string',
            'is_public' => 'boolean'
        ]);

        $collection->update($request->only(['name', 'description', 'is_public']));

        return redirect()->route('collections.index')
            ->with('message', 'تم تحديث المجموعة بنجاح');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Collection $collection): RedirectResponse
    {
        $collection->delete();

        return redirect()->route('collections.index')
            ->with('message', 'تم حذف المجموعة بنجاح');
    }
}

<?php

namespace App\Http\Controllers;

use App\Models\Shelf;
use Illuminate\Http\Request;
use Inertia\Inertia;

class ShelfController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $filters = $request->only(['search']);

        $shelves = Shelf::withCount(['versions'])
            ->when($request->input('search'), function ($query, $search) {
                $query->where('location_code', 'like', "%{$search}%");
            })
            ->latest()
            ->paginate(12)
            ->withQueryString();

        return Inertia::render('Shelves/Index', [
            'shelves' => $shelves,
            'filters' => $filters,
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return Inertia::render('Shelves/Create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'location_code' => 'required|string|max:255|unique:shelves,location_code',
            'capacity' => 'required|integer|min:0',
        ]);

        Shelf::create($request->only(['location_code', 'capacity']));

        return redirect()->route('shelves.index')
            ->with('message', 'تم إنشاء الرف بنجاح');
    }

    /**
     * Display the specified resource.
     */
    public function show(Shelf $shelf)
    {
        return Inertia::render('Shelves/Show', [
            'shelf' => $shelf->load(['versions.versionable']),
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Shelf $shelf)
    {
        return Inertia::render('Shelves/Edit', [
            'shelf' => $shelf,
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Shelf $shelf)
    {
        $request->validate([
            'location_code' => 'sometimes|string|max:255|unique:shelves,location_code,' . $shelf->id,
            'capacity' => 'sometimes|integer|min:0',
        ]);

        $shelf->update($request->only(['location_code', 'capacity']));

        return redirect()->route('shelves.index')
            ->with('message', 'تم تحديث الرف بنجاح');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Shelf $shelf)
    {
        $shelf->delete();

        return redirect()->route('shelves.index')
            ->with('message', 'تم حذف الرف بنجاح');
    }

    /**
     * Bulk destroy resource.
     */
    public function bulkDestroy(Request $request)
    {
        $request->validate([
            'ids' => 'required|array',
            'ids.*' => 'required|exists:shelves,id',
        ]);

        Shelf::whereIn('id', $request->ids)->delete();

        return back()->with('message', 'تم حذف الرفوف المحددة بنجاح.');
    }
}

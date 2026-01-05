<?php

namespace App\Http\Controllers;

use App\Models\Publisher;
use Illuminate\Http\Request;
use Inertia\Inertia;

class PublisherController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = Publisher::query();

        if ($request->has('search')) {
            $query->where('name', 'like', '%' . $request->search . '%');
        }

        $publishers = $query->withCount(['books', 'videos', 'audios', 'manuscripts'])
            ->latest()
            ->paginate(12)
            ->withQueryString();

        return Inertia::render('Publishers/Index', [
            'publishers' => $publishers,
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
            'ids.*' => 'required|exists:publishers,id',
        ]);

        Publisher::whereIn('id', $request->ids)->delete();

        return back()->with('success', 'تم حذف دور النشر بنجاح.');
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return Inertia::render('Publishers/Create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'country_code' => 'nullable|string|max:3',
            'logo' => 'nullable|image|max:2048',
        ]);

        $data['slug'] = \Illuminate\Support\Str::slug($data['name']);
        
        if ($request->hasFile('logo')) {
            $data['logo_path'] = $request->file('logo')->store('logos', 'public');
        }

        Publisher::create($data);

        return redirect()->route('publishers.index')
            ->with('message', 'تم إنشاء دار النشر بنجاح');
    }

    /**
     * Display the specified resource.
     */
    public function show(Publisher $publisher)
    {
        return Inertia::render('Publishers/Show', [
            'publisher' => $publisher->loadCount(['books', 'videos', 'audios', 'manuscripts']),
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Publisher $publisher)
    {
        return Inertia::render('Publishers/Edit', [
            'publisher' => $publisher,
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Publisher $publisher)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'country_code' => 'nullable|string|max:3',
            'logo' => 'nullable|image|max:2048',
        ]);

        $data['slug'] = \Illuminate\Support\Str::slug($data['name']);
        
        if ($request->hasFile('logo')) {
            $data['logo_path'] = $request->file('logo')->store('logos', 'public');
        }

        $publisher->update($data);

        return redirect()->route('publishers.index')
            ->with('message', 'تم تحديث دار النشر بنجاح');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Publisher $publisher)
    {
        $publisher->delete();

        return redirect()->route('publishers.index')
            ->with('message', 'تم حذف دار النشر بنجاح');
    }
}

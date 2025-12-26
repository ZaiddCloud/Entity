<?php

namespace App\Http\Controllers;


use App\Models\Series;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use Illuminate\Http\RedirectResponse;

class SeriesController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): Response
    {
        $filters = $request->only(['search']);

        $series = Series::withCount(['books', 'videos', 'audio', 'manuscripts'])
            ->when($request->search, function ($query, $search) {
                $query->where('title', 'like', "%{$search}%")
                      ->orWhere('description', 'like', "%{$search}%");
            })
            ->latest()
            ->paginate(15)
            ->withQueryString();

        return Inertia::render('Series/Index', [
            'series' => $series,
            'filters' => $filters,
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(): Response
    {
        return Inertia::render('Series/Create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'order_column' => 'integer'
        ]);

        Series::create($request->only(['title', 'description', 'order_column']));

        return redirect()->route('series.index')
            ->with('message', 'تم إنشاء السلسلة بنجاح');
    }

    /**
     * Display the specified resource.
     */
    public function show(Series $series): Response
    {
        return Inertia::render('Series/Show', [
            'series' => $series->load(['books', 'videos', 'audio', 'manuscripts']),
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Series $series): Response
    {
        return Inertia::render('Series/Edit', [
            'series' => $series,
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Series $series): RedirectResponse
    {
        $request->validate([
            'title' => 'sometimes|string|max:255',
            'description' => 'nullable|string',
            'order_column' => 'integer'
        ]);

        $series->update($request->only(['title', 'description', 'order_column']));

        return redirect()->route('series.index')
            ->with('message', 'تم تحديث السلسلة بنجاح');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Series $series): RedirectResponse
    {
        $series->delete();

        return redirect()->route('series.index')
            ->with('message', 'تم حذف السلسلة بنجاح');
    }
}

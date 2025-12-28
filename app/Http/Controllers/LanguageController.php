<?php

namespace App\Http\Controllers;

use App\Models\Language;
use Illuminate\Http\Request;
use Inertia\Inertia;

class LanguageController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = Language::query();

        if ($request->has('search')) {
            $query->where('name', 'like', '%' . $request->search . '%')
                ->orWhere('code', 'like', '%' . $request->search . '%');
        }

        $languages = $query->withCount(['books', 'videos', 'audios', 'manuscripts'])
            ->latest()
            ->paginate(12)
            ->withQueryString();

        return Inertia::render('Languages/Index', [
            'languages' => $languages,
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
            'ids.*' => 'required|exists:languages,id',
        ]);

        Language::whereIn('id', $request->ids)->delete();

        return back()->with('success', 'تم حذف اللغات بنجاح.');
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}

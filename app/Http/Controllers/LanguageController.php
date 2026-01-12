<?php

namespace App\Http\Controllers;

use App\Models\Language;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use Illuminate\Http\RedirectResponse;

class LanguageController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): Response
    {
        $filters = $request->only(['search']);

        $languages = Language::withCount(['books', 'videos', 'audios', 'manuscripts'])
            ->when($request->input('search'), function ($query, $search) {
                $query->where('name', 'like', "%{$search}%")
                    ->orWhere('code', 'like', "%{$search}%");
            })
            ->latest()
            ->paginate(12)
            ->withQueryString();

        return Inertia::render('Languages/Index', [
            'languages' => $languages,
            'filters' => $filters,
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(): Response
    {
        return Inertia::render('Languages/Create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:languages,name',
            'code' => 'required|string|max:10|unique:languages,code',
        ]);

        Language::create($request->only(['name', 'code']));

        return redirect()->route('languages.index')
            ->with('message', 'تم إضافة اللغة بنجاح');
    }

    /**
     * Display the specified resource.
     */
    public function show(Language $language): Response
    {
        return Inertia::render('Languages/Show', [
            'language' => $language->loadCount(['books', 'videos', 'audios', 'manuscripts']),
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Language $language): Response
    {
        return Inertia::render('Languages/Edit', [
            'language' => $language,
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Language $language): RedirectResponse
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:languages,name,' . $language->id,
            'code' => 'required|string|max:10|unique:languages,code,' . $language->id,
        ]);

        $language->update($request->only(['name', 'code']));

        return redirect()->route('languages.index')
            ->with('message', 'تم تحديث اللغة بنجاح');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Language $language): RedirectResponse
    {
        $language->delete();

        return redirect()->route('languages.index')
            ->with('message', 'تم حذف اللغة بنجاح');
    }

    /**
     * Bulk destroy resource.
     */
    public function bulkDestroy(Request $request): RedirectResponse
    {
        $request->validate([
            'ids' => 'required|array',
            'ids.*' => 'required|exists:languages,id',
        ]);

        Language::whereIn('id', $request->ids)->delete();

        return back()->with('message', 'تم حذف اللغات المحددة بنجاح.');
    }
}

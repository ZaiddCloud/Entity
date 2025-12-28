<?php

namespace App\Http\Controllers;

use App\Models\Topic;
use Illuminate\Http\Request;
use Inertia\Inertia;

class TopicController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = Topic::query();

        if ($request->has('search')) {
            $query->where('title', 'like', '%' . $request->search . '%');
        }

        $topics = $query->withCount(['books', 'videos', 'audios', 'manuscripts'])
            ->latest()
            ->paginate(12)
            ->withQueryString();

        return Inertia::render('Topics/Index', [
            'topics' => $topics,
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
            'ids.*' => 'required|exists:topics,id',
        ]);

        Topic::whereIn('id', $request->ids)->delete();

        return back()->with('success', 'تم حذف المواضيع بنجاح.');
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

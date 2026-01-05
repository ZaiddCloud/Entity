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
        $filters = $request->only(['search']);

        $topics = Topic::withCount(['books', 'videos', 'audios', 'manuscripts'])
            ->when($request->search, function ($query, $search) {
                $query->where('name', 'like', "%{$search}%");
            })
            ->latest()
            ->paginate(12)
            ->withQueryString();

        return Inertia::render('Topics/Index', [
            'topics' => $topics,
            'filters' => $filters,
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return Inertia::render('Topics/Create', [
            'parentTopics' => Topic::all(['id', 'name']),
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'parent_id' => 'nullable|exists:topics,id',
        ]);

        $data['slug'] = \Illuminate\Support\Str::slug($data['name']);

        Topic::create($data);

        return redirect()->route('topics.index')
            ->with('message', 'تم إضافة الموضوع بنجاح');
    }

    /**
     * Display the specified resource.
     */
    public function show(Topic $topic)
    {
        return Inertia::render('Topics/Show', [
            'topic' => $topic->loadCount(['books', 'videos', 'audios', 'manuscripts'])
                            ->load(['parent', 'children']),
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Topic $topic)
    {
        return Inertia::render('Topics/Edit', [
            'topic' => $topic,
            'parentTopics' => Topic::where('id', '!=', $topic->id)->get(['id', 'name']),
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Topic $topic)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'parent_id' => 'nullable|exists:topics,id|different:id',
        ]);

        $data['slug'] = \Illuminate\Support\Str::slug($data['name']);

        $topic->update($data);

        return redirect()->route('topics.index')
            ->with('message', 'تم تحديث الموضوع بنجاح');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Topic $topic)
    {
        $topic->delete();

        return redirect()->route('topics.index')
            ->with('message', 'تم حذف الموضوع بنجاح');
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

        return back()->with('message', 'تم حذف المواضيع المحددة بنجاح.');
    }
}

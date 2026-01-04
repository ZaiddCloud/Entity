<?php

namespace App\Http\Controllers;


use App\Models\Video;
use App\Services\EntityManagerService;
use App\Services\EntityQueryService;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use Illuminate\Http\RedirectResponse;

use Illuminate\Support\Facades\Gate;
use App\Http\Requests\StoreEntityRequest;
use App\Http\Requests\UpdateEntityRequest;

class VideoController extends Controller
{
    use Traits\HasEditor;

    protected $manager;
    protected $query;
    protected $mediaManager;
    // ... existing properties/methods ...

    /**
     * عارض محرر الفيديو (الموحد)
     */
    public function show(Video $video): Response
    {
        Gate::authorize('view', $video);
        
        $firstContent = \App\Models\EntityContent::where('entity_type', 'video')
            ->where('entity_id', $video->id)
            ->orderBy('order')
            ->first();

        return Inertia::render('Videos/Show', [
            'video' => $video->load(['tags', 'categories', 'comments.user', 'versions.publisher', 'authors']),
            'first_content_slug' => $firstContent?->slug,
        ]);
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): Response
    {
        Gate::authorize('viewAny', Video::class);
        $filters = $request->only(['search', 'category', 'tag']);

        $videos = Video::with(['tags', 'categories', 'authors', 'versions.publisher'])
            ->when($request->search, function ($query, $search) {
                $query->where('title', 'like', "%{$search}%")
                    ->orWhereHas('authors', function ($q) use ($search) {
                        $q->where('name', 'like', "%{$search}%");
                    });
            })
            ->when($request->category, function ($query, $category) {
                $query->whereHas('categories', function ($q) use ($category) {
                    $q->where('categories.id', $category);
                });
            })
            ->when($request->tag, function ($query, $tag) {
                $query->whereHas('tags', function ($q) use ($tag) {
                    $q->where('tags.id', $tag);
                });
            })
            ->latest()
            ->paginate($request->get('per_page', 10))
            ->withQueryString();

        return Inertia::render('Videos/Index', [
            'videos' => $videos,
            'filters' => $filters,
            'categories' => \App\Models\Category::all(['id', 'name']),
            'tags' => \App\Models\Tag::all(['id', 'name']),
        ]);
    }

    public function create(): Response
    {
        Gate::authorize('create', Video::class);
        return Inertia::render('Videos/Create', [
            'authors' => \App\Models\Author::orderBy('name')->get(['id', 'name']),
            'publishers' => \App\Models\Publisher::orderBy('name')->get(['id', 'name']),
            'categories' => \App\Models\Category::orderBy('name')->get(['id', 'name']),
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreEntityRequest $request): RedirectResponse
    {
        Gate::authorize('create', Video::class);
        $data = $request->validated();

        if (!isset($data['type'])) {
            $data['type'] = 'video';
        }

        if ($request->hasFile('cover')) {
            $data['cover_path'] = $request->file('cover')->store('covers', 'public');
        }

        if ($request->hasFile('file')) {
            $data['file_path'] = $request->file('file')->store('videos', 'public');
        }

        $data['type'] = 'video';
        $this->mediaManager->createMedia($data);

        return redirect()->route('videos.index')
            ->with('message', 'تم إنشاء الفيديو بنجاح');
    }


    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Video $video): Response
    {
        Gate::authorize('update', $video);
        return Inertia::render('Videos/Edit', [
            'video' => $video->load(['tags', 'categories', 'authors', 'versions']),
            'authors' => \App\Models\Author::orderBy('name')->get(['id', 'name']),
            'publishers' => \App\Models\Publisher::orderBy('name')->get(['id', 'name']),
            'categories' => \App\Models\Category::orderBy('name')->get(['id', 'name']),
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateEntityRequest $request, Video $video): RedirectResponse
    {
        Gate::authorize('update', $video);
        $data = $request->validated();

        if ($request->hasFile('cover')) {
            $data['cover_path'] = $request->file('cover')->store('covers', 'public');
        }

        if ($request->hasFile('file')) {
            $data['file_path'] = $request->file('file')->store('videos', 'public');
        }

        $this->mediaManager->updateMedia($video, $data);

        return redirect()->route('videos.show', $video)
            ->with('message', 'تم تحديث الفيديو بنجاح');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Video $video): RedirectResponse
    {
        Gate::authorize('delete', $video);
        $this->manager->delete($video);

        return redirect()->route('videos.index')
            ->with('message', 'تم حذف الفيديو بنجاح');
    }
}

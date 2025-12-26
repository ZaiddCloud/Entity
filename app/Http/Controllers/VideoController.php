<?php

namespace App\Http\Controllers;


use App\Models\Video;
use App\Services\EntityManagerService;
use App\Services\EntityQueryService;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use Illuminate\Http\RedirectResponse;

class VideoController extends Controller
{
    protected $manager;
    protected $query;

    public function __construct(EntityManagerService $manager, EntityQueryService $query)
    {
        $this->manager = $manager;
        $this->query = $query;
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): Response
    {
        $filters = $request->only(['search', 'category', 'tag']);
        
        $videos = Video::with(['tags', 'categories'])
            ->when($request->search, function ($query, $search) {
                $query->where('title', 'like', "%{$search}%");
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

    /**
     * Show the form for creating a new resource.
     */
    public function create(): Response
    {
        return Inertia::render('Videos/Create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request): RedirectResponse
    {
        $data = $request->all();
        $data['type'] = 'video';

        $video = $this->manager->create($data);

        return redirect()->route('videos.index')
            ->with('message', 'تم إنشاء الفيديو بنجاح');
    }

    /**
     * Display the specified resource.
     */
    public function show(Video $video): Response
    {
        return Inertia::render('Videos/Show', [
            'video' => $video->load(['tags', 'categories', 'comments.user']),
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Video $video): Response
    {
        return Inertia::render('Videos/Edit', [
            'video' => $video->load(['tags', 'categories']),
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Video $video): RedirectResponse
    {
        $this->manager->update($video, $request->all());

        return redirect()->route('videos.show', $video->id)
            ->with('message', 'تم تحديث الفيديو بنجاح');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Video $video): RedirectResponse
    {
        $this->manager->delete($video);

        return redirect()->route('videos.index')
            ->with('message', 'تم حذف الفيديو بنجاح');
    }
}

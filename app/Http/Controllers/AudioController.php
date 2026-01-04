<?php

namespace App\Http\Controllers;


use App\Models\Audio;
use App\Services\EntityManagerService;
use App\Services\EntityQueryService;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use Illuminate\Http\RedirectResponse;

use Illuminate\Support\Facades\Gate;
use App\Http\Requests\StoreEntityRequest;
use App\Http\Requests\UpdateEntityRequest;

class AudioController extends Controller
{
    use Traits\HasEditor;

    protected $manager;
    protected $query;
    protected $mediaManager;
    // ... existing properties/methods ...

    /**
     * عارض محرر الملفات الصوتية (الموحد)
     */
    public function show(Audio $audio): Response
    {
        Gate::authorize('view', $audio);
        
        $firstContent = \App\Models\EntityContent::where('entity_type', 'audio')
            ->where('entity_id', $audio->id)
            ->orderBy('order')
            ->first();

        return Inertia::render('Audio/Show', [
            'audio' => $audio->load(['tags', 'categories', 'comments.user', 'versions.publisher', 'authors']),
            'first_content_slug' => $firstContent?->slug,
        ]);
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): Response
    {
        Gate::authorize('viewAny', Audio::class);
        $filters = $request->only(['search', 'category', 'tag']);

        $audios = Audio::with(['tags', 'categories', 'authors', 'versions.publisher'])
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

        return Inertia::render('Audio/Index', [
            'audios' => $audios,
            'filters' => $filters,
            'categories' => \App\Models\Category::all(['id', 'name']),
            'tags' => \App\Models\Tag::all(['id', 'name']),
        ]);
    }

    public function create(): Response
    {
        Gate::authorize('create', Audio::class);
        return Inertia::render('Audio/Create', [
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
        Gate::authorize('create', Audio::class);
        $data = $request->validated();

        if (!isset($data['type'])) {
            $data['type'] = 'audio';
        }

        if ($request->hasFile('cover')) {
            $data['cover_path'] = $request->file('cover')->store('covers', 'public');
        }

        if ($request->hasFile('file')) {
            $data['file_path'] = $request->file('file')->store('audios', 'public');
        }

        $data['type'] = 'audio';
        $this->mediaManager->createMedia($data);

        return redirect()->route('audios.index')
            ->with('message', 'تم إنشاء الملف الصوتي بنجاح');
    }


    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Audio $audio): Response
    {
        Gate::authorize('update', $audio);
        return Inertia::render('Audio/Edit', [
            'audio' => $audio->load(['tags', 'categories', 'authors', 'versions']),
            'authors' => \App\Models\Author::orderBy('name')->get(['id', 'name']),
            'publishers' => \App\Models\Publisher::orderBy('name')->get(['id', 'name']),
            'categories' => \App\Models\Category::orderBy('name')->get(['id', 'name']),
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateEntityRequest $request, Audio $audio): RedirectResponse
    {
        Gate::authorize('update', $audio);
        $data = $request->validated();

        if ($request->hasFile('cover')) {
            $data['cover_path'] = $request->file('cover')->store('covers', 'public');
        }

        if ($request->hasFile('file')) {
            $data['file_path'] = $request->file('file')->store('audios', 'public');
        }

        $this->mediaManager->updateMedia($audio, $data);

        return redirect()->route('audios.show', $audio)
            ->with('message', 'تم تحديث الملف الصوتي بنجاح');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Audio $audio): RedirectResponse
    {
        Gate::authorize('delete', $audio);
        $this->manager->delete($audio);

        return redirect()->route('audios.index')
            ->with('message', 'تم حذف الملف الصوتي بنجاح');
    }
}

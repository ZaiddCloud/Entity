<?php

namespace App\Http\Controllers;


use App\Models\Manuscript;
use App\Services\EntityManagerService;
use App\Services\EntityQueryService;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use Illuminate\Http\RedirectResponse;

use Illuminate\Support\Facades\Gate;
use App\Http\Requests\StoreEntityRequest;
use App\Http\Requests\UpdateEntityRequest;

class ManuscriptController extends Controller
{
    use Traits\HasEditor;

    protected $manager;
    protected $query;
    protected $mediaManager;
    // ...

    /**
     * عارض محرر المخطوطات (الموحد)
     */
    public function show(Manuscript $manuscript): Response
    {
        Gate::authorize('view', $manuscript);
        
        $firstContent = \App\Models\EntityContent::where('entity_type', 'manuscript')
            ->where('entity_id', $manuscript->id)
            ->orderBy('order')
            ->first();

        return Inertia::render('Manuscripts/Show', [
            'manuscript' => $manuscript->load(['tags', 'categories', 'comments.user', 'versions.publisher']),
            'first_content_slug' => $firstContent?->slug,
        ]);
    }
    public function index(Request $request): Response
    {
        Gate::authorize('viewAny', Manuscript::class);
        $filters = $request->only(['search', 'category', 'tag']);

        $manuscripts = Manuscript::with(['tags', 'categories', 'authors', 'versions.publisher'])
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

        return Inertia::render('Manuscripts/Index', [
            'manuscripts' => $manuscripts,
            'filters' => $filters,
            'categories' => \App\Models\Category::all(['id', 'name']),
            'tags' => \App\Models\Tag::all(['id', 'name']),
        ]);
    }

    public function create(): Response
    {
        Gate::authorize('create', Manuscript::class);
        return Inertia::render('Manuscripts/Create', [
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
        Gate::authorize('create', Manuscript::class);
        $data = $request->validated();

        if (!isset($data['type'])) {
            $data['type'] = 'manuscript';
        }

        if ($request->hasFile('cover')) {
            $data['cover_path'] = $request->file('cover')->store('covers', 'public');
        }

        if ($request->hasFile('file')) {
            $data['file_path'] = $request->file('file')->store('manuscripts', 'public');
        }

        $data['type'] = 'manuscript';
        $this->mediaManager->createMedia($data);

        return redirect()->route('manuscripts.index')
            ->with('message', 'تم إنشاء المخطوطة بنجاح');
    }


    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Manuscript $manuscript): Response
    {
        Gate::authorize('update', $manuscript);
        return Inertia::render('Manuscripts/Edit', [
            'manuscript' => $manuscript->load(['tags', 'categories', 'authors', 'versions']),
            'authors' => \App\Models\Author::orderBy('name')->get(['id', 'name']),
            'publishers' => \App\Models\Publisher::orderBy('name')->get(['id', 'name']),
            'categories' => \App\Models\Category::orderBy('name')->get(['id', 'name']),
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateEntityRequest $request, Manuscript $manuscript): RedirectResponse
    {
        Gate::authorize('update', $manuscript);
        $data = $request->validated();

        if ($request->hasFile('cover')) {
            $data['cover_path'] = $request->file('cover')->store('covers', 'public');
        }

        if ($request->hasFile('file')) {
            $data['file_path'] = $request->file('file')->store('manuscripts', 'public');
        }

        $this->mediaManager->updateMedia($manuscript, $data);

        return redirect()->route('manuscripts.show', $manuscript)
            ->with('message', 'تم تحديث المخطوطة بنجاح');
    }



    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Manuscript $manuscript): RedirectResponse
    {
        Gate::authorize('delete', $manuscript);
        $this->manager->delete($manuscript);

        return redirect()->route('manuscripts.index')
            ->with('message', 'تم حذف المخطوطة بنجاح');
    }
}

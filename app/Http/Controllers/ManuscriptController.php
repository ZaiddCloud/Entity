<?php

namespace App\Http\Controllers;


use App\Models\Manuscript;
use App\Services\EntityManagerService;
use App\Services\EntityQueryService;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use Illuminate\Http\RedirectResponse;

class ManuscriptController extends Controller
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
        
        $manuscripts = Manuscript::with(['tags', 'categories'])
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

        return Inertia::render('Manuscripts/Index', [
            'manuscripts' => $manuscripts,
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
        return Inertia::render('Manuscripts/Create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request): RedirectResponse
    {
        $data = $request->all();
        $data['type'] = 'manuscript';

        $manuscript = $this->manager->create($data);

        return redirect()->route('manuscripts.index')
            ->with('message', 'تم إنشاء المخطوطة بنجاح');
    }

    /**
     * Display the specified resource.
     */
    public function show(Manuscript $manuscript): Response
    {
        return Inertia::render('Manuscripts/Show', [
            'manuscript' => $manuscript->load(['tags', 'categories', 'comments.user']),
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Manuscript $manuscript): Response
    {
        return Inertia::render('Manuscripts/Edit', [
            'manuscript' => $manuscript->load(['tags', 'categories']),
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Manuscript $manuscript): RedirectResponse
    {
        $this->manager->update($manuscript, $request->all());

        return redirect()->route('manuscripts.show', $manuscript->id)
            ->with('message', 'تم تحديث المخطوطة بنجاح');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Manuscript $manuscript): RedirectResponse
    {
        $this->manager->delete($manuscript);

        return redirect()->route('manuscripts.index')
            ->with('message', 'تم حذف المخطوطة بنجاح');
    }
}

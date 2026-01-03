<?php

namespace App\Http\Controllers;


use App\Models\Book;
use App\Services\EntityManagerService;
use App\Services\EntityQueryService;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use Illuminate\Http\RedirectResponse;

use Illuminate\Support\Facades\Gate;
use App\Http\Requests\StoreEntityRequest;
use App\Http\Requests\UpdateEntityRequest;

class BookController extends Controller
{
    protected $manager;
    protected $query;
    protected $mediaManager;

    public function __construct(EntityManagerService $manager, EntityQueryService $query, \App\Services\MediaManagerService $mediaManager)
    {
        $this->manager = $manager;
        $this->query = $query;
        $this->mediaManager = $mediaManager;
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): Response
    {
        Gate::authorize('viewAny', Book::class);

        $filters = $request->only(['search', 'category', 'tag']);

        $books = Book::with(['tags', 'categories', 'authors', 'versions.publisher']) // Eager load new relations
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

        return Inertia::render('Books/Index', [
            'books' => $books,
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
        Gate::authorize('create', Book::class);
        return Inertia::render('Books/Create', [
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
        Gate::authorize('create', Book::class);

        // We need to merge file paths manually since the service expects them in the data array
        // but StoreEntityRequest handles validation. We rely on the request being valid here.
        $data = $request->validated();

        if ($request->hasFile('file')) {
            $data['file_path'] = $request->file('file')->store('books', 'public');
        }

        // Handle cover if present (optional for book, might belong to version or book)
        // For now, let's keep it simple and maybe attach to version logic later or book
        if ($request->hasFile('cover')) {
            $data['cover_path'] = $request->file('cover')->store('covers', 'public');
        }

        $data['type'] = 'book';
        $this->mediaManager->createMedia($data);

        return redirect()->route('books.index')
            ->with('message', 'تم إنشاء الكتاب بنجاح');
    }

    /**
     * Display the specified resource.
     */
    public function show(Book $book): Response
    {
        Gate::authorize('view', $book);
        return Inertia::render('Books/Show', [
            'book' => $book->load(['tags', 'categories', 'comments.user']),
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Book $book): Response
    {
        Gate::authorize('update', $book);

        // Load relationships to populate the form
        $book->load(['authors', 'versions']);

        return Inertia::render('Books/Edit', [
            'book' => $book,
            'authors' => \App\Models\Author::orderBy('name')->get(['id', 'name']),
            'publishers' => \App\Models\Publisher::orderBy('name')->get(['id', 'name']),
            'categories' => \App\Models\Category::orderBy('name')->get(['id', 'name']),
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateEntityRequest $request, Book $book): RedirectResponse
    {
        Gate::authorize('update', $book);
        $data = $request->validated();

        if ($request->hasFile('cover')) {
            $data['cover_path'] = $request->file('cover')->store('covers', 'public');
        }

        if ($request->hasFile('file')) {
            $data['file_path'] = $request->file('file')->store('books', 'public');
        }

        $this->mediaManager->updateMedia($book, $data);

        $book->refresh(); // Refresh to get updated slug if changed

        return redirect()->route('books.show', $book)
            ->with('message', 'تم تحديث الكتاب بنجاح');
    }

    /**
     * عارض محرر الكتاب
     */
    public function editor(Book $book, $childSlug): Response
    {
        Gate::authorize('update', $book);

        $child = \App\Models\BookChild::where('book_id', $book->id)
            ->where('slug', $childSlug)
            ->firstOrFail();

        return Inertia::render('Books/Editor/EditorPage', [
            'book' => $book->only(['id', 'title', 'slug', 'author']),
            'child' => [
                'id' => $child->_id,
                'title' => $child->title,
                'content' => $child->content_blocks ?? [],
            ],
            'editor_mode' => 'book',
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Book $book): RedirectResponse
    {
        Gate::authorize('delete', $book);
        $this->manager->delete($book);

        return redirect()->route('books.index')
            ->with('message', 'تم حذف الكتاب بنجاح');
    }
}

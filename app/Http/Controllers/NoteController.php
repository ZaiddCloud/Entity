<?php

namespace App\Http\Controllers;


use App\Models\Note;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use Illuminate\Http\RedirectResponse;

class NoteController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): Response
    {
        $notes = Note::with(['user', 'entity'])
            ->when($request->search, function ($query, $search) {
                $query->where('content', 'like', "%{$search}%");
            })
            ->latest()
            ->paginate($request->get('per_page', 10))
            ->withQueryString();

        return Inertia::render('Notes/Index', [
            'notes' => $notes,
            'filters' => $request->only(['search']),
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(): Response
    {
        return Inertia::render('Notes/Create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'content' => 'required|string',
            'entity_id' => 'required|uuid',
            'entity_type' => 'required|string',
        ]);

        Note::create([
            'content' => $request->content,
            'user_id' => auth()->id(),
            'entity_id' => $request->entity_id,
            'entity_type' => $request->entity_type,
        ]);

        return redirect()->route('notes.index')
            ->with('message', 'تم إضافة الملاحظة بنجاح');
    }

    /**
     * Display the specified resource.
     */
    public function show(Note $note): Response
    {
        return Inertia::render('Notes/Show', [
            'note' => $note->load(['user', 'entity']),
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Note $note): Response
    {
        return Inertia::render('Notes/Edit', [
            'note' => $note,
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Note $note): RedirectResponse
    {
        $request->validate([
            'content' => 'required|string'
        ]);

        $note->update($request->only('content'));

        return redirect()->route('notes.show', $note)
            ->with('message', 'تم تحديث الملاحظة بنجاح');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Note $note): RedirectResponse
    {
        $note->delete();

        return redirect()->route('notes.index')
            ->with('message', 'تم حذف الملاحظة بنجاح');
    }
}

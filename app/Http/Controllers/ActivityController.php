<?php

namespace App\Http\Controllers;


use App\Models\Activity;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class ActivityController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): Response
    {
        $filters = $request->only(['search', 'type']);

        $activities = Activity::with('user')
            ->when($request->search, function ($query, $search) {
                $query->where('description', 'like', "%{$search}%")
                      ->orWhereHas('user', function ($q) use ($search) {
                          $q->where('name', 'like', "%{$search}%");
                      });
            })
            ->when($request->type, function ($query, $type) {
                $query->where('activity_type', $type);
            })
            ->latest()
            ->paginate(20)
            ->withQueryString();

        return Inertia::render('Activities/Index', [
            'activities' => $activities,
            'filters' => $filters,
        ]);
    }

    /**
     * Display the specified resource.
     */
    public function show(Activity $activity): Response
    {
        return Inertia::render('Activities/Show', [
            'activity' => $activity->load('user'),
        ]);
    }
}

<?php

namespace App\Http\Controllers;

use App\Models\Audio;
use App\Models\Book;
use App\Models\Manuscript;
use App\Models\Video;
use App\Models\Series;
use App\Models\Collection;
use App\Models\Category;
use App\Models\Tag;
use App\Models\Comment;
use App\Models\Activity;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    public function index(): Response
    {
        $stats = [
            'books' => Book::count(),
            'authors' => \App\Models\Author::count(),
            'publishers' => \App\Models\Publisher::count(),
            'videos' => Video::count(),
            'audios' => Audio::count(),
            'manuscripts' => Manuscript::count(),
            'collections' => Collection::count(),
            'series' => Series::count(),
            'categories' => Category::count(),
            'tags' => Tag::count(),
            'comments' => Comment::count(),
            'activities' => Activity::count(),
            'versions' => \App\Models\Version::count(),
        ];

        // 1. Unified Recent Additions (Books & Manuscripts)
        $recentBooks = Book::with(['authors', 'categories'])->latest()->limit(4)->get()->map(fn($b) => [...$b->toArray(), 'type' => 'book']);
        $recentManuscripts = Manuscript::with(['authors', 'categories'])->latest()->limit(4)->get()->map(fn($m) => [...$m->toArray(), 'type' => 'manuscript']);
        
        $recentEntities = $recentBooks->concat($recentManuscripts)
            ->sortByDesc('created_at')
            ->take(6)
            ->values();

        // 2. Fetch recent activities
        $recentActivities = Activity::with(['user', 'entity'])
            ->latest()
            ->limit(10)
            ->get()
            ->map(function ($activity) {
                return [
                    'id' => $activity->id,
                    'type' => $activity->entity_type ? strtolower(class_basename($activity->entity_type)) : 'system',
                    'activity_type' => $activity->activity_type,
                    'description' => $activity->description,
                    'entity_title' => $activity->entity?->title ?? 'النظام',
                    'user_name' => $activity->user?->name ?? 'النظام',
                    'created_at' => $activity->created_at,
                    'entity_slug' => $activity->entity?->slug,
                ];
            });

        return Inertia::render('Dashboard', [
            'stats' => $stats,
            'recent' => $recentActivities,
            'recentEntities' => $recentEntities,
        ]);
    }
}

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

class DashboardController extends Controller
{
    public function index()
    {
        $stats = [
            'books' => Book::count(),
            'videos' => Video::count(),
            'audios' => Audio::count(),
            'manuscripts' => Manuscript::count(),
            'collections' => Collection::count(),
            'series' => Series::count(),
            'categories' => Category::count(),
            'tags' => Tag::count(),
            'comments' => Comment::count(),
            'activities' => Activity::count(),
        ];

        // Fetch 5 most recent activities with their related entity and user
        $recent = Activity::with(['user', 'entity'])
            ->latest()
            ->limit(8)
            ->get()
            ->map(function ($activity) {
                return [
                    'id' => $activity->id,
                    'type' => strtolower(class_basename($activity->entity_type)),
                    'activity_type' => $activity->activity_type,
                    'description' => $activity->description,
                    'entity_title' => $activity->entity?->title ?? 'عنصر محذوف',
                    'user_name' => $activity->user?->name ?? 'النظام',
                    'created_at' => $activity->created_at,
                    'entity_slug' => $activity->entity?->slug,
                ];
            });

        return Inertia::render('Dashboard', [
            'stats' => $stats,
            'recent' => $recent,
        ]);
    }
}

<?php

namespace App\Http\Controllers;

use App\Models\Audio;
use App\Models\Book;
use App\Models\Manuscript;
use App\Models\Video;
use Inertia\Inertia;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index()
    {
        $stats = [
            'books' => Book::count(),
            'videos' => Video::count(),
            'audios' => Audio::count(),
            'manuscripts' => Manuscript::count(),
        ];

        // Fetch 5 most recent across all types
        // Note: Using union for a simple "Global Recent Activity"
        $recent = DB::table('books')
            ->select('id', 'title', 'created_at', DB::raw("'book' as type"))
            ->union(
                DB::table('videos')->select('id', 'title', 'created_at', DB::raw("'video' as type"))
            )
            ->union(
                DB::table('audios')->select('id', 'title', 'created_at', DB::raw("'audio' as type"))
            )
            ->union(
                DB::table('manuscripts')->select('id', 'title', 'created_at', DB::raw("'manuscript' as type"))
            )
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();

        return Inertia::render('Dashboard', [
            'stats' => $stats,
            'recent' => $recent,
        ]);
    }
}

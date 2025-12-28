<?php

namespace App\Http\Controllers;

use App\Models\Book;
use App\Models\Manuscript;
use App\Models\Audio;
use App\Models\Video;
use App\Models\Author;
use App\Models\Series;
use Illuminate\Http\Request;
use Inertia\Inertia;

class GlobalSearchController extends Controller
{
    public function index(Request $request)
    {
        $term = $request->input('q');

        if (empty($term)) {
            return Inertia::render('Search/Index', [
                'results' => [],
                'term' => '',
            ]);
        }

        $results = [
            'books' => Book::where('title', 'like', "%{$term}%")->limit(5)->get(),
            'manuscripts' => Manuscript::where('title', 'like', "%{$term}%")->limit(5)->get(),
            'audios' => Audio::where('title', 'like', "%{$term}%")->limit(5)->get(),
            'videos' => Video::where('title', 'like', "%{$term}%")->limit(5)->get(),
            'authors' => Author::where('name', 'like', "%{$term}%")->limit(5)->get(),
            'series' => Series::where('title', 'like', "%{$term}%")->limit(5)->get(),
        ];

        return Inertia::render('Search/Index', [
            'results' => $results,
            'term' => $term,
        ]);
    }
}

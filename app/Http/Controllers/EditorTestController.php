<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;

class EditorTestController extends Controller
{
    public function index()
    {
        // Mock book and child data for testing
        $book = (object) [
            'id' => 1,
            'title' => 'كتاب اختبار',
            'slug' => 'test-book',
        ];

        $child = (object) [
            'id' => 1,
            'title' => 'الباب الأول',
            'content' => '<p>هذا نص تجريبي للمحرر...</p>',
        ];

        return Inertia::render('EditorTest', [
            'book' => $book,
            'child' => $child,
        ]);
    }
}

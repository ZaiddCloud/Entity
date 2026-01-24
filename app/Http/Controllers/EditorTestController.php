<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;

class EditorTestController extends Controller
{
    public function index(Request $request)
    {
        $mode = $request->query('mode', 'book');

        // Mock book and child data for testing
        $book = (object) [
            'id' => 1,
            'title' => 'كتاب اختبار التعددية',
            'slug' => 'test-book',
            'author' => 'مؤلف تجريبي',
        ];

        $child = (object) [
            'id' => 'mock-id-123',
            'title' => 'الباب الأول (اختبار)',
            'content' => '<p>هذا نص تدريبي للمحرر المتعدد الأنماط...</p><p>يمكنك تجربة الأنماط المختلفة عبر تغيير رابط التحميل.</p>',
        ];

        return Inertia::render('Editor/EditorPage', [
            'entity' => $book,
            'contentNode' => $child,
            'hierarchy' => [
                ['_id' => '1', 'title' => 'المقدمة', 'slug' => 'intro', 'type' => 'chapter', 'parent_id' => null, 'order' => 1],
                ['_id' => '2', 'title' => 'الباب الأول', 'slug' => 'part-1', 'type' => 'chapter', 'parent_id' => null, 'order' => 2],
                ['_id' => '3', 'title' => 'الفصل الأول', 'slug' => 'chapter-1', 'type' => 'page', 'parent_id' => '2', 'order' => 1],
                ['_id' => '4', 'title' => 'الفصل الثاني', 'slug' => 'chapter-2', 'type' => 'page', 'parent_id' => '2', 'order' => 2],
            ],
            'navigation' => [
                'prev' => ['slug' => 'intro', 'title' => 'المقدمة'],
                'next' => ['slug' => 'chapter-1', 'title' => 'الفصل الأول'],
            ],
            'editor_mode' => $mode,
            'resource_data' => $mode === 'manuscript' ? [
                'versions' => [
                    ['title' => 'النسخة (أ) - الأزهرية', 'url' => 'https://example.com/ms-a.pdf'],
                    ['title' => 'النسخة (ب) - تشستربيتي', 'url' => 'https://example.com/ms-b.pdf'],
                    ['title' => 'النسخة (ج) - ليدن', 'url' => 'https://example.com/ms-c.pdf'],
                ],
                'id' => 'ms-1'
            ] : ($mode !== 'book' ? [
                    'versions' => ($mode === 'audio' || $mode === 'video') ? [
                        ['title' => 'التسجيل الأساسي', 'url' => 'https://example.com/audio-main.mp3'],
                        ['title' => 'تسجيل بديل (منقح)', 'url' => 'https://example.com/audio-alt.mp3'],
                        ['title' => 'تسجيل ميداني', 'url' => 'https://example.com/audio-field.mp3'],
                    ] : null,
                    'url' => 'https://example.com/resource',
                    'id' => 'res-1'
                ] : null)
        ]);
    }
}

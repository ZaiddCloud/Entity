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

        return Inertia::render('Books/Editor/EditorPage', [
            'book' => $book,
            'child' => $child,
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

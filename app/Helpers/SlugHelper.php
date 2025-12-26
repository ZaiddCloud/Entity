<?php

namespace App\Helpers;

class SlugHelper
{
    public static function arabicSlug(string $text): string
    {
        // أبسط حل أولاً
        $slug = trim($text);
        $slug = preg_replace('/\s+/u', '-', $slug);
        $slug = preg_replace('/[^\p{Arabic}\p{N}\-]/u', '', $slug);
        return mb_strtolower($slug, 'UTF-8');
    }
}

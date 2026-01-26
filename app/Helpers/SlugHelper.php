<?php

namespace App\Helpers;

class SlugHelper
{
    public static function arabicSlug(string $text): string
    {
        // 1. Convert to lowercase for Latin characters
        $slug = mb_strtolower($text, 'UTF-8');
        
        // 2. Replace multiple spaces/underscores with a single dash
        $slug = preg_replace('/[\s_]+/u', '-', $slug);
        
        // 3. Remove any character that is NOT a letter (Arabic/Latin/etc), a number, or a dash
        $slug = preg_replace('/[^\p{L}\p{N}\-]/u', '', $slug);
        
        // 4. Trim dashes from ends
        return trim($slug, '-');
    }
    public static function generate(string $text): string
    {
        return static::arabicSlug($text);
    }
}

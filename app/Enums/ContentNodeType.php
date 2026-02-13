<?php

namespace App\Enums;

enum ContentNodeType: string
{
    // ===== Book Content Types (Arabic Structure) =====
    case SUB_BOOK = 'sub-book';    // كتاب فرعي
    case PART = 'part';            // جزء
    case BAB = 'bab';              // باب
    case CHAPTER = 'chapter';      // فصل
    case MASALAH = 'masalah';      // مسألة
    case PAGE = 'page';            // صفحة
    case SECTION = 'section';      // قسم
    
    // ===== Manuscript Content Types =====
    case FOLIO = 'folio';          // ورقة
    // PAGE already defined above
    // SECTION already defined above
    
    // ===== Audio Content Types =====
    case SEGMENT = 'segment';      // مقطع
    case TRACK = 'track';          // مسار
    case MARKER = 'marker';        // علامة
    
    // ===== Video Content Types =====
    // SEGMENT already defined above
    case SCENE = 'scene';          // مشهد
    case SHOT = 'shot';            // لقطة

    /**
     * Get allowed content types for a given entity type
     *
     * @return self[]
     */
    public static function allowedFor(EntityType $entityType): array
    {
        return match($entityType) {
            EntityType::BOOK => [
                self::SUB_BOOK,
                self::PART,
                self::BAB,
                self::CHAPTER,
                self::MASALAH,
                self::SECTION,
                self::PAGE,
            ],
            EntityType::MANUSCRIPT => [
                self::SUB_BOOK,
                self::PART,
                self::BAB,
                self::CHAPTER,
                self::MASALAH,
                self::SECTION,
                self::PAGE,
                self::FOLIO,
            ],
            EntityType::AUDIO => [
                self::SEGMENT,
                self::TRACK,
                self::MARKER,
            ],
            EntityType::VIDEO => [
                self::SEGMENT,
                self::SCENE,
                self::SHOT,
            ],
        };
    }

    /**
     * Get the visual mapping for this content type
     *
     * @return array{tag: string, behavior: string}
     */
    public function visualMap(): array
    {
        return match($this) {
            self::SUB_BOOK => ['tag' => 'h1', 'behavior' => 'container'],
            self::PART => ['tag' => 'h2', 'behavior' => 'container'],
            self::BAB => ['tag' => 'h3', 'behavior' => 'container'],
            self::CHAPTER => ['tag' => 'h4', 'behavior' => 'container'],
            self::MASALAH => ['tag' => 'h5', 'behavior' => 'container'],
            self::SECTION => ['tag' => 'h6', 'behavior' => 'container'],
            
            self::FOLIO, self::PAGE, self::SEGMENT, self::TRACK, self::SCENE => ['tag' => 'h4', 'behavior' => 'marker'],
            self::MARKER, self::SHOT => ['tag' => 'h5', 'behavior' => 'marker'],
        };
    }

    /**
     * Get the default/primary content type for an entity
     */
    public static function defaultFor(EntityType $entityType): self
    {
        return match($entityType) {
            EntityType::BOOK => self::CHAPTER,
            EntityType::MANUSCRIPT => self::PAGE,
            EntityType::AUDIO => self::SEGMENT,
            EntityType::VIDEO => self::SCENE,
        };
    }

    /**
     * Check if this content type is valid for the given entity type
     */
    public function isValidFor(EntityType $entityType): bool
    {
        return in_array($this, self::allowedFor($entityType), true);
    }

    /**
     * Get Arabic label for this content type
     */
    public function label(): string
    {
        return match($this) {
            self::SUB_BOOK => 'كتاب فرعي',
            self::PART => 'جزء',
            self::BAB => 'باب',
            self::CHAPTER => 'فصل',
            self::MASALAH => 'مسألة',
            self::PAGE => 'صفحة',
            self::SECTION => 'قسم',
            self::FOLIO => 'ورقة',
            self::SEGMENT => 'مقطع',
            self::TRACK => 'مسار',
            self::MARKER => 'علامة',
            self::SCENE => 'مشهد',
            self::SHOT => 'لقطة',
        };
    }

    /**
     * Get allowed values as strings for a given entity type
     *
     * @return string[]
     */
    public static function allowedValuesFor(EntityType $entityType): array
    {
        return array_map(
            fn(self $enum) => $enum->value,
            self::allowedFor($entityType)
        );
    }
}

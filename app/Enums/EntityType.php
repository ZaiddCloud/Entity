<?php

namespace App\Enums;

enum EntityType: string
{
    case BOOK = 'book';
    case AUDIO = 'audio';
    case VIDEO = 'video';
    case MANUSCRIPT = 'manuscript';

    /**
     * Get the model class for this entity type
     */
    public function modelClass(): string
    {
        return match($this) {
            self::BOOK => \App\Models\Book::class,
            self::AUDIO => \App\Models\Audio::class,
            self::VIDEO => \App\Models\Video::class,
            self::MANUSCRIPT => \App\Models\Manuscript::class,
        };
    }

    /**
     * Get the default file format for this entity type
     */
    public function defaultFormat(): string
    {
        return match($this) {
            self::BOOK, self::MANUSCRIPT => 'pdf',
            self::AUDIO => 'mp3',
            self::VIDEO => 'mp4',
        };
    }

    /**
     * Check if this entity type supports duration
     */
    public function supportsDuration(): bool
    {
        return $this === self::AUDIO || $this === self::VIDEO;
    }

    /**
     * Check if this entity type supports pages
     */
    public function supportsPages(): bool
    {
        return $this === self::BOOK || $this === self::MANUSCRIPT;
    }

    /**
     * Get Arabic label for this entity type
     */
    public function label(): string
    {
        return match($this) {
            self::BOOK => 'كتاب',
            self::AUDIO => 'صوتي',
            self::VIDEO => 'مرئي',
            self::MANUSCRIPT => 'مخطوطة',
        };
    }

    /**
     * Get all entity type values as array
     */
    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}

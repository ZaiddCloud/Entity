<?php

namespace App\Models;

class Audio extends Entity
{
    protected $table = 'audios';

    protected $fillable = [
        'title', 'slug', 'duration', 'format',
        'bitrate', 'sample_rate', 'file_size',
        'created_at', 'updated_at'
    ];

    /**
     * خصائص إضافية للصوت
     */

    public function getDurationInMinutesAttribute(): float
    {
        return $this->duration / 60;
    }

    public function getDurationFormattedAttribute(): string
    {
        $hours = floor($this->duration / 3600);
        $minutes = floor(($this->duration % 3600) / 60);
        $seconds = $this->duration % 60;

        if ($hours > 0) {
            return sprintf('%d:%02d:%02d', $hours, $minutes, $seconds);
        }

        return sprintf('%d:%02d', $minutes, $seconds);
    }

    public function getBitrateFormattedAttribute(): string
    {
        return $this->bitrate . ' kbps';
    }

    public function getSampleRateFormattedAttribute(): string
    {
        return $this->sample_rate . ' Hz';
    }
}

<?php

namespace Tests\Unit\Models;

use Tests\TestCase;
use App\Models\Audio;
use Illuminate\Foundation\Testing\RefreshDatabase;

class AudioTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function audio_extends_entity()
    {
        $audio = new Audio(['title' => 'Test Audio']);
        $this->assertInstanceOf(\App\Models\Entity::class, $audio);
    }

    /** @test */
    public function audio_has_audio_specific_properties()
    {
        $audio = Audio::create([
            'title' => 'Quran Recitation',
            'duration' => 3600, // ساعة
            'format' => 'mp3',
            'bitrate' => 128,
            'sample_rate' => 44100
        ]);

        $this->assertEquals('Quran Recitation', $audio->title);
        $this->assertEquals('quran-recitation', $audio->slug);
        $this->assertEquals(3600, $audio->duration);
        $this->assertEquals('mp3', $audio->format);
        $this->assertEquals(128, $audio->bitrate);
        $this->assertEquals(44100, $audio->sample_rate);
    }

    /** @test */
    public function audio_formats_duration_correctly()
    {
        $audio = Audio::create([
            'title' => 'Short Audio',
            'duration' => 125 // دقيقتان و5 ثواني
        ]);

        $this->assertEquals(2.08, round($audio->duration_in_minutes, 2));
        $this->assertEquals('2:05', $audio->duration_formatted);
    }
}

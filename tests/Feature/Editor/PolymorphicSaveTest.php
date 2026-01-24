<?php

namespace Tests\Feature\Editor;

use Tests\TestCase;
use App\Models\User;
use App\Models\Book;
use Illuminate\Foundation\Testing\RefreshDatabase;

class PolymorphicSaveTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_save_book_content()
    {
        // Todo: Implement test
    }

    public function test_can_save_manuscript_transcription()
    {
        // Todo: Implement test
    }

    public function test_can_save_media_transcription()
    {
        // Todo: Implement test
    }
}

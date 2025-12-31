<?php

namespace Tests\Feature;

use App\Models\Book;
use App\Models\BookChild;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class SyncProtectionTest extends TestCase
{
    use RefreshDatabase;

    public function test_storage_sync_skips_manually_edited_units()
    {
        // 1. Setup a book and a storage file
        Storage::fake('public');
        $book = Book::factory()->create(['slug' => 'test-book']);
        $filePath = 'books/test-book.md';
        Storage::disk('public')->put($filePath, "# Chapter 1\nFile Content");

        // 2. Initial Sync
        $this->artisan('storage:sync');
        $this->assertDatabaseHas('book_children', ['title' => 'Chapter 1'], 'mongodb');

        // 3. Mark as manually edited
        $child = BookChild::where('title', 'Chapter 1')->first();
        $child->update([
            'content_blocks' => [['type' => 'paragraph', 'content' => 'Manual Edit']],
            'is_manually_edited' => true
        ]);

        // 4. Modify File
        Storage::disk('public')->put($filePath, "# Chapter 1\nChanged File Content");

        // 5. Sync again
        $this->artisan('storage:sync');

        // 6. Verify Chapter 1 still has 'Manual Edit' content
        $child->refresh();
        $this->assertEquals('Manual Edit', $child->content_blocks[0]['content']);
        $this->assertTrue($child->is_manually_edited);
    }
}

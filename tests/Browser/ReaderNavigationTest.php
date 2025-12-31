<?php

namespace Tests\Browser;

use App\Models\Book;
use App\Models\BookChild;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTruncation;
use Laravel\Dusk\Browser;
use Tests\DuskTestCase;
use PHPUnit\Framework\Attributes\Test;

class ReaderNavigationTest extends DuskTestCase
{
    use DatabaseTruncation;

    /** @test */
    public function it_can_navigate_to_child_content_via_sidebar()
    {
        $this->browse(function (Browser $browser) {
            // 1. Create Data
            $user = User::factory()->create();
            $book = Book::factory()->create([
                'title' => 'Test Navigation Book',
                'slug' => 'test-nav-book'
            ]);

            $chapter = BookChild::create([
                'book_id' => $book->id,
                'title' => 'Test Chapter 1',
                'type' => 'chapter',
                'content_blocks' => [
                    ['type' => 'paragraph', 'body' => 'Content for Chapter 1'] // Changed from 'text' to 'paragraph' to match likely Renderer expectation
                ]
            ]);

            // 2. Login and Visit Reader
            $browser->loginAs($user)
                ->visit("/books/{$book->slug}/reader")
                ->waitForText('Test Navigation Book')
                ->assertSee('Test Chapter 1') // Sidebar item

                // 3. Click the Sidebar Item
                ->clickLink('Test Chapter 1')

                // 4. Verification
                // Wait for the URL to change
                ->waitForRoute('books.reader', ['book' => $book->slug, 'child' => $chapter->id])
                ->pause(1000) // Small pause for Vue to render

                // Check if content loaded
                ->assertSee('Content for Chapter 1')

                // 5. Persistence Check
                // The sidebar item should still be visible because the parent should remain open
                // (In this flat test case, it's a root item, so it's always visible. 
                // To test persistence properly, we need a nested structure).
            ;
        });
    }
}

<?php

namespace Tests\Browser;

use App\Models\User;
use App\Models\Author;
use App\Models\Manuscript;
use Illuminate\Foundation\Testing\DatabaseTruncation;
use Laravel\Dusk\Browser;
use Tests\DuskTestCase;

class ManuscriptCRUDTest extends DuskTestCase
{
    use DatabaseTruncation;

    /**
     * Test creating a manuscript with multiple authors (polymorphic relationship).
     */
    public function test_can_create_manuscript_with_multiple_authors(): void
    {
        $user = User::factory()->create();
        $author1 = Author::factory()->create(['name' => 'Al-Harith']);
        $author2 = Author::factory()->create(['name' => 'Al-Mundhir']);

        $this->browse(function (Browser $browser) use ($user, $author1, $author2) {
            $browser->loginAs($user)
                ->visit('/manuscripts/create')
                ->waitForText('إضافة مخطوطة جديدة')
                // Basic info
                ->type('#title', 'Manuscript for Browser Test')
                ->type('#manuscript_century', '8')
                ->type('#manuscript_century_label', '8 هـ')
                // Select multiple authors
                ->select('#authors', [(string) $author1->id, (string) $author2->id])
                // Description
                ->type('#description', 'Dusk E2E Verification')
                // Submit
                ->press('حفظ المخطوطة')
                // Verify redirection (most robust way)
                ->waitForLocation('/manuscripts', 20)
                ->assertPathIs('/manuscripts');

            // Verify in DB (SQL)
            $this->assertDatabaseHas('manuscripts', [
                'title' => 'Manuscript for Browser Test',
                'manuscript_century' => '8',
            ]);

            // Verify relation (Polymorphic)
            $manuscript = Manuscript::where('title', 'Manuscript for Browser Test')->first();
            $this->assertCount(2, $manuscript->authors);
            $this->assertTrue($manuscript->authors->contains($author1));
        });
    }
}

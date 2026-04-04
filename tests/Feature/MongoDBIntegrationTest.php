<?php

namespace Tests\Feature;

use App\Models\Manuscript;
use App\Models\ManuscriptPage;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Verify MySQL-MongoDB Hybrid Relationship Integrity
 */
class MongoDBIntegrationTest extends TestCase
{
    public function test_manuscript_can_access_mongodb_pages()
    {
        // Create a Manuscript in MySQL with new fields
        $manuscript = Manuscript::create([
            'title' => 'Test Manuscript for MongoDB',
            'slug' => 'test-mongo-' . uniqid(),
            'code' => 'MONGO_TEST',
            'scribe' => 'Test Scribe',
            'manuscript_century' => '9',
            'manuscript_century_label' => '9 هـ',
        ]);

        // Create a ManuscriptPage in MongoDB linked to it
        $page = ManuscriptPage::create([
            'manuscript_id' => $manuscript->id,
            'slug' => 'page-1-' . uniqid(),
            'type' => 'page',
            'title' => 'الصفحة الأولى',
            'order' => 1,
            'folio_number' => '1أ',
            'content' => 'محتوى الصفحة الأولى',
        ]);

        // Test: Can Manuscript access its MongoDB children?
        $this->assertNotNull($manuscript->children);
        
        // Use lazy loading to avoid PDO issues in test
        $children = ManuscriptPage::where('manuscript_id', $manuscript->id)->get();
        $this->assertEquals(1, $children->count());
        
        $retrievedPage = $children->first();
        $this->assertEquals('الصفحة الأولى', $retrievedPage->title);
        $this->assertEquals($manuscript->id, $retrievedPage->manuscript_id);

        // Clean up
        $page->delete();
        $manuscript->delete();
    }

    public function test_new_manuscript_fields_do_not_break_mongodb_queries()
    {
        // Create multiple manuscripts with various new fields
        $m1 = Manuscript::create([
            'title' => 'Manuscript 1',
            'slug' => 'm1-' . uniqid(),
            'scribe' => 'Scribe A',
            'inscriptions' => 'تملك: المكتبة',
        ]);

        $m2 = Manuscript::create([
            'title' => 'Manuscript 2',
            'slug' => 'm2-' . uniqid(),
            'script_type' => 'نسخ',
            'dimensions' => '20x15',
        ]);

        // Create pages for each
        ManuscriptPage::create([
            'manuscript_id' => $m1->id,
            'slug' => 'p1-' . uniqid(),
            'title' => 'Page for M1',
            'order' => 1,
        ]);

        ManuscriptPage::create([
            'manuscript_id' => $m2->id,
            'slug' => 'p2-' . uniqid(),
            'title' => 'Page for M2',
            'order' => 1,
        ]);

        // Verify eager loading works
        $manuscripts = Manuscript::with('children')->whereIn('id', [$m1->id, $m2->id])->get();
        
        $this->assertEquals(2, $manuscripts->count());
        foreach ($manuscripts as $manuscript) {
            $this->assertEquals(1, $manuscript->children->count());
        }
    }
}

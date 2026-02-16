<?php

namespace Tests\Browser\Studio;

use App\Models\User;
use App\Models\Book;
use App\Models\BookChild;
use App\Models\Manuscript;
use App\Models\ManuscriptPage;
use App\Enums\ContentNodeType;
use Laravel\Dusk\Browser;
use Tests\DuskTestCase;
use Illuminate\Support\Str;
use Illuminate\Foundation\Testing\DatabaseTruncation;

class StudioHierarchyTest extends DuskTestCase
{
    use DatabaseTruncation;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Ensure clean slate for MongoDB collections
        BookChild::truncate();
        ManuscriptPage::truncate();
    }
    /**
     * Test dynamic heading levels based on depth (H2-H6).
     */
    public function test_dynamic_heading_levels_reflect_depth()
    {
        $user = User::factory()->create();
        $book = Book::factory()->create(['title' => 'كتاب الهيكلية الديناميكية', 'slug' => 'dynamic-hierarchy-book-' . uniqid()]);

        // Depth 0: Root (H2)
        $rootId = (string) Str::uuid();
        $root = new BookChild();
        $root->_id = $rootId;
        $root->book_id = $book->id;
        $root->slug = 'root-node';
        $root->type = 'sub-book';
        $root->title = 'الكتاب الأول';
        $root->order = 1;
        $root->parent_id = null;
        $root->content = "<p>نص الجذر</p>";
        $root->save();

        // Depth 1: Level 1 (H3)
        $level1Id = (string) Str::uuid();
        $level1 = new BookChild();
        $level1->_id = $level1Id;
        $level1->book_id = $book->id;
        $level1->slug = 'level-1-node';
        $level1->type = 'part';
        $level1->title = 'الجزء الأول';
        $level1->order = 2;
        $level1->parent_id = $rootId;
        $level1->content = "<p>نص المستوى الأول</p>";
        $level1->save();

        // Depth 2: Level 2 (H4)
        $level2Id = (string) Str::uuid();
        $level2 = new BookChild();
        $level2->_id = $level2Id;
        $level2->book_id = $book->id;
        $level2->slug = 'level-2-node';
        $level2->type = 'bab';
        $level2->title = 'الباب الأول';
        $level2->order = 3;
        $level2->parent_id = $level1Id;
        $level2->content = "<p>نص المستوى الثاني</p>";
        $level2->save();

        $this->browse(function (Browser $browser) use ($user, $book, $rootId, $level1Id, $level2Id) {
            $browser->loginAs($user)
                    ->visit("/studio/book/{$book->slug}")
                    ->waitForText('الكتاب الأول', 10)
                    
                    // Verify correct heading tags for each depth
                    ->assertPresent("h2.structure-marker[data-id='{$rootId}']")
                    ->assertPresent("h3.structure-marker[data-id='{$level1Id}']")
                    ->assertPresent("h4.structure-marker[data-id='{$level2Id}']");
        });
    }

    /**
     * Test that paragraphs are ALWAYS rendered as normal text (p) regardless of depth.
     */
    public function test_paragraphs_always_render_as_normal_text()
    {
        $user = User::factory()->create();
        $book = Book::factory()->create(['title' => 'اختبار الفقرات', 'slug' => 'paragraph-test-book-' . uniqid()]);

        // Paragraph as Root (Depth 0) -> Should be P
        $p1Id = (string) Str::uuid();
        $p1 = new BookChild();
        $p1->_id = $p1Id;
        $p1->book_id = $book->id;
        $p1->slug = 'p1';
        $p1->type = 'paragraph';
        $p1->title = 'الفقرة الأولى';
        $p1->order = 1;
        $p1->parent_id = null;
        $p1->content = "بقية نص الفقرة الأولى";
        $p1->save();

        $this->browse(function (Browser $browser) use ($user, $book, $p1Id) {
            $browser->loginAs($user)
                    ->visit("/studio/book/{$book->slug}")
                    ->waitForText('الفقرة الأولى', 10)
                    ->waitFor(".structure-marker-text[data-id='{$p1Id}']", 10)
                    ->assertPresent(".structure-marker-text[data-id='{$p1Id}']")
                    ->assertMissing(".structure-marker[data-id='{$p1Id}']");
        });
    }

    /**
     * Test branch aggregation (Clicking header loads descendants).
     */
    public function test_clicking_header_loads_entire_branch()
    {
        $user = User::factory()->create();
        $book = Book::factory()->create(['title' => 'اختبار التجميع', 'slug' => 'branch-test-book-' . uniqid()]);

        // Create structure: Chapter -> (Masalah 1, Masalah 2)
        $parentChapterId = (string) Str::uuid();
        $parentChapter = new BookChild();
        $parentChapter->_id = $parentChapterId;
        $parentChapter->book_id = $book->id;
        $parentChapter->slug = 'parent-chapter';
        $parentChapter->type = 'chapter';
        $parentChapter->title = 'الفصل الجامع';
        $parentChapter->order = 1;
        $parentChapter->parent_id = null;
        $parentChapter->content = '<p>مقدمة الفصل</p>';
        $parentChapter->save();

        $child1Id = (string) Str::uuid();
        $child1 = new BookChild();
        $child1->_id = $child1Id;
        $child1->book_id = $book->id;
        $child1->slug = 'child-1';
        $child1->type = 'masalah';
        $child1->title = 'المسألة الأولى';
        $child1->order = 2;
        $child1->parent_id = $parentChapterId;
        $child1->content = 'محتوى المسألة 1';
        $child1->save();

        $this->browse(function (Browser $browser) use ($user, $book, $parentChapterId, $child1Id) {
            $browser->loginAs($user)
                    ->visit("/studio/book/{$book->slug}")
                    ->waitForText('الفصل الجامع')
                    ->waitFor("h2.structure-marker[data-id='{$parentChapterId}']")
                    
                    // Click the parent header (should trigger branch load)
                    ->click("h2.structure-marker[data-id='{$parentChapterId}']")
                    ->waitForText('المسألة الأولى')
                    ->assertSee('محتوى المسألة 1');
        });
    }

    /**
     * Test shared folio logic in Manuscripts.
     */
    public function test_shared_folio_between_parents()
    {
        $user = User::factory()->create();
        $manu = Manuscript::factory()->create(['title' => 'مخطوط التشارك', 'slug' => 'shared-folio-manu-' . uniqid()]);

        $bab1Id = (string) Str::uuid();
        $bab1 = new ManuscriptPage();
        $bab1->manuscript_id = $manu->id;
        $bab1->_id = $bab1Id;
        $bab1->slug = 'bab-1';
        $bab1->type = 'bab';
        $bab1->title = 'الباب الأول';
        $bab1->order = 1;
        $bab1->parent_id = null;
        $bab1->content = '<p>محتوى الباب الأول</p>';
        $bab1->save();

        $bab2Id = (string) Str::uuid();
        $bab2 = new ManuscriptPage();
        $bab2->manuscript_id = $manu->id;
        $bab2->_id = $bab2Id;
        $bab2->slug = 'bab-2';
        $bab2->type = 'bab';
        $bab2->title = 'الباب الثاني';
        $bab2->order = 2;
        $bab2->parent_id = null;
        $bab2->content = '<p>محتوى الباب الثاني</p>';
        $bab2->save();

        $folioId = (string) Str::uuid();
        $folio = new ManuscriptPage();
        $folio->manuscript_id = $manu->id;
        $folio->_id = $folioId;
        $folio->slug = 'folio-5b';
        $folio->type = 'folio';
        $folio->title = 'الورقة 5ب';
        $folio->order = 3;
        $folio->parent_id = $bab1Id;
        $folio->content = '<p>نص الورقة المشتركة</p>';
        $folio->save();
        $this->browse(function (Browser $browser) use ($user, $manu, $bab1Id, $bab2Id) {
            $browser->loginAs($user)
                    ->visit("/studio/manuscript/{$manu->slug}")
                    ->waitForText('الباب الأول', 10)
                    ->script([
                        'console.log("FULL DOM DUMP:", document.body.innerHTML);'
                    ]);

            $logs = $browser->driver->manage()->getLog('browser');
            foreach ($logs as $log) {
                if (str_contains($log['message'], 'FULL DOM DUMP')) {
                    echo "--- DOM DUMP START ---\n";
                    echo $log['message'] . "\n";
                    echo "--- DOM DUMP END ---\n";
                }
            }

            $browser->waitFor(".structure-marker[data-id='{$bab1Id}']", 10)
                    
                    // Verify under Bab 1
                    ->click(".structure-marker[data-id='{$bab1Id}']")
                    ->waitForText('الورقة 5ب');
                    
            // TODO: Logic for second parent if we implement multi-parenting in DB
        });
    }

    /**
     * Test exhaustive matrix of all structural node types.
     */
    public function test_exhaustive_node_type_matrix()
    {
        $user = User::factory()->create();
        $book = Book::factory()->create(['title' => 'مصفوفة الأنواع', 'slug' => 'matrix-book-' . uniqid()]);

        $types = [
            'sub-book', 'part', 'volume', 'bab', 'chapter', 
            'section', 'masalah', 'page', 'folio'
        ];

        foreach ($types as $index => $type) {
            $id = (string) Str::uuid();
            $node = new BookChild();
            $node->book_id = $book->id;
            $node->_id = $id;
            $node->slug = "node-{$type}";
            $node->type = $type;
            $node->title = "عنوان {$type}";
            $node->order = $index + 1;
            $node->parent_id = null;
            $node->content = "<p>محتوى {$type}</p>";
            $node->save();
        }

        $this->browse(function (Browser $browser) use ($user, $book, $types) {
            $browser->loginAs($user)
                    ->visit("/studio/book/{$book->slug}")
                    ->waitFor('.structure-marker');
            
            foreach ($types as $type) {
                // Assert each type is present with its structure-marker
                $browser->assertPresent(".structure-marker[data-type='{$type}']");
            }
        });
    }
}

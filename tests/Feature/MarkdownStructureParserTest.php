<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Services\Book\MarkdownStructureParser;

class MarkdownStructureParserTest extends TestCase
{
    /**
     * Test that the parser detects all heading levels from H1 to H8.
     */
    public function test_parser_detects_heading_levels_accurately()
    {
        $markdown = "# Level 1\nContent 1\n## Level 2\nContent 2\n### Level 3\n#### Level 4\n##### Level 5\n###### Level 6\n####### Level 7\n######## Level 8";

        $parser = new MarkdownStructureParser();
        $structure = $parser->parse($markdown);

        $this->assertCount(8, $structure);
        $this->assertEquals('sub-book', $structure[0]['type']); // H1 -> sub-book
        $this->assertEquals('part', $structure[1]['type']);     // H2 -> part
        $this->assertEquals('bab', $structure[2]['type']);      // H3 -> bab
        $this->assertEquals('chapter', $structure[3]['type']);  // H4 -> chapter
        $this->assertEquals('masala', $structure[4]['type']);   // H5 -> masala
        $this->assertEquals('masala', $structure[5]['type']);   // H6+ -> masala (capped)
    }

    /**
     * Test that paragraphs are attached to the nearest preceding heading.
     */
    public function test_parser_attaches_paragraphs_to_correct_parent()
    {
        $markdown = "# Intro\nParagraph 1\nParagraph 2\n## Deep\nParagraph 3";

        $parser = new MarkdownStructureParser();
        $structure = $parser->parse($markdown);

        $this->assertCount(2, $structure[0]['blocks']); // Intro has 2 paragraphs
        $this->assertCount(1, $structure[1]['blocks']); // Deep has 1 paragraph
        $this->assertEquals('Paragraph 1', $structure[0]['blocks'][0]['content']);
    }

    /**
     * Test semantic hierarchy based on depth.
     */
    public function test_parser_creates_semantic_hierarchy_by_depth()
    {
        $markdown = "# Parent\n## Child 1\n## Child 2\n### Grandchild";

        $parser = new MarkdownStructureParser();
        $hierarchy = $parser->buildHierarchy($markdown);

        $this->assertCount(1, $hierarchy); // One root H1
        $this->assertCount(2, $hierarchy[0]['children']); // Two H2 children
        $this->assertCount(1, $hierarchy[0]['children'][1]['children']); // Second H2 has one H3 child
    }

    /**
     * Test ultra-deep nesting up to H8.
     */
    public function test_parser_handles_ultra_deep_nesting_up_to_h8()
    {
        $markdown = "# H1\n## H2\n### H3\n#### H4\n##### H5\n###### H6\n####### H7\n######## H8";

        $parser = new MarkdownStructureParser();
        $hierarchy = $parser->buildHierarchy($markdown);

        $this->assertCount(1, $hierarchy);
        $this->assertCount(1, $hierarchy[0]['children']);
        $this->assertCount(1, $hierarchy[0]['children'][0]['children'][0]['children'][0]['children'][0]['children'][0]['children']); // Level 8
    }
}

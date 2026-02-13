<?php

namespace Tests\Browser\Studio;

use App\Models\User;
use App\Enums\ContentNodeType;
use App\Enums\EntityType;
use Laravel\Dusk\Browser;
use Tests\DuskTestCase;

/**
 * Step 1: The Harmonic Contract
 * 
 * This test defines the expected behavior of ContentNodeType for ALL entities,
 * ensuring "Structural Harmony" where identical types behave identically.
 */
class StudioContentStructureTest extends DuskTestCase
{
    /**
     * Test that ContentNodeType returns correct allowed types for Books
     *
     * @test
     */
    public function it_returns_correct_allowed_types_for_books()
    {
        $expected = ['sub-book', 'part', 'bab', 'chapter', 'masalah', 'section', 'page'];
        $actual = ContentNodeType::allowedValuesFor(EntityType::BOOK);
        
        $this->assertEquals($expected, $actual, 'Book should allow specific content types');
    }

    /**
     * @test
     */
    public function it_returns_correct_allowed_types_for_manuscripts()
    {
        // Manuscripts inherit all book types + folio
        $expected = ['sub-book', 'part', 'bab', 'chapter', 'masalah', 'section', 'page', 'folio'];
        $actual = ContentNodeType::allowedValuesFor(EntityType::MANUSCRIPT);
        
        $this->assertEquals($expected, $actual, 'Manuscript should allow book types + folio');
    }

    /**
     * @test
     */
    public function it_returns_correct_allowed_types_for_audio()
    {
        $expected = ['segment', 'track', 'marker'];
        $actual = ContentNodeType::allowedValuesFor(EntityType::AUDIO);
        
        $this->assertEquals($expected, $actual, 'Audio should allow time-based types');
    }

    /**
     * @test
     */
    public function it_returns_correct_allowed_types_for_video()
    {
        $expected = ['scene', 'shot', 'segment'];
        $actual = ContentNodeType::allowedValuesFor(EntityType::VIDEO);
        
        $this->assertEquals($expected, $actual, 'Video should allow scene-based types');
    }

    /**
     * Test visual mapping for Book types
     *
     * @test
     */
    public function it_returns_correct_visual_map_for_book_types()
    {
        $visualMap = ContentNodeType::getVisualMap(EntityType::BOOK);
        
        // Test structural containers
        $this->assertEquals('h1', $visualMap['sub-book']['tag']);
        $this->assertEquals('container', $visualMap['sub-book']['behavior']);
        
        $this->assertEquals('h2', $visualMap['part']['tag']);
        $this->assertEquals('container', $visualMap['part']['behavior']);
        
        $this->assertEquals('h3', $visualMap['bab']['tag']);
        $this->assertEquals('container', $visualMap['bab']['behavior']);
        
        $this->assertEquals('h4', $visualMap['chapter']['tag']);
        $this->assertEquals('container', $visualMap['chapter']['behavior']);
        
        $this->assertEquals('h5', $visualMap['masalah']['tag']);
        $this->assertEquals('container', $visualMap['masalah']['behavior']);
        
        $this->assertEquals('h6', $visualMap['section']['tag']);
        $this->assertEquals('container', $visualMap['section']['behavior']);
    }

    /**
     * Test visual mapping for Manuscript types (markers)
     *
     * @test
     */
    public function it_returns_correct_visual_map_for_manuscript_markers()
    {
        $visualMap = ContentNodeType::getVisualMap(EntityType::MANUSCRIPT);
        
        // Folio and Page are markers
        $this->assertEquals('h4', $visualMap['folio']['tag']);
        $this->assertEquals('marker', $visualMap['folio']['behavior']);
        
        $this->assertEquals('h4', $visualMap['page']['tag']);
        $this->assertEquals('marker', $visualMap['page']['behavior']);
        
        // Inherited types should match book behavior
        $this->assertEquals('h4', $visualMap['chapter']['tag']);
        $this->assertEquals('container', $visualMap['chapter']['behavior']);
    }

    /**
     * Test visual mapping for Audio types (all markers)
     *
     * @test
     */
    public function it_returns_correct_visual_map_for_audio_types()
    {
        $visualMap = ContentNodeType::getVisualMap(EntityType::AUDIO);
        
        $this->assertEquals('h4', $visualMap['segment']['tag']);
        $this->assertEquals('marker', $visualMap['segment']['behavior']);
        
        $this->assertEquals('h4', $visualMap['track']['tag']);
        $this->assertEquals('marker', $visualMap['track']['behavior']);
        
        $this->assertEquals('h5', $visualMap['marker']['tag']);
        $this->assertEquals('marker', $visualMap['marker']['behavior']);
    }

    /**
     * Test visual mapping for Video types (all markers)
     *
     * @test
     */
    public function it_returns_correct_visual_map_for_video_types()
    {
        $visualMap = ContentNodeType::getVisualMap(EntityType::VIDEO);
        
        $this->assertEquals('h4', $visualMap['scene']['tag']);
        $this->assertEquals('marker', $visualMap['scene']['behavior']);
        
        $this->assertEquals('h5', $visualMap['shot']['tag']);
        $this->assertEquals('marker', $visualMap['shot']['behavior']);
        
        $this->assertEquals('h4', $visualMap['segment']['tag']);
        $this->assertEquals('marker', $visualMap['segment']['behavior']);
    }
}

<?php

namespace Tests\Unit\Models;

use Tests\TestCase;
use App\Models\Manuscript;
use Illuminate\Foundation\Testing\RefreshDatabase;

class ManuscriptTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function manuscript_extends_entity()
    {
        $manuscript = new Manuscript(['title' => 'Test Manuscript']);
        $this->assertInstanceOf(\App\Models\Entity::class, $manuscript);
    }

    /** @test */
    // tests/Unit/Models/ManuscriptTest.php
    /** @test */
    public function manuscript_has_historical_properties()
    {
        $manuscript = Manuscript::create([
            'title' => 'كتاب الطب النبوي',
            'author' => 'ابن قيم الجوزية',
            'century' => 14,
            'language' => 'عربية',
            'pages' => 350
        ]);

        $this->assertEquals('كتاب الطب النبوي', $manuscript->title);
        $this->assertEquals('كتاب-الطب-النبوي', $manuscript->slug); // تغيير التوقع
        $this->assertEquals(14, $manuscript->century);
        $this->assertEquals('عربية', $manuscript->language);
        $this->assertEquals(350, $manuscript->pages);
        $this->assertTrue($manuscript->isAncient());
        $this->assertFalse($manuscript->isModern());
    }

    /** @test */
    public function manuscript_calculates_age_correctly()
    {
        $manuscript = Manuscript::create([
            'title' => 'Old Book',
            'century' => 10 // القرن 10 الميلادي
        ]);

        // العمر التقريبي = السنة الحالية - (900 + 1)
        $expectedAge = date('Y') - 901;
        $this->assertEquals($expectedAge, $manuscript->age);
    }
}

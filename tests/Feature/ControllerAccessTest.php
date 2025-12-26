<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\User;

class ControllerAccessTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
{
    parent::setUp();
    
    // إنشاء مستخدم وتسجيل دخوله إذا كان هناك Middleware يحمي الروابط
    $user = User::factory()->create();
    $this->actingAs($user);

    // تعطيل التحقق من نسخة ملفات JavaScript لتجنب خطأ 409
    \Inertia\Inertia::version(''); 
}

private function assertIndexAccess($prefix)
{
    $entities = [
        'books', 'audios', 'videos', 'manuscripts',
        'categories', 'tags', 'collections', 'series',
        'activities', 'comments', 'notes', 'deletions'
    ];
    foreach ($entities as $entity) {
        $url = $prefix ? "/{$prefix}/{$entity}" : "/{$entity}";
        $response = $this->getJson($url);
        $response->assertStatus(200);
    }
}

    /** @test */
    public function it_can_access_web_index_routes()
    {
        $this->assertIndexAccess(null);
    }

    /** @test */
    public function it_can_access_api_index_routes()
    {
        $this->assertIndexAccess('api');
    }
}

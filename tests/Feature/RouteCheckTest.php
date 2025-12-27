<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Book;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RouteCheckTest extends TestCase
{
    use RefreshDatabase;

    public function test_dashboard_is_accessible()
    {
        $user = User::factory()->create();
        
        $response = $this->actingAs($user)->get('/dashboard');

        $response->assertStatus(200);
    }

    public function test_welcome_is_accessible()
    {
        $response = $this->get('/');

        $response->assertStatus(200);
    }

    public function test_login_is_accessible()
    {
        $response = $this->get('/login');

        $response->assertStatus(200);
    }
}

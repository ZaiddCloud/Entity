<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Booker;
use App\Models\Topic;
use App\Models\Language;
use App\Models\Shelf;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BulkDeletionTest extends TestCase
{
    use RefreshDatabase;

    protected $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
    }

    public function test_can_bulk_delete_bookers(): void
    {
        $bookers = Booker::factory()->count(3)->create();
        $ids = $bookers->pluck('id')->toArray();

        $response = $this->actingAs($this->user)
            ->from(route('bookers.index'))
            ->post(route('bookers.bulk-destroy'), [
                'ids' => $ids
            ]);

        $response->assertRedirect(route('bookers.index'));
        $this->assertEquals(0, Booker::count());
    }

    public function test_can_bulk_delete_topics(): void
    {
        $topics = Topic::factory()->count(3)->create();
        $ids = $topics->pluck('id')->toArray();

        $response = $this->actingAs($this->user)
            ->from(route('topics.index'))
            ->post(route('topics.bulk-destroy'), [
                'ids' => $ids
            ]);

        $response->assertRedirect(route('topics.index'));
        $this->assertEquals(0, Topic::count());
    }

    public function test_can_bulk_delete_languages(): void
    {
        $languages = Language::factory()->count(3)->create();
        $ids = $languages->pluck('id')->toArray();

        $response = $this->actingAs($this->user)
            ->from(route('languages.index'))
            ->post(route('languages.bulk-destroy'), [
                'ids' => $ids
            ]);

        $response->assertRedirect(route('languages.index'));
        $this->assertEquals(0, Language::count());
    }

    public function test_can_bulk_delete_shelves(): void
    {
        $shelves = Shelf::factory()->count(3)->create();
        $ids = $shelves->pluck('id')->toArray();

        $response = $this->actingAs($this->user)
            ->from(route('shelves.index'))
            ->post(route('shelves.bulk-destroy'), [
                'ids' => $ids
            ]);

        $response->assertRedirect(route('shelves.index'));
        $this->assertEquals(0, Shelf::count());
    }
}

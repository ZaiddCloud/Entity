<?php

namespace Tests\Feature;

use App\Models\Publisher;
use App\Models\Series;
use App\Models\Topic;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class StandardControllersTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
        $this->actingAs($this->user);
        Storage::fake('public');
    }

    // ==========================================
    // PUBLISHER TESTS
    // ==========================================

    /** @test */
    public function can_create_publisher()
    {
        $logo = UploadedFile::fake()->image('logo.jpg');
        $data = [
            'name' => 'New Publisher',
            'country_code' => 'US',
            'logo' => $logo,
        ];

        $response = $this->post(route('publishers.store'), $data);

        $response->assertRedirect(route('publishers.index'));
        $this->assertDatabaseHas('publishers', ['name' => 'New Publisher']);
        Storage::disk('public')->assertExists("logos/" . $logo->hashName());
    }

    /** @test */
    public function can_update_publisher()
    {
        $publisher = Publisher::factory()->create();
        $data = ['name' => 'Updated Publisher'];

        $response = $this->put(route('publishers.update', $publisher), $data);

        $publisher->refresh();
        $response->assertRedirect(route('publishers.show', $publisher));
        $this->assertDatabaseHas('publishers', ['id' => $publisher->id, 'name' => 'Updated Publisher']);
    }

    /** @test */
    public function can_delete_publisher()
    {
        $publisher = Publisher::factory()->create();
        $response = $this->delete(route('publishers.destroy', $publisher));
        
        $response->assertRedirect(route('publishers.index'));
        $this->assertSoftDeleted('publishers', ['id' => $publisher->id]);
    }

    // ==========================================
    // SERIES TESTS
    // ==========================================

    /** @test */
    public function can_create_series()
    {
        $data = [
            'title' => 'New Series',
            'description' => 'Test Description',
            'order_column' => 1,
        ];

        $response = $this->post(route('series.store'), $data);

        $response->assertRedirect(route('series.index'));
        $this->assertDatabaseHas('series', ['title' => 'New Series']);
    }

    /** @test */
    public function can_update_series()
    {
        $series = Series::factory()->create();
        $data = ['title' => 'Updated Series'];

        $response = $this->put(route('series.update', $series), $data);

        $series->refresh();
        $response->assertRedirect(route('series.show', $series));
        $this->assertDatabaseHas('series', ['id' => $series->id, 'title' => 'Updated Series']);
    }

    /** @test */
    public function can_delete_series()
    {
        $series = Series::factory()->create();
        $response = $this->delete(route('series.destroy', $series));
        
        $response->assertRedirect(route('series.index'));
        $this->assertDatabaseMissing('series', ['id' => $series->id]);
    }

    // ==========================================
    // TOPIC TESTS
    // ==========================================

    /** @test */
    public function can_create_topic()
    {
        $data = [
            'name' => 'New Topic',
        ];

        $response = $this->post(route('topics.store'), $data);

        $response->assertRedirect(route('topics.index'));
        $this->assertDatabaseHas('topics', ['name' => 'New Topic']);
    }

    /** @test */
    public function can_update_topic()
    {
        $topic = Topic::factory()->create();
        $data = ['name' => 'Updated Topic'];

        $response = $this->put(route('topics.update', $topic), $data);

        $topic->refresh();
        $response->assertRedirect(route('topics.show', $topic));
        $this->assertDatabaseHas('topics', ['id' => $topic->id, 'name' => 'Updated Topic']);
    }

    /** @test */
    public function can_delete_topic()
    {
        $topic = Topic::factory()->create();
        $response = $this->delete(route('topics.destroy', $topic));
        
        $response->assertRedirect(route('topics.index'));
        $this->assertSoftDeleted('topics', ['id' => $topic->id]);
    }

    /** @test */
    public function can_bulk_destroy_topics()
    {
        $topics = Topic::factory()->count(3)->create();
        $ids = $topics->pluck('id')->toArray();

        $response = $this->post(route('topics.bulk-destroy'), ['ids' => $ids]);

        $response->assertRedirect(route('topics.index'));
        foreach ($ids as $id) {
            $this->assertSoftDeleted('topics', ['id' => $id]);
        }
    }
}

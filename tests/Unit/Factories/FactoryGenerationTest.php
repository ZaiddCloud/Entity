<?php

namespace Tests\Unit\Factories;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FactoryGenerationTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_can_generate_media_entities()
    {
        $video = \App\Models\Video::factory()->create();
        $this->assertNotNull($video->id);
        $this->assertNotNull($video->title);
        $this->assertNotNull($video->format);

        $audio = \App\Models\Audio::factory()->create();
        $this->assertNotNull($audio->id);
        $this->assertNotNull($audio->format);
        $this->assertNotNull($audio->bitrate);

        $manuscript = \App\Models\Manuscript::factory()->create();
        $this->assertNotNull($manuscript->id);
        $this->assertNotNull($manuscript->author);
        $this->assertNotNull($manuscript->century);
    }

    /** @test */
    public function it_can_generate_grouping_entities()
    {
        $collection = \App\Models\Collection::factory()->create();
        $this->assertNotNull($collection->id);
        $this->assertNotNull($collection->name);
        $this->assertNotNull($collection->user_id);

        $series = \App\Models\Series::factory()->create();
        $this->assertNotNull($series->id);
        $this->assertNotNull($series->title);
    }

    /** @test */
    public function it_can_generate_polymorphic_relations_and_interactions()
    {
        $tag = \App\Models\Tag::factory()->create();
        $this->assertNotNull($tag->id);
        $this->assertNotNull($tag->slug);

        $category = \App\Models\Category::factory()->create();
        $this->assertNotNull($category->id);

        $activity = \App\Models\Activity::factory()->create();
        $this->assertNotNull($activity->id);
        $this->assertNotNull($activity->entity_id);
        $this->assertNotNull($activity->user_id);

        $comment = \App\Models\Comment::factory()->create();
        $this->assertNotNull($comment->id);
        $this->assertNotNull($comment->entity_id);

        $note = \App\Models\Note::factory()->create();
        $this->assertNotNull($note->id);
        $this->assertNotNull($note->content);

        $deletion = \App\Models\Deletion::factory()->create();
        $this->assertNotNull($deletion->id);
        $this->assertNotNull($deletion->reason);
        $this->assertNotNull($deletion->deleted_at);
    }
}

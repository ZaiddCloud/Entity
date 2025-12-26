<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('tags', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('name');
            $table->string('slug')->unique();
            $table->string('type')->nullable(); // tag, category, keyword, etc.
            $table->timestamps();

            $table->index('slug');
            $table->index('type');
        });

        Schema::create('taggables', function (Blueprint $table) {
            // $table->uuid('id')->primary(); // Pivot tables generally don't need IDs unless using a custom model
            $table->foreignUuid('tag_id');
            $table->nullableUuidMorphs('entity'); // أو nullableMorphs إذا تريد nullable
            $table->timestamps();

            // مؤشرات صحيحة
            $table->index(['entity_id', 'entity_type']);
            $table->index('tag_id');

            // foreign key
            $table->foreign('tag_id')
                ->references('id')
                ->on('tags')
                ->onDelete('cascade');

            $table->unique(['tag_id', 'entity_id', 'entity_type'], 'taggable_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tags');
        Schema::dropIfExists('taggables');
    }
};

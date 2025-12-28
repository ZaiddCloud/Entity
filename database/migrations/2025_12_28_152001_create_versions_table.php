<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('versions', function (Blueprint $table) {
            $table->uuid('id')->primary();

            // Core Relations (Polymorphic)
            $table->uuidMorphs('versionable');
            $table->foreignUuid('publisher_id')->nullable()->references('id')->on('publishers')->onDelete('set null');
            $table->foreignUuid('language_id')->nullable()->references('id')->on('languages')->onDelete('set null');
            $table->foreignUuid('shelf_id')->nullable()->references('id')->on('shelves')->onDelete('set null');

            // File & Meta
            $table->string('file_path')->nullable(); // The core asset
            $table->string('cover_path')->nullable();
            $table->string('format')->default('pdf'); // pdf, epub, mp3, mp4
            $table->bigInteger('file_size')->default(0); // bytes

            // Publication Info
            $table->string('isbn')->nullable();
            $table->integer('pages')->nullable();
            $table->integer('published_year')->nullable();
            $table->integer('edition_number')->default(1);

            $table->timestamps();
            $table->softDeletes();

            // Indexes
            $table->index('isbn');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('versions');
    }
};

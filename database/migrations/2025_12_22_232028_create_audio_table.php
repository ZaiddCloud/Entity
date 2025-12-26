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
        Schema::create('audios', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('title');
            $table->string('slug')->unique();
            $table->integer('duration')->default(0); // بالثانية
            $table->string('format')->default('mp3');
            $table->integer('bitrate')->nullable(); // kbps
            $table->integer('sample_rate')->nullable(); // Hz
            $table->integer('file_size')->nullable(); // بالكيلوبايت
            $table->longText('description')->nullable();
            $table->string('cover_path')->nullable();
            $table->string('file_path')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index('slug');
            $table->index('format');
            $table->index('created_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('audio');
    }
};

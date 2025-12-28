<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('topics', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('name');
            $table->string('slug')->unique();
            $table->uuid('parent_id')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('parent_id')->references('id')->on('topics')->onDelete('cascade');
        });

        Schema::create('book_topic', function (Blueprint $table) {
            $table->foreignUuid('book_id')->references('id')->on('books')->onDelete('cascade');
            $table->foreignUuid('topic_id')->references('id')->on('topics')->onDelete('cascade');
            $table->primary(['book_id', 'topic_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('book_topic');
        Schema::dropIfExists('topics');
    }
};

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
        Schema::create('manuscripts', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('title');
            $table->string('slug')->unique();
            $table->string('author')->nullable();
            $table->integer('century'); // القرن
            $table->string('language')->default('عربية');
            $table->integer('pages')->default(0);
            $table->string('publisher')->nullable();
            $table->string('location')->nullable(); // مكان الحفظ
            $table->longText('description')->nullable();
            $table->string('cover_path')->nullable();
            $table->string('file_path')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index('slug');
            $table->index('author');
            $table->index('century');
            $table->index('language');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('manuscripts');
    }
};

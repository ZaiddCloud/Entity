<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    // database/migrations/xxxx_xx_xx_xxxxxx_create_categories_table.php
    public function up()
    {
        Schema::create('categories', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('name');
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            $table->foreignUuid('parent_id')->nullable();
            $table->softDeletes();
            $table->timestamps();

            // فهرس للعلاقة الذاتية
            $table->foreign('parent_id')->references('id')->on('categories')->onDelete('cascade');
            $table->index('slug');
            $table->index('parent_id');
        });

        // إضافة الرقم التسلسلي باستخدام SQL مباشر لضمان التوافق مع MariaDB عند وجود UUID كـ Primary Key
        DB::statement('ALTER TABLE categories ADD serial_number BIGINT UNSIGNED NOT NULL AUTO_INCREMENT UNIQUE AFTER id');

        Schema::create('categorizables', function (Blueprint $table) {
            // $table->uuid('id')->primary();
            $table->foreignUuid('category_id');
            $table->uuidMorphs('entity');
            $table->timestamps();

            $table->foreign('category_id')->references('id')->on('categories')->onDelete('cascade');
            $table->index(['entity_id', 'entity_type']);
            $table->index('category_id');

            $table->unique(['category_id', 'entity_id', 'entity_type'], 'taggable_unique');

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('categories');
        Schema::dropIfExists('categorizables');
    }
};

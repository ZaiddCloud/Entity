<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('collections', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('name');
            $table->foreignUuid('user_id');
            $table->text('description')->nullable();
            $table->boolean('is_public')->default(false);
            $table->timestamps();
        });

        // إضافة الرقم التسلسلي باستخدام SQL مباشر لضمان التوافق مع MariaDB عند وجود UUID كـ Primary Key
        DB::statement('ALTER TABLE collections ADD serial_number BIGINT UNSIGNED NOT NULL AUTO_INCREMENT UNIQUE AFTER id');

        Schema::create('collectables', function (Blueprint $table) {
            // $table->uuid('id')->primary();
            $table->uuidMorphs('entity');
            $table->foreignUuid('collection_id');
            $table->integer('order_column')->default(0);
            $table->timestamp('added_at')->nullable();

            $table->index(['entity_id', 'entity_type']);
            $table->foreign('collection_id')->references('id')->on('collections')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('collections');
        Schema::dropIfExists('collectables');
    }
};

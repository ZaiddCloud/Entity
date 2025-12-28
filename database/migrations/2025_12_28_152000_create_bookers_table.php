<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('bookers', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('name');
            $table->string('slug')->unique();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('bookables', function (Blueprint $table) {
            $table->foreignUuid('booker_id')->references('id')->on('bookers')->onDelete('cascade');
            $table->uuidMorphs('bookable'); // bookable_id, bookable_type
            $table->string('role')->default('contributor'); // translator, editor, illustrator

            $table->index(['booker_id', 'bookable_type']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bookables');
        Schema::dropIfExists('bookers');
    }
};

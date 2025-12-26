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
        Schema::create('series', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('title');
            $table->text('description')->nullable();
            $table->integer('order_column')->default(0);
            $table->timestamps();
        });

        Schema::create('seriables', function (Blueprint $table) {
            // $table->uuid('id')->primary();
            $table->uuidMorphs('entity');
            $table->foreignUuid('series_id');
            $table->integer('position')->default(0);

            $table->index(['entity_id', 'entity_type']);
            $table->foreign('series_id')->references('id')->on('series')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('series');
    }
};

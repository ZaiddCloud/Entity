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
        Schema::create('assignments', function (Blueprint $table) {
            $table->uuid('id')->primary();
            
            // The Editor
            $table->foreignUuid('user_id')->constrained('users')->cascadeOnDelete();
            
            // The Work (Book, Video, Manuscript...)
            $table->uuidMorphs('entity');
            
            // The Manager
            $table->foreignUuid('assigned_by')->nullable()->constrained('users')->nullOnDelete();
            
            // Management
            $table->string('status')->default('pending')->index(); // pending, in_progress, review, done
            $table->text('notes')->nullable();
            
            // Timestamps
            $table->timestamp('due_at')->nullable();
            $table->timestamp('submitted_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();
            
            // Indexes for performance
            $table->index(['user_id', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('assignments');
    }
};

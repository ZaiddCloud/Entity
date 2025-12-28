<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('manuscripts', function (Blueprint $table) {
            $table->uuid('id')->primary();
            // serial_number column definition removed from here
            $table->string('title');
            $table->string('slug')->unique();
            $table->string('author')->nullable();
            $table->string('century')->nullable(); // Changed from integer to string as per instruction snippet
            $table->string('language')->default('عربية'); // Kept as it was not explicitly removed in the instruction's snippet
            $table->integer('pages')->default(0); // Kept as it was not explicitly removed in the instruction's snippet
            $table->string('publisher')->nullable(); // Kept as it was not explicitly removed in the instruction's snippet
            $table->string('location')->nullable(); // مكان الحفظ // Kept as it was not explicitly removed in the instruction's snippet
            $table->longText('description')->nullable();
            $table->string('cover_path')->nullable();
            $table->string('file_path')->nullable();
            $table->timestamps();
            $table->softDeletes();

            // مؤشرات
            $table->index('slug');
            $table->index('author');
            $table->index('century'); // Kept as it was not explicitly removed in the instruction's snippet
            $table->index('language'); // Kept as it was not explicitly removed in the instruction's snippet
            $table->index('created_at'); // Added as per instruction snippet
        });

        // إضافة الرقم التسلسلي باستخدام SQL مباشر لضمان التوافق مع MariaDB عند وجود UUID كـ Primary Key
        DB::statement('ALTER TABLE manuscripts ADD serial_number BIGINT UNSIGNED NOT NULL AUTO_INCREMENT UNIQUE AFTER id');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('manuscripts');
    }
};

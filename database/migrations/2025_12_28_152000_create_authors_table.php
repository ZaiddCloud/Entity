<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('authors', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('name');
            $table->string('slug')->unique();
            $table->text('bio')->nullable();
            
            // بيانات إضافية (الجديدة)
            $table->string('madhab')->nullable()->index(); // المذهب
            $table->string('original_region')->nullable()->index(); // البلد/الإقليم
            $table->string('century_lived')->nullable()->index(); // قرن الوفاة (نصي: القرن التاسع)

            $table->integer('birth_year')->nullable();
            $table->integer('death_year')->nullable();
            
            $table->timestamps();
            $table->softDeletes();
        });

        // إضافة الرقم التسلسلي
        DB::statement('ALTER TABLE authors ADD serial_number BIGINT UNSIGNED NOT NULL AUTO_INCREMENT UNIQUE AFTER id');

        Schema::create('authorables', function (Blueprint $table) {
            $table->uuidMorphs('authorable');
            $table->foreignUuid('author_id')->references('id')->on('authors')->onDelete('cascade');
            $table->primary(['authorable_id', 'authorable_type', 'author_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('authorables'); // تصحيح الاسم هنا أيضاً ليتطابق مع الـ up
        Schema::dropIfExists('authors');
    }
};
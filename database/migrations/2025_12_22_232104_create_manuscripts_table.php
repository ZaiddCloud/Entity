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
            $table->string('original_title')->nullable(); // العنوان المثبت
            $table->string('code')->nullable()->index(); // كود العمل لربط النسخ
            $table->string('slug')->unique();
            
            // Metadata for Physical Copy
            $table->string('catalog_number')->nullable(); // رقم المخطوط
            $table->string('madhab')->nullable(); // المذهب
            $table->string('scribe')->nullable(); // الناسخ
            $table->string('copy_date')->nullable(); // تاريخ النسخ
            $table->string('parts')->nullable(); // عدد الأجزاء
            $table->string('script_type')->nullable(); // نوع الخط
            $table->string('dimensions')->nullable(); // المقاس
            $table->integer('lines_per_page')->nullable(); // مسطرة الصفحة
            $table->longText('inscriptions')->nullable(); // القيود (تملك، بلاغات...)
            $table->longText('notes')->nullable(); // ملاحظات
            
            $table->string('author')->nullable();
            $table->string('century')->nullable(); // Changed from integer to string as per instruction snippet
            $table->string('century_label')->nullable(); // "9 هـ" or descriptive text
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

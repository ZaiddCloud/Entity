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
        Schema::create('manuscripts', function (Blueprint $table) {
            $table->uuid('id')->primary();
            
            // البيانات الأساسية
            $table->string('title');
            $table->string('slug')->unique();
            $table->string('catalog_number')->nullable(); // رقم المخطوط
            $table->string('parts')->nullable(); // عدد الأجزاء
            $table->boolean('is_autograph')->default(false); // خط المؤلف (يفضل boolean للبحث)
            $table->string('scribe')->nullable(); // الناسخ
            $table->string('copy_date')->nullable(); // تاريخ النسخ
            
            // الوصف والمحتوى
            $table->longText('description')->nullable();
            $table->longText('inscriptions')->nullable(); // القيود (تملك، بلاغات...)
            $table->string('original_title')->nullable(); // العنوان المثبت
            $table->longText('manuscript_start')->nullable(); // بداية المخطوط
            $table->longText('manuscript_end')->nullable(); // نهاية المخطوط
            $table->string('code')->nullable()->index(); // كود العمل لربط النسخ
            $table->longText('notes')->nullable(); // ملاحظات

            // Metadata for Physical Copy
            $table->string('manuscript_century')->nullable(); // قرن النسخ
            $table->string('manuscript_century_label')->nullable(); // "9 هـ" or descriptive text
            $table->string('script_type')->nullable(); // نوع الخط
            $table->string('dimensions')->nullable(); // المقاس
            $table->integer('lines_per_page')->nullable(); // مسطرة الصفحة
            $table->integer('pages')->default(0); 
            $table->string('location')->nullable(); // مكان الحفظ
            
            // الملفات
            $table->string('cover_path')->nullable();
            $table->string('file_path')->nullable();
            
            $table->timestamps();
            $table->softDeletes();

            // مؤشرات هامة للبحث
            $table->index('slug');
            $table->index('created_at');
            $table->index('catalog_number'); // مهم جداً للبحث برقم المخطوط
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
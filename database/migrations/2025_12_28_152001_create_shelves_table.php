<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('shelves', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('location_code')->unique(); // e.g., A-10-3
            $table->integer('capacity')->default(0);
            $table->timestamps();
            $table->softDeletes();
        });

        // إضافة الرقم التسلسلي باستخدام SQL مباشر لضمان التوافق مع MariaDB عند وجود UUID كـ Primary Key
        DB::statement('ALTER TABLE shelves ADD serial_number BIGINT UNSIGNED NOT NULL AUTO_INCREMENT UNIQUE AFTER id');
    }

    public function down(): void
    {
        Schema::dropIfExists('shelves');
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('languages', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('name');
            $table->string('code', 3)->unique(); // ar, en
            $table->timestamps();
        });

        // إضافة الرقم التسلسلي باستخدام SQL مباشر لضمان التوافق مع MariaDB عند وجود UUID كـ Primary Key
        DB::statement('ALTER TABLE languages ADD serial_number BIGINT UNSIGNED NOT NULL AUTO_INCREMENT UNIQUE AFTER id');
    }

    public function down(): void
    {
        Schema::dropIfExists('languages');
    }
};

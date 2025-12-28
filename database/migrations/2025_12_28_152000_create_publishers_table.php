<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('publishers', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('name');
            $table->string('slug')->unique();
            $table->string('country_code', 3)->nullable(); // ISO Alpha-3 or 2
            $table->string('logo_path')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        // إضافة الرقم التسلسلي باستخدام SQL مباشر لضمان التوافق مع MariaDB عند وجود UUID كـ Primary Key
        DB::statement('ALTER TABLE publishers ADD serial_number BIGINT UNSIGNED NOT NULL AUTO_INCREMENT UNIQUE AFTER id');
    }

    public function down(): void
    {
        Schema::dropIfExists('publishers');
    }
};

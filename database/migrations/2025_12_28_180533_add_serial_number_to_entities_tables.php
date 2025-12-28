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
        $tables = ['books', 'videos', 'audios', 'manuscripts'];
        foreach ($tables as $tableName) {
            // we use raw SQL because $table->autoIncrement() in Laravel forces a Primary Key
            // but we already have a UUID as the primary key.
            // This raw SQL works in MySQL/MariaDB and assigns sequential numbers to existing rows.
            DB::statement("ALTER TABLE {$tableName} ADD COLUMN serial_number BIGINT UNSIGNED NOT NULL AUTO_INCREMENT UNIQUE AFTER id");
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $tables = ['books', 'videos', 'audios', 'manuscripts'];
        foreach ($tables as $tableName) {
            Schema::table($tableName, function (Blueprint $table) {
                // In MySQL, dropping an auto-increment column might require dropping the key first
                // But usually dropColumn handles it if it's the only one.
                $table->dropColumn('serial_number');
            });
        }
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Employee category enum'una system_admin ekle
        DB::statement("ALTER TABLE employees MODIFY COLUMN category ENUM('worker', 'foreman', 'engineer', 'manager', 'system_admin') DEFAULT 'worker'");
    }

    public function down(): void
    {
        // Geri alma - system_admin'leri manager'a çevir
        DB::statement("UPDATE employees SET category = 'manager' WHERE category = 'system_admin'");
        DB::statement("ALTER TABLE employees MODIFY COLUMN category ENUM('worker', 'foreman', 'engineer', 'manager') DEFAULT 'worker'");
    }
};
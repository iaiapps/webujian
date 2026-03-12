<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // ============================================================
            // SISTEM KREDIT - GANTI DARI SUBSCRIPTION
            // Kolom credits untuk membuat package
            // ============================================================
            $table->integer('credits')->default(10)->after('max_classes');

            // Kolom plan dan plan_expired_at tidak dihapus untuk backward compatibility
            // Tapi tidak lagi digunakan dalam logika aplikasi
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('credits');
        });
    }
};

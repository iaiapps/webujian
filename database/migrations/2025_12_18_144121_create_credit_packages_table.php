<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('credit_packages', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // contoh: "Paket 10", "Paket Hemat 50"
            $table->integer('credit_amount'); // jumlah kredit
            $table->integer('bonus_credits')->default(0); // bonus tambahan
            $table->decimal('price', 12, 2); // harga
            $table->text('description')->nullable();
            $table->boolean('is_active')->default(true);
            $table->integer('sort_order')->default(0);
            $table->timestamps();

            $table->index('is_active');
            $table->index('sort_order');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('credit_packages');
    }
};

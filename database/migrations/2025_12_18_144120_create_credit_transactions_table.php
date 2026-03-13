<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('credit_transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->enum('type', ['purchase', 'usage', 'bonus', 'manual_add', 'manual_deduct', 'refund']);
            $table->integer('amount'); // positif untuk masuk, negatif untuk keluar
            $table->integer('balance_before');
            $table->integer('balance_after');
            $table->string('description');
            $table->string('reference_id')->nullable(); // ID package jika usage, invoice jika purchase
            $table->string('reference_type')->nullable(); // 'test_package', 'purchase', 'manual'
            $table->foreignId('performed_by')->nullable()->constrained('users')->nullOnDelete(); // admin ID jika manual
            $table->text('notes')->nullable(); // alasan manual adjustment
            $table->timestamps();

            $table->index('user_id');
            $table->index(['type', 'created_at']);
            $table->index('reference_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('credit_transactions');
    }
};

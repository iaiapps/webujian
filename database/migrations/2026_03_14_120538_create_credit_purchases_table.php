<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('credit_purchases', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('credit_package_id')->constrained()->onDelete('cascade');

            // Mayar data
            $table->string('mayar_invoice_id')->unique();
            $table->string('mayar_transaction_id')->nullable();
            $table->string('payment_link')->nullable();

            // Amount & Credits
            $table->decimal('amount', 12, 2);
            $table->integer('credits_amount');
            $table->integer('bonus_credits')->default(0);
            $table->integer('total_credits');

            // Status
            $table->enum('status', ['pending', 'paid', 'expired', 'cancelled'])->default('pending');
            $table->timestamp('expired_at');
            $table->timestamp('paid_at')->nullable();

            // Tracking
            $table->string('internal_ref')->unique();
            $table->string('payment_method')->nullable();
            $table->json('mayar_response')->nullable();

            $table->timestamps();

            $table->index(['user_id', 'status']);
            $table->index('mayar_invoice_id');
            $table->index('internal_ref');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('credit_purchases');
    }
};

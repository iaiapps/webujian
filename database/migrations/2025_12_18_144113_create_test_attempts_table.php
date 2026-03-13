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
        Schema::create('test_attempts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->constrained()->onDelete('cascade');
            $table->foreignId('package_id')->constrained('test_packages')->onDelete('cascade');
            $table->timestamp('start_time');
            $table->timestamp('end_time')->nullable();
            $table->timestamp('submitted_at')->nullable();
            $table->decimal('total_score', 8, 2)->default(0);
            $table->integer('correct_answers')->default(0);
            $table->integer('wrong_answers')->default(0);
            $table->integer('unanswered')->default(0);
            $table->enum('status', ['ongoing', 'completed', 'expired'])->default('ongoing');
            $table->ipAddress('ip_address')->nullable();
            $table->text('user_agent')->nullable();

            // ANTI PELANGGARAN / PROCTORING
            $table->integer('violations_count')->default(0);
            $table->json('violations_log')->nullable(); // Detail setiap pelanggaran
            $table->boolean('is_flagged')->default(false);
            $table->timestamp('flagged_at')->nullable();
            $table->string('reset_token')->nullable();
            $table->timestamp('reset_token_expires_at')->nullable();

            $table->timestamps();

            $table->index('student_id');
            $table->index('package_id');
            $table->index('status');
            $table->index('is_flagged');
            $table->index('reset_token');
            $table->unique(['student_id', 'package_id']); // prevent multiple attempts
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('test_attempts');
    }
};

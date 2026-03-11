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
        Schema::create('test_answers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('attempt_id')->constrained('test_attempts')->onDelete('cascade');
            $table->foreignId('question_id')->constrained()->onDelete('cascade');
            $table->string('answer')->nullable(); // 'A' atau 'A,C,E' untuk kompleks
            $table->boolean('is_correct')->default(false);
            $table->boolean('is_doubt')->default(false); // ragu-ragu
            $table->timestamps();

            $table->index('attempt_id');
            $table->unique(['attempt_id', 'question_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('test_answers');
    }
};

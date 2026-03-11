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
        Schema::create('questions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade'); // guru pemilik soal
            $table->foreignId('category_id')->constrained('question_categories')->onDelete('cascade');
            $table->enum('question_type', ['single', 'complex'])->default('single');
            $table->text('question_text');
            $table->string('question_image')->nullable();
            $table->text('option_a');
            $table->text('option_b');
            $table->text('option_c');
            $table->text('option_d');
            $table->text('option_e');
            $table->string('correct_answer'); // 'A' atau 'A,C,E' untuk kompleks
            $table->text('explanation')->nullable();
            $table->enum('difficulty', ['easy', 'medium', 'hard'])->default('medium');
            $table->integer('usage_count')->default(0); // berapa kali dipakai
            $table->timestamps();
            $table->softDeletes();

            $table->index('user_id');
            $table->index('category_id');
            $table->index(['question_type', 'difficulty']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('questions');
    }
};

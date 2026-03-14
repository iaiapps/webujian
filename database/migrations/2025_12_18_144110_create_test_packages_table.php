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
        Schema::create('test_packages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('title');
            $table->text('description')->nullable();
            $table->integer('duration'); // dalam menit
            $table->timestamp('start_date')->nullable();
            $table->timestamp('end_date')->nullable();
            $table->boolean('show_result')->default(true);
            $table->boolean('show_explanation')->default(true);
            $table->boolean('show_ranking')->default(true);
            $table->boolean('shuffle_questions')->default(false);
            $table->boolean('is_active')->default(true);
            $table->integer('total_questions')->default(0);
            $table->integer('attempt_count')->default(0);
            $table->decimal('score_correct', 5, 2)->default(4);
            $table->decimal('score_wrong', 5, 2)->default(-1);
            $table->decimal('score_empty', 5, 2)->default(0);

            // ANTI PELANGGARAN
            $table->integer('max_violations')->default(3);

            $table->timestamps();
            $table->softDeletes();

            $table->index('user_id');
            $table->index(['is_active', 'start_date', 'end_date']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('test_packages');
    }
};

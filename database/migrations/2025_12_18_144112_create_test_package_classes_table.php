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
        Schema::create('test_package_classes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('package_id')->constrained('test_packages')->onDelete('cascade');
            $table->foreignId('class_id')->constrained()->onDelete('cascade');
            $table->timestamps();

            $table->unique(['package_id', 'class_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('test_package_classes');
    }
};

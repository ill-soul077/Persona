<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * 
     * Creates the income_sources lookup table for categorizing income transactions.
     * Examples: from_home, tuition, freelance, part_time_job, investment, other
     */
    public function up(): void
    {
        Schema::create('income_sources', function (Blueprint $table) {
            $table->id();
            $table->string('name', 100)->comment('Human-readable income source name');
            $table->string('slug', 100)->unique()->comment('URL-friendly identifier');
            $table->text('description')->nullable()->comment('Detailed explanation of income source');
            $table->boolean('is_active')->default(true)->comment('Soft disable without deletion');
            $table->timestamps();

            // Indexes for performance
            $table->index('is_active');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('income_sources');
    }
};

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
        Schema::create('budget_summaries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->date('month'); // YYYY-MM-01 for the month
            $table->json('summary_data'); // Stores AI-generated advice JSON
            $table->string('model_used')->nullable(); // Which Gemini model generated this
            $table->boolean('is_fallback')->default(false); // True if heuristic fallback was used
            $table->timestamps();
            
            // Unique constraint: one summary per user per month
            $table->unique(['user_id', 'month']);
            
            // Index for quick lookups
            $table->index(['user_id', 'month', 'updated_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('budget_summaries');
    }
};

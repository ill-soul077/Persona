<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * 
     * Creates the budgets table for monthly budget tracking.
     * Each user can set one budget per month with optional notes.
     */
    public function up(): void
    {
        Schema::create('budgets', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')
                ->constrained()
                ->onDelete('cascade')
                ->comment('Owner of the budget');
            $table->date('month')->comment('First day of the month (YYYY-MM-01)');
            $table->decimal('amount', 12, 2)->comment('Budget amount for the month');
            $table->char('currency', 3)->default('USD')->comment('ISO 4217 currency code');
            $table->text('notes')->nullable()->comment('Optional budget notes or goals');
            $table->timestamps();

            // Ensure one budget per user per month
            $table->unique(['user_id', 'month']);
            
            // Indexes for queries
            $table->index(['user_id', 'month']); // Fast lookups
            $table->index('month'); // Monthly reports
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('budgets');
    }
};

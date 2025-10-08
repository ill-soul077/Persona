<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * 
     * Creates the transactions table for recording all financial activities.
     * Supports polymorphic relationships to income_sources or expense_categories.
     * Uses DECIMAL(12,2) for precise monetary calculations.
     */
    public function up(): void
    {
        Schema::create('transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')
                ->constrained()
                ->onDelete('cascade')
                ->comment('Owner of the transaction');
            $table->enum('type', ['income', 'expense'])->comment('Transaction direction');
            $table->decimal('amount', 12, 2)->comment('Monetary value with fixed precision');
            $table->char('currency', 3)->default('USD')->comment('ISO 4217 currency code');
            $table->date('date')->comment('Transaction date (not timestamp)');
            
            // Polymorphic relationship to income_sources OR expense_categories
            $table->unsignedBigInteger('category_id')->nullable()->comment('Polymorphic category ID');
            $table->string('category_type', 50)->nullable()->comment('Polymorphic category type');
            
            $table->text('description')->nullable()->comment('Free-text details, e.g., "on burger at McDonald\'s"');
            $table->json('meta')->nullable()->comment('Structured data: vendor, location, tax, tip, etc.');
            $table->timestamps();

            // Indexes for optimal query performance
            $table->index(['user_id', 'date']); // User transaction history
            $table->index('type'); // Filter by income/expense
            $table->index(['category_id', 'category_type']); // Polymorphic queries
            $table->index('date'); // Date range queries
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('transactions');
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * 
     * Creates the expense_categories table with support for nested categories.
     * Parent categories: food, clothing, education, transport, entertainment, health, other
     * Subcategories: fast_food, groceries, fuel, books_supplies, etc.
     */
    public function up(): void
    {
        Schema::create('expense_categories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('parent_id')
                ->nullable()
                ->constrained('expense_categories')
                ->onDelete('cascade')
                ->comment('Self-referential FK for nested categories');
            $table->string('name', 100)->comment('Human-readable category name');
            $table->string('slug', 100)->unique()->comment('URL-friendly identifier');
            $table->text('description')->nullable()->comment('Category details');
            $table->boolean('is_active')->default(true)->comment('Soft disable without deletion');
            $table->timestamps();

            // Indexes for performance
            $table->index('parent_id');
            $table->index('is_active');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('expense_categories');
    }
};

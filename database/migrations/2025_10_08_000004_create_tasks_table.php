<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * 
     * Creates the tasks table for the Daily Task Tracker module.
     * Supports task priority, status tracking, recurrence patterns, and completion timestamps.
     */
    public function up(): void
    {
        Schema::create('tasks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')
                ->constrained()
                ->onDelete('cascade')
                ->comment('Task owner');
            $table->string('title', 255)->comment('Task summary');
            $table->text('description')->nullable()->comment('Detailed task information');
            $table->dateTime('due_date')->nullable()->comment('When task is due');
            $table->enum('status', ['pending', 'in_progress', 'completed', 'cancelled'])
                ->default('pending')
                ->comment('Current task state');
            $table->enum('priority', ['low', 'medium', 'high'])
                ->default('medium')
                ->comment('Task urgency level');
            $table->string('recurrence', 50)
                ->nullable()
                ->comment('Recurrence pattern: daily, weekly, monthly, or cron expression');
            $table->timestamp('completed_at')->nullable()->comment('Completion timestamp');
            $table->timestamps();

            // Indexes for performance
            $table->index(['user_id', 'due_date']); // User's upcoming tasks
            $table->index('status'); // Filter by status
            $table->index('due_date'); // Date-based queries
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tasks');
    }
};

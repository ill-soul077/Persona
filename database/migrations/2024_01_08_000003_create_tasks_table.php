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
        Schema::create('tasks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('title');
            $table->text('description')->nullable();
            $table->dateTime('due_date')->nullable();
            $table->enum('status', ['pending', 'completed'])->default('pending');
            $table->timestamp('completed_at')->nullable();
            
            // Recurrence fields
            $table->enum('recurrence_type', ['none', 'daily', 'weekly', 'monthly'])->default('none');
            $table->integer('recurrence_interval')->default(1); // e.g., every 2 days
            $table->dateTime('recurrence_end_date')->nullable();
            $table->dateTime('next_occurrence')->nullable();
            
            // Priority and tags
            $table->enum('priority', ['low', 'medium', 'high'])->default('medium');
            $table->json('tags')->nullable();
            
            // AI-related fields
            $table->boolean('created_via_ai')->default(false);
            $table->text('ai_raw_input')->nullable();
            
            $table->timestamps();
            $table->softDeletes();
            
            // Indexes
            $table->index(['user_id', 'status']);
            $table->index(['user_id', 'due_date']);
            $table->index('next_occurrence');
        });

        Schema::create('task_history', function (Blueprint $table) {
            $table->id();
            $table->foreignId('task_id')->constrained()->onDelete('cascade');
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('action'); // created, updated, completed, uncompleted, deleted
            $table->text('changes')->nullable(); // JSON of what changed
            $table->timestamps();
            
            $table->index(['task_id', 'created_at']);
        });

        Schema::create('task_reminders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('task_id')->constrained()->onDelete('cascade');
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->dateTime('remind_at');
            $table->boolean('is_sent')->default(false);
            $table->timestamp('sent_at')->nullable();
            $table->timestamps();
            
            $table->index(['user_id', 'is_sent', 'remind_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('task_reminders');
        Schema::dropIfExists('task_history');
        Schema::dropIfExists('tasks');
    }
};

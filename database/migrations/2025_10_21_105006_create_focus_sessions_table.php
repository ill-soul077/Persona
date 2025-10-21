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
        Schema::create('focus_sessions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('task_id')->nullable()->constrained()->nullOnDelete();
            $table->enum('session_type', ['work', 'short_break', 'long_break'])->default('work');
            $table->integer('duration_minutes')->default(25);
            $table->integer('actual_minutes')->nullable();
            $table->timestamp('started_at');
            $table->timestamp('completed_at')->nullable();
            $table->boolean('interrupted')->default(false);
            $table->integer('pomodoro_count')->default(0);
            $table->text('notes')->nullable();
            $table->timestamps();

            // Add indexes for common queries
            $table->index(['user_id', 'created_at']);
            $table->index(['task_id', 'session_type']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('focus_sessions');
    }
};

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
        Schema::create('task_templates', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->text('description')->nullable();
            $table->enum('category', ['work', 'personal', 'health', 'shopping', 'meeting', 'routine', 'other'])->default('other');
            $table->json('tasks'); // Array of task objects with title, description, priority, due_offset
            $table->boolean('is_public')->default(false);
            $table->integer('use_count')->default(0);
            $table->string('icon')->nullable(); // Emoji or icon identifier
            $table->string('color')->nullable(); // Color theme for the template
            $table->timestamps();

            // Indexes
            $table->index(['user_id', 'category']);
            $table->index(['is_public', 'use_count']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('task_templates');
    }
};

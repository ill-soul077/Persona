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
        if (!Schema::hasTable('task_reminders')) {
            Schema::create('task_reminders', function (Blueprint $table) {
                $table->id();
                $table->foreignId('task_id')->constrained()->onDelete('cascade');
                $table->foreignId('user_id')->constrained()->onDelete('cascade');
                $table->dateTime('remind_at');
                $table->enum('type', ['email', 'in_app', 'push'])->default('in_app');
                $table->boolean('sent')->default(false);
                $table->dateTime('sent_at')->nullable();
                $table->timestamps();
                
                $table->index(['task_id', 'remind_at']);
                $table->index(['user_id', 'sent']);
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('task_reminders');
    }
};

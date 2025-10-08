<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * 
     * Creates the ai_logs table for auditing all chatbot interactions.
     * Stores raw user input, parsed results, model metadata, and processing status.
     * Critical for model training, debugging, and compliance.
     */
    public function up(): void
    {
        Schema::create('ai_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')
                ->constrained()
                ->onDelete('cascade')
                ->comment('User who made the request');
            $table->enum('module', ['finance', 'tasks'])->comment('Which chatbot was used');
            $table->text('raw_text')->comment('Original user input (e.g., "spent 15 on burger")');
            $table->json('parsed_json')
                ->nullable()
                ->comment('Structured output from LLM: {type, amount, category, etc.}');
            $table->string('model', 50)
                ->default('gemini')
                ->comment('AI model used for parsing');
            $table->decimal('confidence', 5, 4)
                ->nullable()
                ->comment('Model confidence score (0.0000 to 1.0000)');
            $table->enum('status', ['parsed', 'pending_review', 'failed', 'applied'])
                ->default('pending_review')
                ->comment('Processing workflow state');
            $table->text('error_message')
                ->nullable()
                ->comment('API errors or validation failures');
            $table->string('ip_address', 45)
                ->nullable()
                ->comment('User IP for security audit (IPv6 compatible)');
            $table->timestamps();

            // Indexes for audit queries and analytics
            $table->index(['user_id', 'module', 'created_at']); // User activity timeline
            $table->index('status'); // Find items needing review
            $table->index('created_at'); // Chronological analysis
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ai_logs');
    }
};

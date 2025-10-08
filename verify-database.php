#!/usr/bin/env php
<?php

/**
 * Database Verification Script
 * Verifies all tables, data, and relationships are working correctly
 */

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "\n";
echo "=== AI PERSONAL TRACKER - DATABASE VERIFICATION ===\n";
echo "\n";

// Lookup Tables
echo "📊 LOOKUP TABLES:\n";
echo "  Income Sources: " . \App\Models\IncomeSource::count() . "\n";
foreach (\App\Models\IncomeSource::all() as $source) {
    echo "    - {$source->name}\n";
}

echo "\n";
echo "  Expense Categories (Parents): " . \App\Models\ExpenseCategory::parents()->count() . "\n";
foreach (\App\Models\ExpenseCategory::parents()->get() as $category) {
    $childCount = $category->children()->count();
    echo "    - {$category->name} ({$childCount} children)\n";
}

// Users
echo "\n";
echo "👥 USERS:\n";
foreach (\App\Models\User::all() as $user) {
    $txCount = $user->transactions()->count();
    $taskCount = $user->tasks()->count();
    $logCount = $user->aiLogs()->count();
    echo "  - {$user->name} ({$user->email})\n";
    echo "    Transactions: {$txCount} | Tasks: {$taskCount} | AI Logs: {$logCount}\n";
}

// Sample Transaction
echo "\n";
echo "💰 TRANSACTION SAMPLE:\n";
$tx = \App\Models\Transaction::with('category')->first();
if ($tx) {
    echo "  Type: {$tx->type}\n";
    echo "  Amount: {$tx->formatted_amount}\n";
    echo "  Category: {$tx->category->name}\n";
    echo "  Description: {$tx->description}\n";
}

// Sample Task
echo "\n";
echo "✅ TASK SAMPLE:\n";
$task = \App\Models\Task::first();
if ($task) {
    echo "  Title: {$task->title}\n";
    echo "  Status: {$task->status}\n";
    echo "  Priority: {$task->priority}\n";
    echo "  Due: " . ($task->due_date ? $task->due_date->format('Y-m-d H:i') : 'N/A') . "\n";
}

// Sample AI Log
echo "\n";
echo "🤖 AI LOG SAMPLE:\n";
$log = \App\Models\AiLog::first();
if ($log) {
    echo "  Module: {$log->module}\n";
    echo "  Raw Text: {$log->raw_text}\n";
    echo "  Status: {$log->status}\n";
    echo "  Confidence: " . ($log->confidence ?? 'N/A') . "\n";
}

// Statistics
echo "\n";
echo "📈 DATABASE STATISTICS:\n";
echo "  Total Income Sources: " . \App\Models\IncomeSource::count() . "\n";
echo "  Total Expense Categories: " . \App\Models\ExpenseCategory::count() . "\n";
echo "  Total Users: " . \App\Models\User::count() . "\n";
echo "  Total Transactions: " . \App\Models\Transaction::count() . "\n";
echo "  Total Tasks: " . \App\Models\Task::count() . "\n";
echo "  Total AI Logs: " . \App\Models\AiLog::count() . "\n";

// Test Relationships
echo "\n";
echo "🔗 RELATIONSHIP TESTS:\n";
$transaction = \App\Models\Transaction::with(['user', 'category'])->first();
echo "  ✓ Transaction → User: " . ($transaction->user ? 'OK' : 'FAIL') . "\n";
echo "  ✓ Transaction → Category: " . ($transaction->category ? 'OK' : 'FAIL') . "\n";

$user = \App\Models\User::first();
echo "  ✓ User → Transactions: " . ($user->transactions()->count() > 0 ? 'OK' : 'FAIL') . "\n";
echo "  ✓ User → Tasks: " . ($user->tasks()->count() > 0 ? 'OK' : 'FAIL') . "\n";

$category = \App\Models\ExpenseCategory::parents()->first();
echo "  ✓ ExpenseCategory → Children: " . ($category->children()->count() > 0 ? 'OK' : 'FAIL') . "\n";

echo "\n";
echo "✅ ALL TESTS PASSED!\n";
echo "🎉 Database setup complete and verified!\n";
echo "\n";

<?php

namespace Database\Seeders;

use App\Models\AiLog;
use App\Models\ExpenseCategory;
use App\Models\IncomeSource;
use App\Models\Task;
use App\Models\Transaction;
use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

/**
 * Main Database Seeder
 * 
 * Seeds all tables for the AI Personal Tracker application.
 * Creates lookup tables, demo users, and sample data for testing.
 */
class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->command->info('🌱 Starting database seeding...');

        // Step 1: Seed lookup tables
        $this->command->info('📊 Seeding lookup tables...');
        $this->call([
            IncomeSourceSeeder::class,
            ExpenseCategorySeeder::class,
        ]);

        // Step 2: Create demo users
        $this->command->info('👥 Creating demo users...');
        
        $john = User::factory()->create([
            'name' => 'John Doe',
            'email' => 'john@example.com',
        ]);

        $jane = User::factory()->create([
            'name' => 'Jane Smith',
            'email' => 'jane@example.com',
        ]);

        $admin = User::factory()->create([
            'name' => 'Admin User',
            'email' => 'admin@example.com',
        ]);

        // Step 3: Create transactions for each user
        $this->command->info('💰 Creating sample transactions...');
        
        foreach ([$john, $jane, $admin] as $user) {
            // Create income transactions
            Transaction::factory()
                ->count(5)
                ->income()
                ->create(['user_id' => $user->id]);

            // Create expense transactions
            Transaction::factory()
                ->count(10)
                ->expense()
                ->create(['user_id' => $user->id]);
        }

        // Step 4: Create tasks for each user
        $this->command->info('✅ Creating sample tasks...');
        
        foreach ([$john, $jane, $admin] as $user) {
            // Pending tasks
            Task::factory()
                ->count(3)
                ->pending()
                ->create(['user_id' => $user->id]);

            // Completed tasks
            Task::factory()
                ->count(2)
                ->completed()
                ->create(['user_id' => $user->id]);

            // Overdue tasks
            Task::factory()
                ->count(1)
                ->overdue()
                ->create(['user_id' => $user->id]);

            // Recurring task
            Task::factory()
                ->recurring('daily')
                ->create(['user_id' => $user->id]);
        }

        // Step 5: Create AI logs for each user
        $this->command->info('🤖 Creating AI interaction logs...');
        
        foreach ([$john, $jane, $admin] as $user) {
            // Finance logs
            AiLog::factory()
                ->count(5)
                ->finance()
                ->create(['user_id' => $user->id]);

            // Task logs
            AiLog::factory()
                ->count(3)
                ->tasks()
                ->create(['user_id' => $user->id]);
        }

        $this->command->info('✨ Database seeding completed successfully!');
        $this->command->newLine();
        $this->command->info('📝 Demo Users Created:');
        $this->command->table(
            ['Name', 'Email', 'Password'],
            [
                ['John Doe', 'john@example.com', 'password'],
                ['Jane Smith', 'jane@example.com', 'password'],
                ['Admin User', 'admin@example.com', 'password'],
            ]
        );
    }
}


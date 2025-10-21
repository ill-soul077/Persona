<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\TaskTemplate;
use App\Models\User;

class TaskTemplateSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get first user (or create a default system user)
        $user = User::first();
        
        if (!$user) {
            // Create a system user for public templates
            $user = User::create([
                'name' => 'System',
                'email' => 'system@persona.app',
                'password' => bcrypt('password'),
            ]);
        }

        $templates = [
            [
                'name' => 'Morning Routine',
                'description' => 'Start your day right with this energizing morning routine',
                'category' => 'routine',
                'icon' => '🌅',
                'is_public' => true,
                'tasks' => [
                    ['title' => 'Exercise for 30 minutes', 'description' => 'Any form of physical activity', 'priority' => 'high', 'due_offset' => 0],
                    ['title' => 'Meditation and mindfulness', 'description' => '10-15 minutes of quiet reflection', 'priority' => 'medium', 'due_offset' => 0],
                    ['title' => 'Review today\'s goals - {date}', 'description' => 'Plan the day', 'priority' => 'high', 'due_offset' => 0],
                    ['title' => 'Healthy breakfast', 'description' => 'Fuel your body', 'priority' => 'medium', 'due_offset' => 0],
                ],
            ],
            [
                'name' => 'Meeting Preparation',
                'description' => 'Comprehensive checklist for productive meetings',
                'category' => 'meeting',
                'icon' => '🤝',
                'is_public' => true,
                'tasks' => [
                    ['title' => 'Review meeting agenda', 'description' => 'Understand objectives', 'priority' => 'high', 'due_offset' => 0],
                    ['title' => 'Prepare presentation materials', 'description' => 'Create slides or demos', 'priority' => 'high', 'due_offset' => 0],
                    ['title' => 'Send pre-read materials', 'description' => 'Share documents 24 hours before', 'priority' => 'medium', 'due_offset' => -1],
                    ['title' => 'Test technical setup', 'description' => 'Check video, audio, screen sharing', 'priority' => 'medium', 'due_offset' => 0],
                ],
            ],
            [
                'name' => 'Weekly Review',
                'description' => 'Reflect on the week and plan ahead',
                'category' => 'routine',
                'icon' => '📊',
                'is_public' => true,
                'tasks' => [
                    ['title' => 'Clear email inbox', 'description' => 'Process all emails', 'priority' => 'high', 'due_offset' => 0],
                    ['title' => 'Review goals and KRs', 'description' => 'Check weekly progress', 'priority' => 'high', 'due_offset' => 0],
                    ['title' => 'Plan next week\'s priorities', 'description' => 'Block calendar time', 'priority' => 'high', 'due_offset' => 0],
                    ['title' => 'Clean workspace', 'description' => 'Organize files', 'priority' => 'low', 'due_offset' => 0],
                ],
            ],
            [
                'name' => 'Shopping List - Groceries',
                'description' => 'Weekly grocery shopping essentials',
                'category' => 'shopping',
                'icon' => '🛒',
                'is_public' => true,
                'tasks' => [
                    ['title' => 'Fresh produce', 'description' => 'Fruits & vegetables', 'priority' => 'high', 'due_offset' => 0],
                    ['title' => 'Proteins', 'description' => 'Meat, fish, eggs', 'priority' => 'high', 'due_offset' => 0],
                    ['title' => 'Dairy products', 'description' => 'Milk, cheese, yogurt', 'priority' => 'medium', 'due_offset' => 0],
                    ['title' => 'Pantry staples', 'description' => 'Rice, pasta, canned goods', 'priority' => 'low', 'due_offset' => 0],
                    ['title' => 'Household items', 'description' => 'Cleaning supplies', 'priority' => 'low', 'due_offset' => 0],
                ],
            ],
        ];

        foreach ($templates as $templateData) {
            TaskTemplate::create(array_merge($templateData, [
                'user_id' => $user->id,
            ]));
        }

        $this->command->info('Task templates seeded successfully!');
    }
}

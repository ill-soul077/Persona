<?php

namespace Database\Factories;

use App\Models\AiLog;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\AiLog>
 */
class AiLogFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = AiLog::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $module = fake()->randomElement(['finance', 'tasks']);
        
        if ($module === 'finance') {
            $rawTexts = [
                'spent 15 on burger at McDonald\'s',
                'received 500 from home',
                'paid 45 for textbook',
                'coffee 5 dollars downtown',
                'earned 200 from freelance project',
            ];
            
            $parsedJson = [
                'type' => fake()->randomElement(['income', 'expense']),
                'amount' => fake()->randomFloat(2, 5, 500),
                'category' => fake()->randomElement(['food', 'education', 'from_home', 'freelance']),
                'description' => fake()->sentence(),
                'meta' => [
                    'vendor' => fake()->company(),
                    'location' => fake()->city(),
                ],
            ];
        } else {
            $rawTexts = [
                'remind me to call mom tomorrow at 3pm',
                'add task study for exam next week',
                'grocery shopping this weekend',
                'pay bills by friday',
                'workout daily at 7am',
            ];
            
            $parsedJson = [
                'title' => fake()->sentence(3),
                'description' => fake()->sentence(),
                'due_date' => fake()->dateTimeBetween('now', '+7 days')->format('Y-m-d H:i:s'),
                'priority' => fake()->randomElement(['low', 'medium', 'high']),
            ];
        }

        return [
            'user_id' => User::factory(),
            'module' => $module,
            'raw_text' => fake()->randomElement($rawTexts),
            'parsed_json' => $parsedJson,
            'model' => 'gemini',
            'confidence' => fake()->randomFloat(4, 0.7, 1.0),
            'status' => fake()->randomElement(['parsed', 'pending_review', 'failed', 'applied']),
            'error_message' => null,
            'ip_address' => fake()->ipv4(),
        ];
    }

    /**
     * Indicate that the log is for finance module.
     */
    public function finance(): static
    {
        return $this->state(fn (array $attributes) => [
            'module' => 'finance',
        ]);
    }

    /**
     * Indicate that the log is for tasks module.
     */
    public function tasks(): static
    {
        return $this->state(fn (array $attributes) => [
            'module' => 'tasks',
        ]);
    }

    /**
     * Indicate that the log parsing failed.
     */
    public function failed(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'failed',
            'error_message' => fake()->sentence(),
            'parsed_json' => null,
        ]);
    }
}

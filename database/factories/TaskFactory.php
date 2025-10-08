<?php

namespace Database\Factories;

use App\Models\Task;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Task>
 */
class TaskFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Task::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $status = fake()->randomElement(['pending', 'in_progress', 'completed', 'cancelled']);
        
        $titles = [
            'Submit assignment',
            'Grocery shopping',
            'Pay electricity bill',
            'Call mom',
            'Study for exam',
            'Workout session',
            'Team meeting',
            'Doctor appointment',
            'Car maintenance',
            'Clean apartment',
        ];

        return [
            'user_id' => User::factory(),
            'title' => fake()->randomElement($titles),
            'description' => fake()->sentence(),
            'due_date' => fake()->dateTimeBetween('now', '+30 days'),
            'status' => $status,
            'priority' => fake()->randomElement(['low', 'medium', 'high']),
            'recurrence' => fake()->boolean(20) ? fake()->randomElement(['daily', 'weekly', 'monthly']) : null,
            'completed_at' => $status === 'completed' ? fake()->dateTimeBetween('-7 days', 'now') : null,
        ];
    }

    /**
     * Indicate that the task is pending.
     */
    public function pending(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'pending',
            'completed_at' => null,
        ]);
    }

    /**
     * Indicate that the task is completed.
     */
    public function completed(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'completed',
            'completed_at' => fake()->dateTimeBetween('-7 days', 'now'),
        ]);
    }

    /**
     * Indicate that the task is high priority.
     */
    public function highPriority(): static
    {
        return $this->state(fn (array $attributes) => [
            'priority' => 'high',
        ]);
    }

    /**
     * Indicate that the task is recurring.
     */
    public function recurring(?string $pattern = null): static
    {
        return $this->state(fn (array $attributes) => [
            'recurrence' => $pattern ?? fake()->randomElement(['daily', 'weekly', 'monthly']),
        ]);
    }

    /**
     * Indicate that the task is overdue.
     */
    public function overdue(): static
    {
        return $this->state(fn (array $attributes) => [
            'due_date' => fake()->dateTimeBetween('-7 days', '-1 day'),
            'status' => 'pending',
            'completed_at' => null,
        ]);
    }
}

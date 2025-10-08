<?php

namespace Database\Factories;

use App\Models\ExpenseCategory;
use App\Models\IncomeSource;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Transaction>
 */
class TransactionFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Transaction::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $type = fake()->randomElement(['income', 'expense']);
        
        // Set category based on type
        if ($type === 'income') {
            $category = IncomeSource::inRandomOrder()->first();
            $categoryType = IncomeSource::class;
            $descriptions = [
                'Monthly allowance',
                'Freelance payment',
                'Part-time salary',
                'Tuition refund',
                'Investment dividend',
            ];
        } else {
            $category = ExpenseCategory::inRandomOrder()->first();
            $categoryType = ExpenseCategory::class;
            $descriptions = [
                'Lunch at restaurant',
                'Coffee at Starbucks',
                'Textbook purchase',
                'Bus fare',
                'Movie tickets',
                'Grocery shopping',
                'Gas for car',
            ];
        }

        $vendors = ['Starbucks', 'McDonald\'s', 'Amazon', 'Walmart', 'Target', 'Shell', 'Local Store'];
        $locations = ['Downtown', 'Main Street', 'Shopping Mall', 'University Area', 'Online'];

        return [
            'user_id' => User::factory(),
            'type' => $type,
            'amount' => fake()->randomFloat(2, 5, 500),
            'currency' => 'USD',
            'date' => fake()->dateTimeBetween('-30 days', 'now'),
            'category_id' => $category?->id,
            'category_type' => $categoryType,
            'description' => fake()->randomElement($descriptions),
            'meta' => fake()->boolean(60) ? [
                'vendor' => fake()->randomElement($vendors),
                'location' => fake()->randomElement($locations),
                'tax' => fake()->randomFloat(2, 0, 10),
            ] : null,
        ];
    }

    /**
     * Indicate that the transaction is income.
     */
    public function income(): static
    {
        return $this->state(function (array $attributes) {
            $category = IncomeSource::inRandomOrder()->first();
            
            return [
                'type' => 'income',
                'category_id' => $category?->id,
                'category_type' => IncomeSource::class,
                'description' => fake()->randomElement([
                    'Monthly allowance from home',
                    'Freelance project payment',
                    'Part-time job salary',
                    'Tuition refund',
                ]),
            ];
        });
    }

    /**
     * Indicate that the transaction is expense.
     */
    public function expense(): static
    {
        return $this->state(function (array $attributes) {
            $category = ExpenseCategory::inRandomOrder()->first();
            
            return [
                'type' => 'expense',
                'category_id' => $category?->id,
                'category_type' => ExpenseCategory::class,
                'description' => fake()->randomElement([
                    'Coffee and snacks',
                    'Lunch with friends',
                    'Course materials',
                    'Transportation',
                ]),
            ];
        });
    }
}

<?php

namespace Database\Seeders;

use App\Models\ExpenseCategory;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

/**
 * Expense Category Seeder
 * 
 * Seeds the expense_categories table with hierarchical categories.
 * Creates parent categories (food, clothing, etc.) and their subcategories.
 */
class ExpenseCategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Parent Categories
        $food = ExpenseCategory::create([
            'name' => 'Food & Dining',
            'slug' => 'food',
            'description' => 'All food and dining related expenses',
            'is_active' => true,
        ]);

        $clothing = ExpenseCategory::create([
            'name' => 'Clothing & Accessories',
            'slug' => 'clothing',
            'description' => 'Apparel, shoes, accessories, and fashion items',
            'is_active' => true,
        ]);

        $education = ExpenseCategory::create([
            'name' => 'Education',
            'slug' => 'education',
            'description' => 'Educational expenses including tuition, books, and supplies',
            'is_active' => true,
        ]);

        $transport = ExpenseCategory::create([
            'name' => 'Transportation',
            'slug' => 'transport',
            'description' => 'Travel and transportation costs',
            'is_active' => true,
        ]);

        $entertainment = ExpenseCategory::create([
            'name' => 'Entertainment',
            'slug' => 'entertainment',
            'description' => 'Leisure, hobbies, and entertainment activities',
            'is_active' => true,
        ]);

        $health = ExpenseCategory::create([
            'name' => 'Health & Wellness',
            'slug' => 'health',
            'description' => 'Healthcare, fitness, and wellness expenses',
            'is_active' => true,
        ]);

        $other = ExpenseCategory::create([
            'name' => 'Other Expenses',
            'slug' => 'other',
            'description' => 'Miscellaneous expenses not categorized elsewhere',
            'is_active' => true,
        ]);

        // Subcategories - Food & Dining
        ExpenseCategory::create([
            'parent_id' => $food->id,
            'name' => 'Fast Food',
            'slug' => 'fast_food',
            'description' => 'Quick service restaurants and takeout',
            'is_active' => true,
        ]);

        ExpenseCategory::create([
            'parent_id' => $food->id,
            'name' => 'Groceries',
            'slug' => 'groceries',
            'description' => 'Supermarket and grocery store purchases',
            'is_active' => true,
        ]);

        ExpenseCategory::create([
            'parent_id' => $food->id,
            'name' => 'Dining Out',
            'slug' => 'dining_out',
            'description' => 'Restaurants, cafes, and fine dining',
            'is_active' => true,
        ]);

        ExpenseCategory::create([
            'parent_id' => $food->id,
            'name' => 'Coffee & Snacks',
            'slug' => 'coffee_snacks',
            'description' => 'Coffee shops, snacks, and light refreshments',
            'is_active' => true,
        ]);

        // Subcategories - Transportation
        ExpenseCategory::create([
            'parent_id' => $transport->id,
            'name' => 'Fuel',
            'slug' => 'fuel',
            'description' => 'Gasoline and vehicle fuel',
            'is_active' => true,
        ]);

        ExpenseCategory::create([
            'parent_id' => $transport->id,
            'name' => 'Public Transit',
            'slug' => 'public_transit',
            'description' => 'Bus, train, subway fares',
            'is_active' => true,
        ]);

        ExpenseCategory::create([
            'parent_id' => $transport->id,
            'name' => 'Ride Sharing',
            'slug' => 'ride_sharing',
            'description' => 'Uber, Lyft, taxi services',
            'is_active' => true,
        ]);

        ExpenseCategory::create([
            'parent_id' => $transport->id,
            'name' => 'Vehicle Maintenance',
            'slug' => 'vehicle_maintenance',
            'description' => 'Car repairs, oil changes, servicing',
            'is_active' => true,
        ]);

        // Subcategories - Education
        ExpenseCategory::create([
            'parent_id' => $education->id,
            'name' => 'Books & Supplies',
            'slug' => 'books_supplies',
            'description' => 'Textbooks, stationery, and school supplies',
            'is_active' => true,
        ]);

        ExpenseCategory::create([
            'parent_id' => $education->id,
            'name' => 'Tuition Fees',
            'slug' => 'tuition_fees',
            'description' => 'Course fees and tuition payments',
            'is_active' => true,
        ]);

        ExpenseCategory::create([
            'parent_id' => $education->id,
            'name' => 'Online Courses',
            'slug' => 'online_courses',
            'description' => 'Udemy, Coursera, and other online learning platforms',
            'is_active' => true,
        ]);

        // Subcategories - Entertainment
        ExpenseCategory::create([
            'parent_id' => $entertainment->id,
            'name' => 'Movies & Shows',
            'slug' => 'movies_shows',
            'description' => 'Cinema tickets, streaming services',
            'is_active' => true,
        ]);

        ExpenseCategory::create([
            'parent_id' => $entertainment->id,
            'name' => 'Gaming',
            'slug' => 'gaming',
            'description' => 'Video games, gaming subscriptions',
            'is_active' => true,
        ]);

        ExpenseCategory::create([
            'parent_id' => $entertainment->id,
            'name' => 'Sports & Hobbies',
            'slug' => 'sports_hobbies',
            'description' => 'Sports equipment, hobby materials',
            'is_active' => true,
        ]);

        // Subcategories - Health & Wellness
        ExpenseCategory::create([
            'parent_id' => $health->id,
            'name' => 'Medical',
            'slug' => 'medical',
            'description' => 'Doctor visits, prescriptions, medical care',
            'is_active' => true,
        ]);

        ExpenseCategory::create([
            'parent_id' => $health->id,
            'name' => 'Fitness',
            'slug' => 'fitness',
            'description' => 'Gym memberships, fitness classes',
            'is_active' => true,
        ]);

        // Subcategories - Clothing
        ExpenseCategory::create([
            'parent_id' => $clothing->id,
            'name' => 'Apparel',
            'slug' => 'apparel',
            'description' => 'Clothes, shoes, and fashion items',
            'is_active' => true,
        ]);

        ExpenseCategory::create([
            'parent_id' => $clothing->id,
            'name' => 'Accessories',
            'slug' => 'accessories',
            'description' => 'Bags, jewelry, watches, and accessories',
            'is_active' => true,
        ]);

        $this->command->info('Expense categories seeded successfully!');
    }
}

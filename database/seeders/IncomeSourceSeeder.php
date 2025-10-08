<?php

namespace Database\Seeders;

use App\Models\IncomeSource;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

/**
 * Income Source Seeder
 * 
 * Seeds the income_sources lookup table with predefined categories.
 * These are used to categorize income transactions in the Finance Tracker module.
 */
class IncomeSourceSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $incomeSources = [
            [
                'name' => 'From Home',
                'slug' => 'from_home',
                'description' => 'Money received from family or home (allowance, financial support)',
                'is_active' => true,
            ],
            [
                'name' => 'Tuition Refund',
                'slug' => 'tuition',
                'description' => 'Scholarship, grants, or tuition-related income',
                'is_active' => true,
            ],
            [
                'name' => 'Freelance Work',
                'slug' => 'freelance',
                'description' => 'Income from freelance projects, gigs, or contract work',
                'is_active' => true,
            ],
            [
                'name' => 'Part-time Job',
                'slug' => 'part_time_job',
                'description' => 'Regular part-time employment income',
                'is_active' => true,
            ],
            [
                'name' => 'Investment Returns',
                'slug' => 'investment',
                'description' => 'Stock dividends, interest, capital gains',
                'is_active' => true,
            ],
            [
                'name' => 'Gift',
                'slug' => 'gift',
                'description' => 'Monetary gifts from friends, family, or others',
                'is_active' => true,
            ],
            [
                'name' => 'Other Income',
                'slug' => 'other',
                'description' => 'Miscellaneous income sources not categorized above',
                'is_active' => true,
            ],
        ];

        foreach ($incomeSources as $source) {
            IncomeSource::create($source);
        }

        $this->command->info('Income sources seeded successfully!');
    }
}

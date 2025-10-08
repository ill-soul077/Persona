<?php

namespace Tests\Feature;

use App\Models\ExpenseCategory;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Expense Category Tests
 * 
 * Tests hierarchical category structure and relationships.
 */
class ExpenseCategoryTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(\Database\Seeders\ExpenseCategorySeeder::class);
    }

    /**
     * Test retrieving nested categories
     */
    public function test_retrieves_nested_categories(): void
    {
        $food = ExpenseCategory::where('slug', 'food')->first();
        
        $this->assertNotNull($food);
        $this->assertTrue($food->isParent());
        $this->assertFalse($food->isChild());
        
        // Should have children
        $this->assertGreaterThan(0, $food->children()->count());
    }

    /**
     * Test parent-child relationship
     */
    public function test_parent_child_relationship(): void
    {
        $fastFood = ExpenseCategory::where('slug', 'fast_food')->first();
        
        $this->assertNotNull($fastFood);
        $this->assertTrue($fastFood->isChild());
        $this->assertFalse($fastFood->isParent());
        
        // Should have a parent
        $this->assertNotNull($fastFood->parent);
        $this->assertEquals('food', $fastFood->parent->slug);
    }

    /**
     * Test querying parent categories
     */
    public function test_queries_parent_categories_only(): void
    {
        $parents = ExpenseCategory::parents()->get();
        
        $this->assertGreaterThan(0, $parents->count());
        
        foreach ($parents as $category) {
            $this->assertNull($category->parent_id);
        }
    }

    /**
     * Test active categories scope
     */
    public function test_filters_active_categories(): void
    {
        // Create an inactive category
        ExpenseCategory::factory()->inactive()->create();
        
        $activeCount = ExpenseCategory::active()->count();
        $totalCount = ExpenseCategory::count();
        
        $this->assertLessThan($totalCount, $activeCount);
    }

    /**
     * Test cascade deletion of children
     */
    public function test_deletes_children_when_parent_deleted(): void
    {
        $parent = ExpenseCategory::factory()->create();
        $child1 = ExpenseCategory::factory()->create(['parent_id' => $parent->id]);
        $child2 = ExpenseCategory::factory()->create(['parent_id' => $parent->id]);
        
        $childrenCount = $parent->children()->count();
        $this->assertEquals(2, $childrenCount);
        
        // Delete parent
        $parent->delete();
        
        // Children should also be deleted
        $this->assertDatabaseMissing('expense_categories', ['id' => $child1->id]);
        $this->assertDatabaseMissing('expense_categories', ['id' => $child2->id]);
    }
}

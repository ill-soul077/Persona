# Testing & Quality Assurance Guide
## AI Personal Tracker - Finance Module

**Last Updated:** October 8, 2025  
**Version:** 1.0.0

---

## Table of Contents
1. [Testing Strategy Overview](#testing-strategy-overview)
2. [Unit Tests](#unit-tests)
3. [Feature Tests](#feature-tests)
4. [Integration Tests](#integration-tests)
5. [UI/UX Acceptance Tests](#uiux-acceptance-tests)
6. [Sample Test Phrases](#sample-test-phrases)
7. [Chart Data Validation](#chart-data-validation)
8. [Manual Testing Checklist](#manual-testing-checklist)

---

## Testing Strategy Overview

### Test Pyramid
```
        /\
       /  \    E2E Tests (5%)
      /    \   - Browser automation
     /------\  Integration Tests (15%)
    /        \ - API endpoints, chat flow
   /----------\ Feature Tests (30%)
  /            \ - Controller actions, policies
 /--------------\ Unit Tests (50%)
                  - Models, services, parsing
```

### Testing Environments
- **Local Development:** SQLite database
- **CI/CD:** GitHub Actions with MySQL
- **Staging:** XAMPP MySQL mirror
- **Production:** MySQL with real Gemini API

---

## Unit Tests

### 1. GeminiService Parsing Tests

**File:** `tests/Unit/GeminiServiceParsingTest.php`

#### Test Categories

##### A. Basic Income Parsing
```php
Sample Phrases with Expected Outputs:
-------------------------------------

Input: "I received 50000 taka as salary"
Expected Output:
{
  "type": "income",
  "amount": 50000.00,
  "currency": "BDT",
  "category": "salary",
  "category_id": <salary_income_source_id>,
  "date": "2025-10-08",
  "confidence": 0.95,
  "vendor": null,
  "description": "Salary income"
}

Input: "Got paid $1500 for freelance work"
Expected Output:
{
  "type": "income",
  "amount": 1500.00,
  "currency": "USD",
  "category": "freelance",
  "category_id": <freelance_income_source_id>,
  "date": "2025-10-08",
  "confidence": 0.90,
  "description": "Freelance work payment"
}

Input: "Bonus of 25000 BDT received today"
Expected Output:
{
  "type": "income",
  "amount": 25000.00,
  "currency": "BDT",
  "category": "bonus",
  "date": "2025-10-08",
  "confidence": 0.88
}
```

##### B. Basic Expense Parsing
```php
Sample Phrases with Expected Outputs:
-------------------------------------

Input: "Spent 500 taka on groceries at Agora"
Expected Output:
{
  "type": "expense",
  "amount": 500.00,
  "currency": "BDT",
  "category": "groceries",
  "category_id": <groceries_expense_category_id>,
  "vendor": "Agora",
  "date": "2025-10-08",
  "confidence": 0.92
}

Input: "Paid 1200 for electricity bill"
Expected Output:
{
  "type": "expense",
  "amount": 1200.00,
  "currency": "BDT",
  "category": "utilities",
  "subcategory": "electricity",
  "date": "2025-10-08",
  "confidence": 0.85
}

Input: "Movie tickets cost $15"
Expected Output:
{
  "type": "expense",
  "amount": 15.00,
  "currency": "USD",
  "category": "entertainment",
  "subcategory": "movies",
  "confidence": 0.88
}
```

##### C. Complex Parsing (Multiple Entities)
```php
Input: "Yesterday I bought groceries for 2500 taka at Shwapno and paid 800 for transport"
Expected Output: [
  {
    "type": "expense",
    "amount": 2500.00,
    "currency": "BDT",
    "category": "groceries",
    "vendor": "Shwapno",
    "date": "2025-10-07",
    "confidence": 0.90
  },
  {
    "type": "expense",
    "amount": 800.00,
    "currency": "BDT",
    "category": "transport",
    "date": "2025-10-07",
    "confidence": 0.87
  }
]
```

##### D. Date Parsing Edge Cases
```php
Relative Dates:
---------------
"yesterday" → 2025-10-07
"today" → 2025-10-08
"last week" → 2025-10-01
"3 days ago" → 2025-10-05
"on Monday" → Most recent Monday
"October 1st" → 2025-10-01

Absolute Dates:
---------------
"on 05/10/2025" → 2025-10-05
"2025-10-03" → 2025-10-03
```

##### E. Currency Detection
```php
BDT Indicators:
---------------
"500 taka", "৳500", "500 BDT", "Tk 500"
All → currency: "BDT"

USD Indicators:
---------------
"$50", "50 dollars", "50 USD"
All → currency: "USD"

Default (ambiguous):
--------------------
"spent 500" → currency: "BDT" (default)
```

##### F. Category Slug Mapping
```php
Expense Categories:
-------------------
"food" → "groceries" or "dining-out"
"electricity bill" → "utilities" → "electricity"
"uber ride" → "transport" → "ride-sharing"
"netflix" → "entertainment" → "subscriptions"
"doctor visit" → "healthcare" → "medical"

Income Categories:
------------------
"salary" → "salary"
"freelance project" → "freelance"
"sold item" → "sales"
"dividend" → "investment-income"
```

##### G. Low Confidence Scenarios
```php
Ambiguous Inputs (Expected confidence < 0.6):
----------------------------------------------
"spent some money" → FAIL (no amount)
"bought stuff" → FAIL (no amount, vague category)
"500" → FAIL (no context: income or expense?)
"paid John" → FAIL (missing amount)

Should Trigger Fallback Rules or Clarification Request
```

##### H. Fallback Rule Testing
```php
Regex Patterns to Test:
-----------------------

Amount Extraction:
- "500.50" → 500.50
- "1,234.56" → 1234.56
- "৳500" → 500
- "$20" → 20

Type Detection:
- Keywords: "spent", "paid", "bought" → expense
- Keywords: "received", "earned", "got paid" → income

Category Keywords:
- "grocery", "groceries", "supermarket" → groceries
- "rent", "house rent" → rent
- "taxi", "uber", "transport" → transport
```

---

## Feature Tests

### 2. Chat Flow Integration Tests

**File:** `tests/Feature/ChatFlowTest.php`

#### Test Scenarios

##### Test 1: Complete Chat-to-Database Flow
```php
/**
 * Test: User sends message → Parse → Preview → Confirm → DB Insert
 */
public function test_complete_chat_flow_creates_transaction()
{
    // Setup
    $user = User::factory()->create();
    $groceryCategory = ExpenseCategory::factory()->create(['slug' => 'groceries']);
    
    // Step 1: Send chat message
    $response = $this->actingAs($user)
        ->postJson('/api/chat/parse-finance', [
            'text' => 'I spent 500 taka on groceries at Agora'
        ]);
    
    // Step 2: Verify parsing response
    $response->assertStatus(200)
        ->assertJson([
            'success' => true,
            'data' => [
                'type' => 'expense',
                'amount' => 500.00,
                'currency' => 'BDT',
                'category' => 'groceries'
            ]
        ]);
    
    $parsedData = $response->json('data');
    
    // Step 3: Confirm transaction
    $confirmResponse = $this->actingAs($user)
        ->postJson('/api/chat/confirm-transaction', $parsedData);
    
    // Step 4: Verify transaction saved to database
    $confirmResponse->assertStatus(200)
        ->assertJson(['success' => true]);
    
    $this->assertDatabaseHas('transactions', [
        'user_id' => $user->id,
        'type' => 'expense',
        'amount' => 500.00,
        'currency' => 'BDT',
        'category_type' => ExpenseCategory::class,
        'category_id' => $groceryCategory->id
    ]);
    
    // Step 5: Verify AI log created
    $this->assertDatabaseHas('ai_logs', [
        'user_id' => $user->id,
        'action' => 'parse_finance',
        'status' => 'success'
    ]);
}
```

##### Test 2: Low Confidence Warning
```php
public function test_low_confidence_parsing_shows_warning()
{
    $user = User::factory()->create();
    
    $response = $this->actingAs($user)
        ->postJson('/api/chat/parse-finance', [
            'text' => 'bought stuff yesterday' // Ambiguous
        ]);
    
    $response->assertStatus(200);
    $data = $response->json('data');
    
    $this->assertLessThan(0.6, $data['confidence']);
    $this->assertTrue($data['requires_confirmation']);
}
```

##### Test 3: Gemini API Failure Fallback
```php
public function test_fallback_rules_when_gemini_fails()
{
    // Mock Gemini API failure
    Http::fake([
        'generativelanguage.googleapis.com/*' => Http::response([], 500)
    ]);
    
    $user = User::factory()->create();
    
    $response = $this->actingAs($user)
        ->postJson('/api/chat/parse-finance', [
            'text' => 'Spent 500 BDT on groceries'
        ]);
    
    // Should still parse using regex fallback
    $response->assertStatus(200)
        ->assertJson([
            'success' => true,
            'data' => [
                'amount' => 500.00,
                'currency' => 'BDT',
                'type' => 'expense'
            ],
            'fallback_used' => true
        ]);
}
```

##### Test 4: Multi-Transaction Parsing
```php
public function test_parse_multiple_transactions_from_single_message()
{
    $user = User::factory()->create();
    
    $response = $this->actingAs($user)
        ->postJson('/api/chat/parse-finance', [
            'text' => 'Yesterday bought groceries for 2500 taka and paid 800 for transport'
        ]);
    
    $response->assertStatus(200);
    $data = $response->json('data');
    
    // Should return array of transactions
    $this->assertIsArray($data);
    $this->assertCount(2, $data);
    
    $this->assertEquals(2500.00, $data[0]['amount']);
    $this->assertEquals('groceries', $data[0]['category']);
    
    $this->assertEquals(800.00, $data[1]['amount']);
    $this->assertEquals('transport', $data[1]['category']);
}
```

##### Test 5: Authorization Check
```php
public function test_user_cannot_edit_others_transactions()
{
    $user1 = User::factory()->create();
    $user2 = User::factory()->create();
    
    $transaction = Transaction::factory()->create([
        'user_id' => $user1->id
    ]);
    
    $response = $this->actingAs($user2)
        ->putJson("/finance/transactions/{$transaction->id}", [
            'amount' => 999.99
        ]);
    
    $response->assertStatus(403); // Forbidden
    
    // Verify original amount unchanged
    $this->assertDatabaseHas('transactions', [
        'id' => $transaction->id,
        'amount' => $transaction->amount // Original amount
    ]);
}
```

---

### 3. Controller Tests

**File:** `tests/Feature/TransactionControllerTest.php`

##### Test 6: Dashboard Data Aggregation
```php
public function test_dashboard_calculates_correct_totals()
{
    $user = User::factory()->create();
    
    // Create test transactions
    Transaction::factory()->count(3)->create([
        'user_id' => $user->id,
        'type' => 'income',
        'amount' => 10000.00,
        'currency' => 'BDT'
    ]); // Total: 30000
    
    Transaction::factory()->count(2)->create([
        'user_id' => $user->id,
        'type' => 'expense',
        'amount' => 5000.00,
        'currency' => 'BDT'
    ]); // Total: 10000
    
    $response = $this->actingAs($user)
        ->get('/finance/dashboard');
    
    $response->assertStatus(200)
        ->assertViewHas('totalIncome', 30000.00)
        ->assertViewHas('totalExpense', 10000.00)
        ->assertViewHas('balance', 20000.00);
}
```

##### Test 7: Date Range Filtering
```php
public function test_dashboard_filters_by_date_range()
{
    $user = User::factory()->create();
    
    // Old transaction (outside range)
    Transaction::factory()->create([
        'user_id' => $user->id,
        'date' => '2025-09-01',
        'amount' => 5000.00,
        'type' => 'expense'
    ]);
    
    // New transaction (in range)
    Transaction::factory()->create([
        'user_id' => $user->id,
        'date' => '2025-10-05',
        'amount' => 3000.00,
        'type' => 'expense'
    ]);
    
    $response = $this->actingAs($user)
        ->get('/finance/dashboard?start_date=2025-10-01&end_date=2025-10-08');
    
    $response->assertStatus(200)
        ->assertViewHas('totalExpense', 3000.00); // Only October transaction
}
```

##### Test 8: Chart Data Endpoint
```php
public function test_chart_data_returns_correct_aggregation()
{
    $user = User::factory()->create();
    
    $groceries = ExpenseCategory::factory()->create(['name' => 'Groceries']);
    $transport = ExpenseCategory::factory()->create(['name' => 'Transport']);
    
    Transaction::factory()->count(3)->create([
        'user_id' => $user->id,
        'type' => 'expense',
        'category_type' => ExpenseCategory::class,
        'category_id' => $groceries->id,
        'amount' => 1000.00
    ]); // Total: 3000
    
    Transaction::factory()->count(2)->create([
        'user_id' => $user->id,
        'type' => 'expense',
        'category_type' => ExpenseCategory::class,
        'category_id' => $transport->id,
        'amount' => 500.00
    ]); // Total: 1000
    
    $response = $this->actingAs($user)
        ->getJson('/finance/chart-data');
    
    $response->assertStatus(200)
        ->assertJson([
            'Groceries' => 3000.00,
            'Transport' => 1000.00
        ]);
}
```

##### Test 9: Category Drilldown
```php
public function test_category_drilldown_shows_transactions()
{
    $user = User::factory()->create();
    $groceries = ExpenseCategory::factory()->create(['name' => 'Groceries']);
    
    Transaction::factory()->count(5)->create([
        'user_id' => $user->id,
        'category_type' => ExpenseCategory::class,
        'category_id' => $groceries->id
    ]);
    
    $response = $this->actingAs($user)
        ->getJson("/finance/category-drilldown?category=Groceries");
    
    $response->assertStatus(200)
        ->assertJsonCount(5, 'transactions');
}
```

---

## Chart Data Validation

### 4. Chart Accuracy Tests

**File:** `tests/Feature/ChartDataAccuracyTest.php`

##### Test 10: Expense Breakdown Pie Chart
```php
public function test_expense_breakdown_percentages_sum_to_100()
{
    $user = User::factory()->create();
    
    $categories = ExpenseCategory::factory()->count(5)->create();
    
    foreach ($categories as $category) {
        Transaction::factory()->create([
            'user_id' => $user->id,
            'type' => 'expense',
            'category_id' => $category->id,
            'category_type' => ExpenseCategory::class,
            'amount' => rand(100, 1000)
        ]);
    }
    
    $response = $this->actingAs($user)
        ->getJson('/finance/chart-data');
    
    $data = $response->json();
    $total = array_sum($data);
    
    foreach ($data as $category => $amount) {
        $percentage = ($amount / $total) * 100;
        $this->assertGreaterThan(0, $percentage);
        $this->assertLessThanOrEqual(100, $percentage);
    }
    
    // Verify all percentages sum to ~100%
    $totalPercentage = array_sum(array_map(
        fn($amt) => ($amt / $total) * 100,
        $data
    ));
    
    $this->assertEquals(100, round($totalPercentage, 2));
}
```

##### Test 11: Multi-Currency Aggregation
```php
public function test_dashboard_handles_multiple_currencies()
{
    $user = User::factory()->create();
    
    Transaction::factory()->create([
        'user_id' => $user->id,
        'type' => 'income',
        'amount' => 50000,
        'currency' => 'BDT'
    ]);
    
    Transaction::factory()->create([
        'user_id' => $user->id,
        'type' => 'income',
        'amount' => 500,
        'currency' => 'USD'
    ]);
    
    $response = $this->actingAs($user)
        ->get('/finance/dashboard');
    
    // Should show both currencies separately or with conversion
    $response->assertStatus(200);
    
    // TODO: Implement currency conversion logic
    // For now, verify both transactions exist
    $this->assertDatabaseCount('transactions', 2);
}
```

##### Test 12: Empty State Handling
```php
public function test_dashboard_shows_zero_when_no_transactions()
{
    $user = User::factory()->create();
    
    $response = $this->actingAs($user)
        ->get('/finance/dashboard');
    
    $response->assertStatus(200)
        ->assertViewHas('totalIncome', 0)
        ->assertViewHas('totalExpense', 0)
        ->assertViewHas('balance', 0)
        ->assertViewHas('expenseBreakdown', collect([]));
}
```

---

## UI/UX Acceptance Tests

### 5. Browser Automation Tests (Laravel Dusk)

**File:** `tests/Browser/ChatbotFlowTest.php`

##### Test 13: Complete Chatbot UI Flow
```php
/**
 * @group browser
 */
public function test_chatbot_widget_opens_and_sends_message()
{
    $this->browse(function (Browser $browser) {
        $user = User::factory()->create();
        
        $browser->loginAs($user)
            ->visit('/finance/dashboard')
            
            // 1. Verify chatbot button exists
            ->assertVisible('.chatbot-button')
            
            // 2. Click to open chatbot
            ->click('.chatbot-button')
            ->pause(500)
            
            // 3. Verify chat window opened
            ->assertVisible('.chat-window')
            ->assertSee('Finance Assistant')
            
            // 4. Type message
            ->type('@chat-input', 'I spent 500 taka on groceries')
            
            // 5. Send message
            ->click('@send-button')
            
            // 6. Verify typing indicator appears
            ->waitFor('.typing-indicator', 2)
            
            // 7. Wait for response
            ->waitForText('I found an expense', 10)
            
            // 8. Verify confirmation modal opens
            ->waitFor('.confirmation-modal', 2)
            ->assertSee('Confirm Transaction')
            ->assertSee('500')
            ->assertSee('BDT')
            ->assertSee('groceries')
            
            // 9. Confirm transaction
            ->click('@confirm-button')
            
            // 10. Verify success message
            ->waitForText('Transaction saved successfully', 5)
            
            // 11. Verify page refreshes
            ->pause(2000)
            ->assertPathIs('/finance/dashboard');
        
        // 12. Verify transaction in database
        $this->assertDatabaseHas('transactions', [
            'user_id' => $user->id,
            'amount' => 500.00
        ]);
    });
}
```

##### Test 14: Entity Highlighting
```php
public function test_chat_message_highlights_entities()
{
    $this->browse(function (Browser $browser) {
        $user = User::factory()->create();
        
        $browser->loginAs($user)
            ->visit('/finance/dashboard')
            ->click('.chatbot-button')
            ->pause(500)
            
            ->type('@chat-input', 'Spent 500 BDT on groceries at Agora')
            ->click('@send-button')
            
            ->waitForText('500 BDT', 10)
            
            // Verify entity highlighting classes
            ->assertSourceHas('class="highlight-amount"')
            ->assertSourceHas('class="highlight-category"')
            ->assertSourceHas('class="highlight-vendor"');
    });
}
```

##### Test 15: Low Confidence Warning Display
```php
public function test_low_confidence_shows_warning_badge()
{
    $this->browse(function (Browser $browser) {
        $user = User::factory()->create();
        
        $browser->loginAs($user)
            ->visit('/finance/dashboard')
            ->click('.chatbot-button')
            ->pause(500)
            
            // Send ambiguous message
            ->type('@chat-input', 'bought stuff')
            ->click('@send-button')
            
            ->waitFor('.confirmation-modal', 5)
            
            // Verify warning badge visible
            ->assertVisible('.low-confidence-warning')
            ->assertSee('Low confidence. Please review carefully.');
    });
}
```

##### Test 16: Form Validation
```php
public function test_manual_transaction_form_validates_input()
{
    $this->browse(function (Browser $browser) {
        $user = User::factory()->create();
        
        $browser->loginAs($user)
            ->visit('/finance/transactions/create')
            
            // Submit empty form
            ->press('Create Transaction')
            
            // Verify validation errors
            ->assertSee('The amount field is required')
            ->assertSee('The date field is required')
            ->assertSee('The category field is required')
            
            // Fill valid data
            ->click('button[data-type="expense"]')
            ->type('amount', '500.50')
            ->select('currency', 'BDT')
            ->type('date', '2025-10-08')
            ->select('category_id', '1')
            ->type('description', 'Test expense')
            
            // Submit
            ->press('Create Transaction')
            
            // Verify redirect
            ->assertPathIs('/finance/transactions')
            ->assertSee('Transaction created successfully');
    });
}
```

---

## Sample Test Phrases

### 6. Comprehensive Parsing Test Suite

#### Income Phrases (30 samples)
```
1. "I received 50000 taka as salary today"
2. "Got paid $1500 for freelance work"
3. "Bonus of 25000 BDT"
4. "Earned 3000 from side hustle"
5. "Received dividend of $200"
6. "Salary credited 45000 taka"
7. "Freelance payment 2500 dollars"
8. "Got commission 5000 BDT"
9. "Investment return $350"
10. "Rent income 15000 taka"
11. "Sold old laptop for 20000 BDT"
12. "Gift money 5000 taka"
13. "Refund of 1200 BDT"
14. "Cash back 500 taka"
15. "Prize money $100"
16. "Part time job paid 8000 taka"
17. "Consulting fee $800"
18. "Royalty income 3000 BDT"
19. "Interest earned 450 taka"
20. "Scholarship 30000 BDT"
21. "Allowance 10000 taka"
22. "Performance bonus $500"
23. "Overtime pay 6000 BDT"
24. "Tips received 2000 taka"
25. "Project milestone $1200"
26. "Revenue share 4000 BDT"
27. "Reimbursement 3500 taka"
28. "Pension payment $900"
29. "Grant money 50000 BDT"
30. "Profit from sale 8000 taka"
```

#### Expense Phrases (50 samples)
```
1. "Spent 500 taka on groceries at Agora"
2. "Paid 1200 for electricity bill"
3. "Movie tickets cost $15"
4. "Bought medicine for 850 BDT"
5. "House rent 18000 taka"
6. "Taxi fare 300 BDT"
7. "Lunch at restaurant $25"
8. "Internet bill 1500 taka"
9. "Gym membership $50"
10. "Haircut cost 400 BDT"
11. "Coffee at Starbucks $5"
12. "Phone recharge 200 taka"
13. "Uber ride 450 BDT"
14. "Netflix subscription $12"
15. "Gas bill 900 taka"
16. "Parking fee 100 BDT"
17. "Office supplies $30"
18. "Dry cleaning 600 taka"
19. "Water bill 250 BDT"
20. "Concert ticket $75"
21. "Book purchase 1200 taka"
22. "Gym equipment $200"
23. "Pet food 800 BDT"
24. "Car wash 300 taka"
25. "Dentist visit $150"
26. "Clothes shopping 4500 BDT"
27. "Fuel 2000 taka"
28. "Insurance premium $300"
29. "Birthday gift 2500 BDT"
30. "Hotel booking $180"
31. "Flight ticket 35000 taka"
32. "Vegetables 350 BDT"
33. "Laundry 250 taka"
34. "Repair work $85"
35. "Charity donation 5000 BDT"
36. "Domain renewal $15"
37. "Plumber service 1500 taka"
38. "Car maintenance $250"
39. "Tuition fee 20000 BDT"
40. "Furniture 15000 taka"
41. "Electronics $450"
42. "Beauty salon 1800 BDT"
43. "Snacks 150 taka"
44. "Bus pass $40"
45. "Magazine subscription 500 BDT"
46. "Garden supplies 800 taka"
47. "Toys for kids $60"
48. "Home decor 3000 BDT"
49. "Stationery 400 taka"
50. "Video game $70"
```

#### Complex Multi-Transaction Phrases (10 samples)
```
1. "Yesterday bought groceries for 2500 taka at Shwapno and paid 800 for transport"
2. "Spent $50 on dinner and $15 on movie tickets"
3. "Paid 1200 for electricity, 900 for gas, and 1500 for internet"
4. "Received salary 50000 BDT and got bonus 10000 taka"
5. "Coffee $5, lunch $20, and taxi $12"
6. "Shopping 3000 taka, haircut 400 BDT, medicines 850"
7. "Earned 5000 from freelance and 2000 from sales"
8. "Fuel 2000, car wash 300, parking 100 taka"
9. "Groceries $80 at Walmart, pharmacy $25 at CVS"
10. "Rent 18000, utilities 3000, internet 1500 BDT"
```

#### Edge Cases (20 samples)
```
Ambiguous (Should fail or low confidence):
1. "spent some money"
2. "bought stuff"
3. "500"
4. "paid John"
5. "yesterday transaction"

Large Numbers:
6. "Salary 150000 taka"
7. "House purchase $250,000"
8. "Car loan 3,500,000 BDT"

Decimals:
9. "Coffee cost 4.75 dollars"
10. "Bus fare 25.50 taka"

Zero/Negative (Should fail):
11. "Spent 0 taka"
12. "Paid -500 BDT"

Special Characters:
13. "Bought milk @ 120 taka"
14. "Dinner cost ~$30"
15. "Roughly 1000 BDT spent"

Mixed Languages:
16. "ভাত কিনলাম 500 taka"
17. "Paid ১০০০ টাকা for groceries"

Dates:
18. "Last Monday spent 500 on groceries"
19. "On 1st October paid rent 18000"
20. "Three days ago bought medicine 850 taka"
```

---

## Manual Testing Checklist

### 7. QA Testing Protocol

#### Pre-Release Checklist

**Environment Setup:**
- [ ] Database seeded with demo data
- [ ] Gemini API key configured
- [ ] XAMPP MySQL running
- [ ] Laravel cache cleared
- [ ] Browser cache cleared

**Functionality Tests:**

**Dashboard:**
- [ ] Summary cards show correct totals
- [ ] Date range filter updates data
- [ ] Pie chart renders correctly
- [ ] Chart percentages sum to 100%
- [ ] Click on chart slice opens drilldown
- [ ] Recent transactions list displays
- [ ] Empty state shows when no data

**Transactions List:**
- [ ] Filter by type (income/expense) works
- [ ] Filter by category works
- [ ] Date range filter works
- [ ] Search box finds transactions
- [ ] Pagination works
- [ ] Edit button opens form
- [ ] Delete confirmation works
- [ ] Color coding (green/red) correct

**Chatbot:**
- [ ] Widget button visible
- [ ] Click opens chat window
- [ ] Welcome message displays
- [ ] Can type and send message
- [ ] Typing indicator shows
- [ ] Response appears
- [ ] Entity highlighting works
- [ ] Confirmation modal opens
- [ ] Preview data is accurate
- [ ] Low confidence warning shows
- [ ] Confirm saves to database
- [ ] Cancel discards transaction
- [ ] Chat history persists
- [ ] Unread badge works
- [ ] Close button works

**Manual Entry Form:**
- [ ] Type selector (Income/Expense) works
- [ ] Category dropdown loads dynamically
- [ ] Date picker works
- [ ] Amount validation works
- [ ] Currency selector works
- [ ] File upload accepts images/PDF
- [ ] File upload rejects large files (>5MB)
- [ ] Form validation shows errors
- [ ] Submit creates transaction
- [ ] Redirect after submit

**Authorization:**
- [ ] Users can't see others' transactions
- [ ] Edit/Delete only own transactions
- [ ] Unauthenticated redirects to login

**Performance:**
- [ ] Dashboard loads in < 2 seconds
- [ ] Chart renders in < 1 second
- [ ] API response time < 500ms
- [ ] Chat parsing < 3 seconds (Gemini)
- [ ] Chat parsing < 100ms (fallback)

**Responsive Design:**
- [ ] Mobile view (320px width)
- [ ] Tablet view (768px width)
- [ ] Desktop view (1920px width)
- [ ] Chatbot mobile friendly
- [ ] Forms usable on mobile

**Browser Compatibility:**
- [ ] Chrome (latest)
- [ ] Firefox (latest)
- [ ] Safari (latest)
- [ ] Edge (latest)

---

## Running the Tests

### Command Reference

```bash
# Run all tests
php artisan test

# Run specific test suite
php artisan test --testsuite=Unit
php artisan test --testsuite=Feature

# Run specific test file
php artisan test tests/Unit/GeminiServiceParsingTest.php

# Run with coverage
php artisan test --coverage

# Run browser tests (Dusk)
php artisan dusk

# Run specific browser test
php artisan dusk tests/Browser/ChatbotFlowTest.php

# Parallel testing (faster)
php artisan test --parallel

# Stop on first failure
php artisan test --stop-on-failure
```

### CI/CD Pipeline

**GitHub Actions Workflow:**
```yaml
name: Tests

on: [push, pull_request]

jobs:
  tests:
    runs-on: ubuntu-latest
    
    steps:
      - uses: actions/checkout@v2
      
      - name: Setup PHP
        uses: shivammathur/setup-php@v2
        with:
          php-version: 8.2
          
      - name: Install Dependencies
        run: composer install
        
      - name: Run Tests
        run: php artisan test
        env:
          GEMINI_API_KEY: ${{ secrets.GEMINI_API_KEY }}
```

---

## Expected Test Results

### Acceptance Criteria

**Unit Tests:** 
- ✅ 50+ tests
- ✅ 100% pass rate
- ✅ 85%+ code coverage

**Feature Tests:**
- ✅ 30+ tests
- ✅ 100% pass rate
- ✅ All CRUD operations covered

**Browser Tests:**
- ✅ 15+ scenarios
- ✅ Complete user flows tested
- ✅ Cross-browser compatibility

**Parsing Accuracy:**
- ✅ 95%+ accuracy on clear inputs
- ✅ 80%+ accuracy on ambiguous inputs
- ✅ 100% fallback rule coverage

**Chart Data:**
- ✅ Percentages sum to 100%
- ✅ No rounding errors > 0.01
- ✅ Multi-currency handled correctly

---

## Bug Reporting Template

```markdown
### Bug Report

**Test Case:** [Test name or manual step]
**Expected Result:** [What should happen]
**Actual Result:** [What actually happened]
**Steps to Reproduce:**
1. 
2. 
3. 

**Screenshots:** [Attach if applicable]
**Browser/Environment:** [Chrome 119, MySQL 8.0, etc.]
**Severity:** Critical / High / Medium / Low
**Assigned To:** 
**Status:** Open / In Progress / Fixed / Closed
```

---

## Test Data Setup

### Seed Command for Testing
```bash
# Reset and seed database
php artisan migrate:fresh --seed

# Seed specific data
php artisan db:seed --class=IncomeSourceSeeder
php artisan db:seed --class=ExpenseCategorySeeder

# Create demo user
php artisan tinker
>>> User::factory()->create(['email' => 'test@example.com'])
```

---

## Performance Benchmarks

### Target Metrics
- Dashboard load: < 2s
- Chart render: < 1s
- API response: < 500ms
- Gemini parse: < 3s
- Fallback parse: < 100ms
- DB query time: < 50ms

### Load Testing
```bash
# Install Apache Bench
sudo apt install apache2-utils

# Test dashboard endpoint
ab -n 1000 -c 10 http://localhost:8000/finance/dashboard

# Expected: 
# - Requests per second: > 100
# - Time per request: < 100ms
# - Failed requests: 0
```

---

**Document Version:** 1.0.0  
**Last Updated:** October 8, 2025  
**Maintained By:** Development Team  
**Review Cycle:** After each sprint

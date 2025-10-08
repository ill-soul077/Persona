# Testing & QA Quick Reference

## 🚀 Quick Start Testing

### Run All Tests
```bash
# Full test suite
php artisan test

# With coverage report
php artisan test --coverage --min=80

# Parallel execution (faster)
php artisan test --parallel
```

### Run Specific Test Suites
```bash
# Unit tests only
php artisan test --testsuite=Unit

# Feature tests only
php artisan test --testsuite=Feature

# Specific test file
php artisan test tests/Unit/GeminiServiceParsingTest.php

# Specific test method
php artisan test --filter=test_parses_basic_income_with_salary
```

### Manual Interactive Testing
```bash
# Run interactive manual testing script
php manual-test.php

# Options:
# 1. Test single phrase parsing
# 2. Test complete chat flow (parse → preview → confirm)
# 3. Test chart data aggregation
# 4. Run sample phrases (80+ phrases)
# 5. Test edge cases
# 6. View statistics
# 7. Cleanup test data
```

---

## 📊 Expected Results

### Parsing Accuracy Targets
- ✅ **High Confidence (≥0.9):** 70% of clear inputs
- ⚠️  **Medium Confidence (0.6-0.9):** 20% of inputs
- ❌ **Low Confidence (<0.6):** 10% of ambiguous inputs

### Performance Benchmarks
- Dashboard load: **< 2 seconds**
- Chart render: **< 1 second**
- API response: **< 500ms**
- Gemini parse: **< 3 seconds**
- Fallback parse: **< 100ms**

### Chart Data Validation
- Percentages must sum to **100% ± 0.01**
- No rounding errors **> 0.01**
- Multi-currency handled correctly
- Empty states return zero

---

## 🧪 Sample Test Phrases

### ✅ Should Parse Successfully (High Confidence)

**Income:**
```
"I received 50000 taka as salary"
"Got paid $1500 for freelance work"
"Bonus of 25000 BDT received today"
"Earned 3000 from side hustle"
```

**Expense:**
```
"Spent 500 taka on groceries at Agora"
"Paid 1200 for electricity bill"
"Movie tickets cost $15"
"Bought medicine for 850 BDT"
```

### ⚠️  Should Parse with Warnings (Medium Confidence)

```
"bought groceries yesterday"
"paid for shopping"
"spent around 500 taka"
```

### ❌ Should Fail or Low Confidence

```
"bought stuff"          # Too vague
"500"                   # No context
"paid John"             # Missing amount
"spent -500 taka"       # Negative amount
"0 BDT"                 # Zero amount
```

---

## 📋 Manual Testing Checklist

### Dashboard
- [ ] Summary cards show correct totals
- [ ] Date range filter updates data
- [ ] Pie chart renders correctly
- [ ] Chart percentages sum to 100%
- [ ] Click on chart slice opens drilldown
- [ ] Recent transactions list displays
- [ ] Empty state shows when no data

### Chatbot
- [ ] Widget button visible and clickable
- [ ] Chat window opens with animation
- [ ] Welcome message displays
- [ ] Can type and send message
- [ ] Typing indicator shows
- [ ] Entity highlighting works (green=amounts, blue=categories)
- [ ] Confirmation modal shows preview
- [ ] Low confidence warning appears when needed
- [ ] Confirm saves to database
- [ ] Cancel discards transaction
- [ ] Chat history persists in localStorage

### Transactions List
- [ ] Filter by type works
- [ ] Filter by category works
- [ ] Date range filter works
- [ ] Search finds transactions
- [ ] Pagination works
- [ ] Edit/Delete only own transactions
- [ ] Color coding correct (green/red)

### Manual Entry Form
- [ ] Type selector switches categories
- [ ] Category dropdown loads dynamically
- [ ] Date picker works
- [ ] Amount validation works
- [ ] File upload accepts images/PDF
- [ ] Form validation shows errors
- [ ] Submit creates transaction

---

## 🐛 Common Issues & Solutions

### Gemini API Errors
```bash
# Check API key
php artisan tinker
>>> config('services.gemini.api_key')

# Test API connection
curl "https://generativelanguage.googleapis.com/v1beta/models/gemini-1.5-flash-latest:generateContent?key=YOUR_KEY"
```

### Database Issues
```bash
# Reset and reseed
php artisan migrate:fresh --seed

# Verify data
php artisan tinker
>>> App\Models\Transaction::count()
>>> App\Models\ExpenseCategory::count()
```

### Test Failures
```bash
# Clear cache
php artisan cache:clear
php artisan config:clear
php artisan view:clear

# Run single failing test with verbose output
php artisan test --filter=test_name -vvv
```

---

## 📈 Test Coverage Goals

### Current Coverage
- **Unit Tests:** 50+ tests covering GeminiService
- **Feature Tests:** 30+ tests covering controllers, chat flow, charts
- **Code Coverage Target:** 85%+

### Coverage Report
```bash
# Generate HTML coverage report
php artisan test --coverage --coverage-html=coverage-report

# View report
# Open coverage-report/index.html in browser
```

---

## 🔄 Continuous Integration

### GitHub Actions Workflow
```yaml
# .github/workflows/tests.yml
name: Tests
on: [push, pull_request]
jobs:
  test:
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
        run: php artisan test --coverage --min=80
```

---

## 📊 Sample Test Run Output

```
PASS  Tests\Unit\GeminiServiceParsingTest
✓ it parses basic income with salary                    0.23s
✓ it parses freelance income in usd                      0.18s
✓ it parses grocery expense with vendor                  0.21s
✓ it parses utility bill expense                         0.19s
✓ it detects bdt currency variations                     0.45s
✓ it uses fallback rules when gemini fails               0.12s

PASS  Tests\Feature\ChatFlowTest
✓ complete chat flow creates transaction                 0.34s
✓ low confidence parsing shows warning                   0.28s
✓ fallback rules work when gemini fails                  0.15s
✓ income transaction creates correctly                   0.31s

PASS  Tests\Feature\ChartDataAccuracyTest
✓ expense breakdown percentages sum to 100               0.42s
✓ dashboard calculates correct totals                    0.38s
✓ date range filtering works correctly                   0.35s
✓ chart data aggregates by category correctly            0.40s

Tests:    20 passed (85 assertions)
Duration: 4.82s
```

---

## 🎯 Acceptance Criteria

### ✅ Test Suite PASSES if:
- All unit tests pass (100%)
- All feature tests pass (100%)
- Code coverage ≥ 85%
- Parsing accuracy ≥ 80% on sample phrases
- Chart percentages sum to 100% ± 0.01
- No N+1 query issues
- Response times meet benchmarks

### ❌ Test Suite FAILS if:
- Any test fails
- Code coverage < 85%
- Parsing accuracy < 70%
- Chart aggregation errors
- Performance degradation
- Security vulnerabilities detected

---

## 📝 Bug Reporting

When a test fails:
1. **Reproduce:** Run test in isolation
2. **Document:** Screenshot + error message
3. **Debug:** Use `dd()` or `dump()` for inspection
4. **Fix:** Make minimal change to pass test
5. **Verify:** Run full test suite again

```bash
# Debug specific test
php artisan test --filter=failing_test_name -vvv

# Enable query logging
DB::enableQueryLog();
// ... code ...
dd(DB::getQueryLog());
```

---

**Last Updated:** October 8, 2025  
**Maintained By:** Development Team  
**Next Review:** After each sprint

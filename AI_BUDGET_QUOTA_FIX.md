# AI Budget Summary - Quota Fix Implementation

## Problem Diagnosed
- **429 Quota Errors**: Every refresh hit the Gemini API immediately, exhausting free tier quota
- **No Persistent Cache**: Only 1-hour memory cache; users refreshing after 1 hour triggered new API calls
- **Poor Retry Logic**: Short backoff delays couldn't handle quota exhaustion gracefully

## Solution Implemented

### 1. Database-Backed Caching ✅
- **New Table**: `budget_summaries`
  - Stores one summary per user per month
  - Tracks: summary JSON, model used, fallback flag, timestamps
  - Unique constraint: `(user_id, month)`
  
- **Automatic Loading**: Dashboard checks DB first and shows existing summary if available

### 2. Request Throttling ✅
- **1 Refresh Per Hour**: Users can only regenerate summary once per 60 minutes
- **Graceful Messaging**: Shows blue banner "Showing cached summary (updated recently)"
- **DB Check**: `DashboardController::refreshBudgetSummary()` checks `updated_at` before calling AI

### 3. Enhanced Retry & Backoff ✅
- **429-Specific Handling**:
  - Up to 5 retries (vs. 3 for other errors)
  - Longer delays: 3s → 6s → 12s → 24s → 48s with 50% jitter
  - Logs each retry attempt with delay time
  
- **Exponential Backoff**:
  - For 5xx errors: 1s → 2s → 4s
  - For 429: 3× multiplier to respect quota cooldown

### 4. Gemini 2.5 Flash Priority ✅
- **Model Selection**:
  1. gemini-2.5-flash (preferred)
  2. gemini-flash-latest
  3. gemini-2.5-flash-lite
  4. gemini-2.0-flash
  5. older fallbacks
  
- **Logging**: Every budget advice call logs which model is used

### 5. Heuristic Fallback ✅
- **Rule-Based Summary**: When quota exhausted, generates deterministic advice:
  - Budget status analysis (over/near/under)
  - Category allocation (groceries 30%, transport 15%, bills 25%, savings 20%, discretionary 10%)
  - Contextual recommendations based on spending pattern
  - Yellow banner: "Using heuristic summary due to temporary AI quota limits"

## Files Changed

### New Files
- `database/migrations/2025_10_18_152333_create_budget_summaries_table.php`
- `app/Models/BudgetSummary.php`

### Modified Files
- `app/Http/Controllers/DashboardController.php`
  - Added DB check on page load
  - Throttle logic in `refreshBudgetSummary()`
  - Saves summary to DB after generation
  
- `app/Services/GeminiService.php`
  - Removed redundant Cache::remember (now DB-backed)
  - Enhanced `executeWithRetry()` with 429-specific delays
  - Added model logging
  
- `resources/views/components/budget-ai-summary.blade.php`
  - Shows throttle info banner
  - Shows fallback banner
  
- `config/services.php`
  - Default model: `gemini-2.5-flash`

## How It Works Now

### First Visit
1. User lands on dashboard
2. Controller checks DB for existing summary
3. If found and fresh (<24h), displays immediately
4. If not, shows empty state with "Get AI summary" button

### Refresh Click
1. Check DB for summary updated < 60 min ago
   - If yes: return cached, show throttle banner
   - If no: proceed
2. Gather budget context (spent, remaining, categories)
3. Call `GeminiService::generateBudgetAdvice()`
   - Try Gemini 2.5 Flash API
   - If 429: retry with backoff (up to 5 attempts)
   - If still failing: use heuristic fallback
4. Save result to `budget_summaries` table
5. Log to `ai_logs` (only if real AI used)
6. Redirect with flash session

### Quota Exhaustion
1. API returns 429 after retries
2. Service catches and returns heuristic summary
3. DB saves with `is_fallback = true`
4. User sees yellow banner + reasonable advice

## Testing Commands

```powershell
# Check logs for model usage
php artisan tail

# List available models
php artisan gemini:models --filter=flash

# Clear caches
php artisan config:clear
php artisan cache:clear

# Check DB
php artisan tinker
>>> App\Models\BudgetSummary::with('user')->latest()->first()
```

## Expected Behavior

- **First refresh today**: Calls Gemini API (or fallback if quota hit)
- **Second refresh within 1 hour**: Shows cached DB result, no API call
- **After 1 hour**: Allows new refresh, updates DB
- **Quota exhausted**: Shows heuristic summary with yellow banner
- **Next day**: Summary still cached; user can refresh once per hour

## Quota Savings

| Before | After |
|--------|-------|
| Every refresh = API call | 1 API call per user per hour max |
| No persistent storage | DB-backed, survives restarts |
| 3 retries @ 1s each | 5 retries @ 3-48s for 429 |
| Memory cache (1h) | DB cache (persistent) |

**Estimated reduction**: 95%+ fewer API calls

## Admin Commands

```powershell
# View current summaries
php artisan tinker
>>> BudgetSummary::count()
>>> BudgetSummary::where('is_fallback', true)->count()

# Clear old summaries (optional)
>>> BudgetSummary::where('created_at', '<', now()->subMonths(2))->delete()
```

## Next Steps (Optional)

1. **Admin Dashboard**: Add page to view quota usage, cached summaries
2. **User Preference**: Let users opt for "fallback only" to never use quota
3. **Scheduled Refresh**: Cron job to pre-generate summaries overnight (spread load)
4. **Quota Monitoring**: Alert when nearing daily/monthly limits

---

**Status**: ✅ All fixes implemented and tested
**Date**: 2025-10-18
**Model Used**: Gemini 2.5 Flash (with fallback to heuristic)

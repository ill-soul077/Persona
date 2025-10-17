# 🛡️ Gemini API Safety & Reliability Features

## Overview
Comprehensive safety mechanisms implemented to handle API failures, rate limits, and ensure reliable receipt scanning service.

---

## ✅ Implemented Features

### 1. **Exponential Backoff with Jitter**
Automatically retries failed requests with increasing delays.

**Configuration:**
- Max Retries: `3`
- Base Delay: `1000ms` (1 second)
- Retry on: HTTP 429, 500, 503, 504

**How it works:**
```
Attempt 1: Immediate
Attempt 2: 1s + jitter (0-300ms)
Attempt 3: 2s + jitter (0-600ms)
Attempt 4: 4s + jitter (0-1200ms)
```

**Benefits:**
- ✅ Handles temporary API failures
- ✅ Reduces thundering herd problem
- ✅ Spreads retry load evenly

---

### 2. **Client-Side Rate Limiting**
Prevents exceeding API quotas before hitting the server.

**Configuration:**
- Max Requests: `50 per minute`
- Tracking: Per-minute buckets
- Cache TTL: `120 seconds`

**Response when exceeded:**
```json
{
  "success": false,
  "error": "Rate limit exceeded. Please wait a moment and try again."
}
```

**Benefits:**
- ✅ Prevents 429 errors
- ✅ User-friendly error messages
- ✅ Automatic recovery after 1 minute

---

### 3. **Circuit Breaker Pattern**
Stops calling API after multiple failures to prevent cascading failures.

**Configuration:**
- Failure Threshold: `5 failures`
- Reset Time: `300 seconds` (5 minutes)
- Auto-recovery: Yes

**States:**
- **CLOSED** (normal): API calls allowed
- **OPEN** (broken): API calls blocked for 5 minutes

**Response when open:**
```json
{
  "success": false,
  "error": "Service temporarily unavailable. Please try again in a few minutes."
}
```

**Benefits:**
- ✅ Prevents API quota exhaustion
- ✅ Protects against cascading failures
- ✅ Automatic recovery after cool-down

---

### 4. **Request Deduplication & Caching**
Prevents duplicate scans of the same receipt image.

**How it works:**
1. Generate MD5 hash of image data
2. Check cache for existing result
3. Return cached result if found (instant response)
4. Cache new results for 1 hour

**Benefits:**
- ✅ Reduces API costs
- ✅ Faster response times
- ✅ Prevents accidental duplicate charges

---

### 5. **Comprehensive Logging**
All API interactions are logged for monitoring and debugging.

**What's logged:**
- ✅ All API requests and responses
- ✅ Rate limit hits
- ✅ Circuit breaker status changes
- ✅ Retry attempts with delays
- ✅ Failures with full stack traces

**Log location:** `storage/logs/laravel.log`

---

## 📊 Monitoring & Stats

### Get Usage Statistics
```php
$geminiService = new GeminiService();
$stats = $geminiService->getUsageStats();
```

**Response:**
```json
{
  "requests_today": 125,
  "requests_this_minute": 3,
  "rate_limit": 50,
  "rate_limit_exceeded": false,
  "circuit_breaker": {
    "status": "CLOSED",
    "failure_count": 0,
    "threshold": 5,
    "last_failure": null,
    "reset_in_seconds": 0
  },
  "retry_config": {
    "max_retries": 3,
    "base_delay_ms": 1000
  },
  "quota_remaining": 1000
}
```

---

## 🔧 Admin Functions

### Manually Reset Circuit Breaker
```php
$geminiService = new GeminiService();
$geminiService->resetCircuitBreakerManually();
```

### Clear Rate Limits
```php
$geminiService = new GeminiService();
$geminiService->clearRateLimits();
```

---

## 🚨 Error Handling

### Error Types and Responses

#### 1. Rate Limit Exceeded (429)
```json
{
  "success": false,
  "error": "Rate limit exceeded. Please wait a moment and try again."
}
```
**Recovery:** Automatic after 1 minute

#### 2. Circuit Breaker Open
```json
{
  "success": false,
  "error": "Service temporarily unavailable. Please try again in a few minutes."
}
```
**Recovery:** Automatic after 5 minutes or manual reset

#### 3. Invalid Receipt Image
```json
{
  "success": false,
  "error": "Not a valid receipt image"
}
```
**Recovery:** Upload a clearer image

#### 4. API Error (after retries)
```json
{
  "success": false,
  "error": "Failed to scan receipt: [API error message]"
}
```
**Recovery:** Check logs, verify API key, retry later

---

## 📈 Best Practices

### For High-Volume Usage

1. **Monitor Usage Daily**
   ```php
   $stats = $geminiService->getUsageStats();
   Log::info('Daily API Usage', $stats);
   ```

2. **Set Up Alerts**
   - Alert when `requests_today` > 800
   - Alert when `circuit_breaker.status` == "OPEN"
   - Alert when `rate_limit_exceeded` == true

3. **Implement Request Queue**
   For very high volume, consider:
   - Laravel Queue with Redis
   - Background worker processing
   - Batch processing during off-peak hours

4. **Cache Aggressively**
   - Current: 1 hour for receipt scans
   - Consider: 24 hours for completed transactions
   - Consider: Permanent cache for verified receipts

---

## 🔮 Future Enhancements

### Planned Features

- [ ] **Request Batching**
  - Send multiple receipts in one API call
  - Reduce API calls by up to 90%

- [ ] **Redis-based Rate Limiting**
  - More accurate per-second limits
  - Distributed rate limiting across servers

- [ ] **Cloud Console Integration**
  - Real-time quota checking
  - Automatic quota alerts
  - Usage dashboards

- [ ] **Fallback to Local Model**
  - Ollama/LocalAI integration
  - Automatic failover when API unavailable
  - Reduced cloud costs

- [ ] **Advanced Circuit Breaker**
  - Half-open state for testing recovery
  - Per-endpoint circuit breakers
  - Gradual recovery (ramp-up)

---

## ⚙️ Configuration

### Environment Variables (Optional)
Add to `.env` for custom configuration:

```env
# Rate Limiting
GEMINI_MAX_REQUESTS_PER_MINUTE=50

# Circuit Breaker
GEMINI_CIRCUIT_BREAKER_THRESHOLD=5
GEMINI_CIRCUIT_BREAKER_RESET_TIME=300

# Retry Configuration
GEMINI_MAX_RETRIES=3
GEMINI_RETRY_BASE_DELAY=1000

# Cache
GEMINI_CACHE_TTL=3600
```

### Code Configuration
Or modify directly in `GeminiService.php`:

```php
protected int $maxRequestsPerMinute = 50;
protected int $circuitBreakerThreshold = 5;
protected int $circuitBreakerResetTime = 300;
protected int $maxRetries = 3;
protected int $retryBaseDelay = 1000;
```

---

## 📊 Performance Metrics

### Expected Behavior

| Metric | Value | Notes |
|--------|-------|-------|
| Success Rate | >99% | With retries enabled |
| Average Response Time | 3-5s | First attempt |
| Cache Hit Rate | 10-20% | Varies by usage |
| Retry Rate | <5% | Healthy API |
| Circuit Breaker Trips | 0 | Normal operation |

### Performance Under Load

| Load | Behavior | Action |
|------|----------|--------|
| <50 req/min | Normal | No action needed |
| 50-60 req/min | Rate limiting | Some requests delayed |
| >60 req/min | Queueing recommended | Implement background processing |
| 5+ failures | Circuit breaker opens | Service paused 5 min |

---

## 🐛 Troubleshooting

### Issue: Too Many 429 Errors
**Solution:**
1. Check `requests_this_minute` in stats
2. Reduce `maxRequestsPerMinute` to 30
3. Implement request queue

### Issue: Circuit Breaker Keeps Opening
**Solution:**
1. Check API key validity
2. Review error logs for root cause
3. Verify quota in Google Cloud Console
4. Temporarily increase `circuitBreakerThreshold`

### Issue: Slow Response Times
**Solution:**
1. Check if retries are happening (logs)
2. Increase cache TTL for better hit rate
3. Consider implementing request queue
4. Monitor Google API status page

### Issue: Cache Not Working
**Solution:**
1. Verify Laravel cache driver is working
2. Check cache permissions
3. Test with `php artisan cache:clear`
4. Consider switching to Redis for better performance

---

## 📚 Related Documentation

- [AI Receipt Scanner Guide](AI_RECEIPT_SCANNER.md)
- [Quick Start Guide](RECEIPT_SCANNER_QUICK_START.md)
- [Implementation Summary](AI_RECEIPT_SCANNER_COMPLETE.md)

---

## 🔒 Security Notes

- ✅ API keys stored server-side only
- ✅ No client-side exposure
- ✅ All requests authenticated
- ✅ Rate limiting prevents abuse
- ✅ Circuit breaker prevents quota exhaustion

---

**Last Updated:** October 18, 2025  
**Version:** 2.0.0  
**Status:** 🟢 Production Ready with Enterprise-Grade Reliability

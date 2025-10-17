# Receipt Scanner Testing Guide

## Changes Made for Better Error Reporting

### 1. Enhanced Error Handling in GeminiService
- Added detailed error logging with error code, file, and line
- User-friendly error messages for common scenarios:
  - Model unavailable: "The AI model is currently unavailable"
  - Timeout: "The request timed out. Please try again with a smaller image"
  - Quota exceeded: "API quota exceeded. Please try again in a few minutes"
  - Parsing errors: "Could not read the receipt. Please ensure the image is clear"
- Debug messages shown only when `APP_DEBUG=true`

### 2. Added API Call Logging
- Logs before making API call with:
  - Sanitized URL (API key masked)
  - MIME type
  - Image size in bytes

### 3. Frontend Debug Display
- Shows `debug_message` from API when available
- Better error messages in modal

## Testing Steps

### Step 1: Enable Debug Mode
Edit `.env`:
```
APP_DEBUG=true
```

### Step 2: Clear Log File
```powershell
Clear-Content storage\logs\laravel.log
```

### Step 3: Test with Receipt Image
1. Navigate to: http://localhost:8000/finance/transactions/create
2. Click "Scan Receipt with AI" button
3. Upload a receipt image (JPG/PNG, max 5MB)
4. Watch for the response

### Step 4: Check Logs
```powershell
Get-Content storage\logs\laravel.log -Tail 100
```

## What to Look For

### In Browser Console (F12):
- Network tab → Check POST request to `/finance/transactions/scan-receipt`
- Response body should show detailed error if any
- Console tab → Look for JavaScript errors

### In Laravel Logs:
Look for these log entries:
```
[timestamp] local.INFO: Starting receipt scan API call
[timestamp] local.ERROR: Gemini receipt scan API error (if API fails)
[timestamp] local.ERROR: Receipt scanning error (if exception occurs)
```

## Common Issues & Solutions

### Issue 1: "The AI model is currently unavailable"
**Cause**: Model not found or wrong endpoint
**Solution**: Verify model name in GeminiService.php line ~657

### Issue 2: "API quota exceeded"
**Cause**: Too many requests or quota limit reached
**Solution**: Wait a few minutes or check Google Cloud Console quota

### Issue 3: "The request timed out"
**Cause**: Image too large or slow API response
**Solution**: Compress image or increase timeout in GeminiService.php

### Issue 4: "Could not read the receipt"
**Cause**: Invalid JSON response or unclear image
**Solution**: Try with clearer receipt image

## Test Receipt Images

You can test with:
1. **Real receipt**: Take photo of any receipt
2. **Sample receipt**: Create simple receipt in Paint/Photoshop
3. **Online samples**: Download from Google Images (search "receipt sample")

## Expected Successful Response

```json
{
  "success": true,
  "data": {
    "amount": "25.99",
    "date": "2024-01-15",
    "description": "Grocery shopping",
    "merchantName": "SuperMart",
    "category": "groceries"
  }
}
```

## Expected Error Response (Debug Mode)

```json
{
  "success": false,
  "error": "The AI model is currently unavailable. Please try again later.",
  "debug_message": "Failed to scan receipt: 404 model not found"
}
```

## Next Steps After Testing

1. **If it works**: Great! You can now disable debug mode
2. **If it fails**: Share the log output and we'll diagnose the issue
3. **Rate limiting**: If you test multiple times quickly, you might hit rate limit (50/min)

## Quick Test Command Sequence

```powershell
# 1. Clear logs
Clear-Content storage\logs\laravel.log

# 2. Test with browser (upload receipt)

# 3. Check logs immediately
Get-Content storage\logs\laravel.log -Tail 50

# 4. Search for specific errors
Get-Content storage\logs\laravel.log | Select-String -Pattern "Receipt scanning error|Gemini receipt scan API error|Starting receipt scan"
```

## API Key Verification

Current API key: `AIzaSyCg2151c78yrL6aXmbEeeUJ4oKSHfn7QfA`

To verify key is valid:
```bash
curl "https://generativelanguage.googleapis.com/v1beta/models/gemini-1.5-flash:generateContent?key=AIzaSyCg2151c78yrL6aXmbEeeUJ4oKSHfn7QfA" \
  -H 'Content-Type: application/json' \
  -d '{"contents":[{"parts":[{"text":"Hello"}]}]}'
```

Expected: JSON response with model output
If error: Check API key permissions in Google Cloud Console

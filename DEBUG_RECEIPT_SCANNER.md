# 🔍 DEBUG INSTRUCTIONS - Receipt Scanner

## What to Do Now:

### Step 1: Open the Transaction Create Page
Go to: **http://localhost:8000/finance/transactions/create**

### Step 2: Try to Scan a Receipt
1. Click **"Scan Receipt with AI"** button
2. Upload any receipt image (or any image for testing)
3. Wait for the error to appear

### Step 3: Check the Logs
After you see the error, run this command:

```powershell
Get-Content storage\logs\laravel.log
```

**Then share the full output with me** so I can see exactly what's failing.

---

## Alternative: Check Browser Console

1. Press **F12** to open Developer Tools
2. Go to **Console** tab
3. Try scanning receipt again
4. Look for any red error messages
5. Also check **Network** tab:
   - Find the `scan-receipt` request
   - Click on it
   - Check the **Response** tab to see the actual error

---

## Quick Log Commands

**View all logs:**
```powershell
Get-Content storage\logs\laravel.log
```

**View last 50 lines:**
```powershell
Get-Content storage\logs\laravel.log -Tail 50
```

**Search for errors:**
```powershell
Get-Content storage\logs\laravel.log | Select-String -Pattern "error|ERROR|Exception"
```

**Search for receipt scanning:**
```powershell
Get-Content storage\logs\laravel.log | Select-String -Pattern "Receipt|Gemini|scanning"
```

---

## What I'm Looking For:

I need to see:
- ✅ "Calling Gemini API for receipt scan" log
- ✅ "Starting receipt scan API call" log  
- ✅ Any error messages from Gemini API
- ✅ "Receipt scanning error" with full details

**This will tell me exactly why it's failing!**

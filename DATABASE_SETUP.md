# AI Personal Tracker - Database Setup Guide

## 🚀 Quick Start Guide

This guide will help you set up the database and run migrations for the AI Personal Tracker application.

---

## 📋 Prerequisites

Before starting, ensure you have:

- ✅ **XAMPP** installed with MySQL running
- ✅ **Laravel Herd** (or PHP 8.2+) configured
- ✅ **Composer** installed
- ✅ **Node.js & NPM** installed
- ✅ **Gemini API Key** (already configured: `AIzaSyBmX9e8OozSX8NAWwOa8094OM-9eNZGY-8`)

---

## 🗄️ Database Setup

### Step 1: Start XAMPP MySQL Server

1. Open **XAMPP Control Panel**
2. Click **Start** for **MySQL**
3. Verify: Open `http://localhost/phpmyadmin`

### Step 2: Create Database

Open **phpMyAdmin** and run:

```sql
CREATE DATABASE ai_personal_tracker 
CHARACTER SET utf8mb4 
COLLATE utf8mb4_unicode_ci;
```

Or via command line:

```bash
mysql -u root -p -e "CREATE DATABASE ai_personal_tracker CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"
```

### Step 3: Configure Environment

The `.env` file has been pre-configured with:

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=ai_personal_tracker
DB_USERNAME=root
DB_PASSWORD=

GEMINI_API_KEY=AIzaSyBmX9e8OozSX8NAWwOa8094OM-9eNZGY-8
GEMINI_BASE_URL=https://generativelanguage.googleapis.com/v1beta
GEMINI_MODEL=gemini-1.5-flash
```

If your MySQL has a password, update `DB_PASSWORD` in `.env`

---

## 🏗️ Run Migrations & Seeders

### Install Dependencies

```bash
composer install
npm install
```

### Generate Application Key

```bash
php artisan key:generate
```

### Run Migrations

```bash
php artisan migrate
```

This will create:
- ✅ `users` table
- ✅ `income_sources` table (lookup)
- ✅ `expense_categories` table (hierarchical)
- ✅ `transactions` table (polymorphic)
- ✅ `tasks` table
- ✅ `ai_logs` table (audit trail)

### Seed Database with Sample Data

```bash
php artisan db:seed
```

This will create:
- 📊 7 income source categories
- 📊 7 parent expense categories + 20+ subcategories
- 👥 3 demo users (john@example.com, jane@example.com, admin@example.com)
- 💰 45 sample transactions (15 per user)
- ✅ 21 sample tasks (7 per user)
- 🤖 24 AI log entries (8 per user)

### Fresh Migration (Optional - Resets Database)

⚠️ **WARNING**: This will delete all existing data!

```bash
php artisan migrate:fresh --seed
```

---

## ✅ Verify Installation

### Check Migration Status

```bash
php artisan migrate:status
```

### Inspect Database Schema

```bash
php artisan db:show
php artisan db:table transactions
php artisan db:table tasks
```

### Test with Tinker

```bash
php artisan tinker
```

```php
// Count records
\App\Models\IncomeSource::count(); // Should return 7
\App\Models\ExpenseCategory::whereNull('parent_id')->count(); // Should return 7
\App\Models\Transaction::count(); // Should return ~45
\App\Models\Task::count(); // Should return ~21
\App\Models\AiLog::count(); // Should return ~24

// Test relationships
$user = \App\Models\User::first();
$user->transactions; // User's transactions
$user->tasks; // User's tasks

// Test polymorphic relationship
$transaction = \App\Models\Transaction::expense()->first();
$transaction->category; // Should return ExpenseCategory instance

exit
```

---

## 👥 Demo User Credentials

| Name       | Email              | Password |
|------------|--------------------|----------|
| John Doe   | john@example.com   | password |
| Jane Smith | jane@example.com   | password |
| Admin User | admin@example.com  | password |

---

## 📊 Database Schema Overview

### **Transactions Table**
```
- id (PK)
- user_id (FK → users)
- type (enum: income|expense)
- amount (DECIMAL 12,2)
- currency (CHAR 3)
- date (DATE)
- category_id (polymorphic)
- category_type (polymorphic)
- description (TEXT)
- meta (JSON) - vendor, location, tax, tip
- timestamps
```

### **Expense Categories** (Hierarchical)
```
Parent Categories:
├── Food & Dining
│   ├── Fast Food
│   ├── Groceries
│   ├── Dining Out
│   └── Coffee & Snacks
├── Transportation
│   ├── Fuel
│   ├── Public Transit
│   ├── Ride Sharing
│   └── Vehicle Maintenance
├── Education
│   ├── Books & Supplies
│   ├── Tuition Fees
│   └── Online Courses
└── (and more...)
```

### **Income Sources**
```
- From Home
- Tuition Refund
- Freelance Work
- Part-time Job
- Investment Returns
- Gift
- Other Income
```

---

## 🧪 Run Tests

### Run All Tests

```bash
php artisan test
```

### Run Specific Test Suite

```bash
# Unit tests only
php artisan test --testsuite=Unit

# Feature tests only
php artisan test --testsuite=Feature

# Specific test file
php artisan test --filter GeminiServiceTest
php artisan test --filter TransactionTest
```

---

## 🔍 Common Troubleshooting

### Issue: "Access denied for user 'root'@'localhost'"

**Solution**: Update `.env` with correct MySQL credentials:
```env
DB_USERNAME=root
DB_PASSWORD=your_password_here
```

### Issue: "Database 'ai_personal_tracker' doesn't exist"

**Solution**: Create the database manually:
```bash
mysql -u root -p -e "CREATE DATABASE ai_personal_tracker;"
```

### Issue: Migration errors

**Solution**: Reset migrations:
```bash
php artisan migrate:fresh
```

### Issue: Seeder not working

**Solution**: Run seeders manually:
```bash
php artisan db:seed --class=IncomeSourceSeeder
php artisan db:seed --class=ExpenseCategorySeeder
php artisan db:seed --class=DatabaseSeeder
```

---

## 📁 Project Structure

```
database/
├── migrations/
│   ├── 2025_10_08_000001_create_income_sources_table.php
│   ├── 2025_10_08_000002_create_expense_categories_table.php
│   ├── 2025_10_08_000003_create_transactions_table.php
│   ├── 2025_10_08_000004_create_tasks_table.php
│   └── 2025_10_08_000005_create_ai_logs_table.php
├── factories/
│   ├── IncomeSourceFactory.php
│   ├── ExpenseCategoryFactory.php
│   ├── TransactionFactory.php
│   ├── TaskFactory.php
│   └── AiLogFactory.php
└── seeders/
    ├── IncomeSourceSeeder.php
    ├── ExpenseCategorySeeder.php
    └── DatabaseSeeder.php

app/
├── Models/
│   ├── User.php (updated with relationships)
│   ├── IncomeSource.php
│   ├── ExpenseCategory.php
│   ├── Transaction.php
│   ├── Task.php
│   └── AiLog.php
└── Services/
    └── GeminiService.php

tests/
├── Unit/
│   └── Services/
│       └── GeminiServiceTest.php
└── Feature/
    ├── TransactionTest.php
    └── ExpenseCategoryTest.php
```

---

## 🎯 Next Steps

After successful database setup:

1. ✅ **Test Gemini API Connection**
   ```bash
   php artisan tinker
   >>> $service = new \App\Services\GeminiService();
   >>> $service->healthCheck(); // Should return true
   ```

2. 🎨 **Start Building UI**
   - Create dashboard view
   - Build transaction forms
   - Implement chatbot interface

3. 🔌 **Create API Routes**
   - `/api/finance/chatbot`
   - `/api/tasks/chatbot`
   - RESTful endpoints for CRUD

4. 📊 **Integrate Charts**
   - Install Chart.js
   - Create expense breakdown pie chart
   - Income vs Expense trends

5. 🚀 **Deploy with Herd**
   ```bash
   herd link
   herd open
   ```

---

## 📚 API Documentation

### GeminiService Methods

```php
// Parse finance text
$result = $geminiService->parseFinanceText('spent 25 on coffee at Starbucks');
// Returns: ['type' => 'expense', 'amount' => 25, 'category' => 'coffee_snacks', ...]

// Parse task text
$result = $geminiService->parseTaskText('remind me to call mom tomorrow at 3pm');
// Returns: ['title' => 'Call mom', 'due_date' => '2025-10-09 15:00:00', ...]

// Health check
$isHealthy = $geminiService->healthCheck();
// Returns: true/false
```

---

## 📝 Notes

- All monetary amounts use `DECIMAL(12,2)` for precision
- Transactions use polymorphic relationships for flexible categorization
- AI logs are never deleted (audit trail for model training)
- Passwords for demo users are hashed with bcrypt
- Cache driver can be changed to Redis for production

---

## 🆘 Support

If you encounter any issues:

1. Check Laravel logs: `storage/logs/laravel.log`
2. Enable query logging in `.env`: `DB_LOG_QUERIES=true`
3. Run `php artisan config:clear` and `php artisan cache:clear`

---

**🎉 You're all set! Happy coding!**

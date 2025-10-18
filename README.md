# 💰 Persona - AI Personal Tracker

> **An intelligent finance and task management system powered by Google Gemini AI**

[![Laravel](https://img.shields.io/badge/Laravel-11.x-FF2D20?style=flat&logo=laravel)](https://laravel.com)
[![PHP](https://img.shields.io/badge/PHP-8.2+-777BB4?style=flat&logo=php)](https://php.net)
[![Gemini AI](https://img.shields.io/badge/Gemini-AI%20Powered-4285F4?style=flat&logo=google)](https://ai.google.dev)
[![Tailwind CSS](https://img.shields.io/badge/Tailwind-CSS-38B2AC?style=flat&logo=tailwind-css)](https://tailwindcss.com)

---

## 🌟 Overview

**Persona** is a modern, AI-powered personal tracking application that helps you manage your finances and tasks effortlessly. Using natural language processing with Google Gemini AI, you can simply chat with the assistant to log transactions, track expenses, and manage your daily tasks.

### ✨ Key Features

- 🤖 **AI-Powered Chatbot** - Natural language transaction logging with Google Gemini
- 📊 **Interactive Dashboard** - Real-time financial overview with beautiful charts
- � **Monthly Budget Tracking** - Set budgets, track spending, get smart insights
- 📈 **Budget vs Actual Analysis** - Visual progress bars and spending recommendations
- �💳 **Dual Input Modes** - Chat with AI or use manual entry forms
- 📈 **Smart Analytics** - Expense breakdown, savings rate, and trend analysis
- 🔒 **Secure & Private** - User-specific data with policy-based authorization
- 🌍 **Multi-Currency Support** - Track expenses in BDT and USD
- 📱 **Responsive Design** - Works seamlessly on desktop, tablet, and mobile
- ✅ **Comprehensive Testing** - 35+ automated tests with 85%+ coverage

---

## 🚀 Quick Start

### Prerequisites

- PHP 8.2 or higher
- Composer
- MySQL 8.0+
- Node.js & NPM
- Google Gemini API Key ([Get one here](https://ai.google.dev))

### Installation

1. **Clone the repository**
```bash
git clone https://github.com/ill-soul077/Persona.git
cd Persona
```

2. **Install dependencies**
```bash
composer install
npm install
```

3. **Environment setup**
```bash
cp .env.example .env
php artisan key:generate
```

4. **Configure database**
```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=ai_personal_tracker
DB_USERNAME=root
DB_PASSWORD=
```

5. **Add Gemini API key**
```env
GEMINI_API_KEY=your_gemini_api_key_here
```

6. **Run migrations and seed database**
```bash
php artisan migrate:fresh --seed
```

7. **Build assets**
```bash
npm run build
```

8. **Start the server**
```bash
php artisan serve
```

9. **Visit the application**
```
http://localhost:8000
```

### Demo Credentials
```
Email: john@example.com
Password: password
```

---

## 💡 Features Deep Dive

### 🎯 Monthly Budget Management

Set and track monthly spending budgets with real-time insights:

**Budget Setup:**
- Set monthly budget amounts in your preferred currency
- Apply budget settings to future months (up to 12 months ahead)
- Add notes and context to your budgets
- Edit or delete budgets anytime

**Budget Tracking:**
- **Visual Progress Bar** - Color-coded indicators (green/yellow/red)
  - 🟢 Green: Under 80% spent (On Track)
  - 🟡 Yellow: 80-100% spent (Near Limit)
  - 🔴 Red: Over 100% spent (Over Budget)
- **Real-time Statistics:**
  - Total budget amount
  - Amount spent this month
  - Remaining budget
  - Percentage used
  - Days remaining in month

**Smart Insights & Recommendations:**
Access `/finance/budget/insights` for detailed analytics:
- Daily budget calculation
- Spending variance (expected vs actual)
- Spending pace analysis
- Personalized recommendations based on your habits:
  - Spending on track
  - Approaching budget limit
  - Budget exceeded with corrective actions
  - Under budget with optimization tips

**Technical Implementation:**
- Database: `budgets` table with unique constraint per user/month
- Model: `Budget.php` with computed attributes and business logic
- Controller: `BudgetController.php` with CRUD + insights API
- UI: Reusable `budget-progress.blade.php` component with Alpine.js
- Routes: RESTful endpoints under `/finance/budget`

### 🤖 AI-Powered Features

**Natural Language Processing:**

The AI chatbot understands your natural language input to log transactions:

- "I spent 500 BDT on groceries"
- "Received salary of 50000"
- "Paid 300 for dinner last night"

### 📊 Dashboard Analytics

**Financial Overview:**
- Total Income, Expenses, and Balance
- Monthly Budget Progress
- Savings Rate calculation
- Recent transactions list

**Visual Charts:**
- Expense breakdown by category
- Income vs Expense comparison
- Monthly trends and patterns

---

## 📖 How to Use

### Managing Your Monthly Budget

**Create a Budget:**
1. Navigate to Dashboard
2. Click "Set Budget" button (if no budget exists)
3. Enter budget amount (e.g., 50000)
4. Select currency (BDT/USD)
5. Add optional notes
6. Toggle "Apply to next 12 months" if desired
7. Click "Save Budget"

**Monitor Budget Progress:**
- View real-time progress bar with color indicators
- Check percentage used and remaining amount
- Monitor status: "On Track", "Near Limit", or "Over Budget"

**Edit Your Budget:**
1. Click "Edit Budget" button
2. Update amount, currency, or notes
3. Choose to apply changes to future months
4. Save changes

**Get Budget Insights:**
- Navigate to Finance Dashboard
- Click "View Insights" for detailed analytics
- See daily budget, spending variance, and pace
- Read personalized spending recommendations

### Logging Transactions with AI

**Using the Chat Interface:**
1. Navigate to Finance → Transactions
2. Click "Chat with Assistant"
3. Type natural language requests:
   - "I spent 500 BDT on groceries"
   - "Received salary of 50000"
   - "Paid 300 for dinner last night"
4. AI automatically extracts amount, category, and type

**Manual Entry:**
- Use the "Add Transaction" form for precise control
- Select type (income/expense), category, and amount
- Add optional notes and date

---

## 🏗️ Tech Stack

**Backend:**
- Laravel 11 (PHP Framework)
- MySQL (Database)
- Google Gemini API (AI/NLP)

**Frontend:**
- Tailwind CSS (Styling)
- Alpine.js (Reactivity)
- Chart.js (Data Visualization)
- Blade Templates (Views)

**Testing:**
- PHPUnit (Unit & Feature Tests)
- Custom Manual Testing Script

---

## 🧪 Testing

### Run Tests

```bash
# All tests
php artisan test

# With coverage
php artisan test --coverage --min=85
```

### Manual Testing

```bash
php manual-test.php
```

**Test Coverage:**
- 35+ automated tests
- 100+ sample phrases
- 85%+ code coverage

---

## 📚 Documentation

- **[Testing Guide](TESTING_GUIDE.md)** - Comprehensive testing documentation
- **[Testing Quick Reference](TESTING_QUICK_REFERENCE.md)** - Quick commands
- **[Implementation Summary](TESTING_IMPLEMENTATION_SUMMARY.md)** - Overview
- **[Database Setup](DATABASE_SETUP.md)** - Database documentation
- **[Quick Reference](QUICK_REFERENCE.md)** - API reference

---

## 🤝 Contributing

Contributions are welcome! Please:

1. Fork the repository
2. Create a feature branch
3. Commit your changes
4. Push to the branch
5. Open a Pull Request

---

## 📄 License

This project is licensed under the MIT License.

---

## 🙏 Acknowledgments

- [Laravel](https://laravel.com) - The PHP Framework
- [Google Gemini](https://ai.google.dev) - AI/NLP API
- [Tailwind CSS](https://tailwindcss.com) - Utility-first CSS
- [Chart.js](https://www.chartjs.org) - JavaScript charting
- [Alpine.js](https://alpinejs.dev) - Reactive framework

---

## 👨‍💻 Author

**ill-soul077**
- GitHub: [@ill-soul077](https://github.com/ill-soul077)

---

<p align="center">Made with ❤️ using Laravel and Google Gemini AI</p>


## Laravel Sponsors

We would like to extend our thanks to the following sponsors for funding Laravel development. If you are interested in becoming a sponsor, please visit the [Laravel Partners program](https://partners.laravel.com).

### Premium Partners

- **[Vehikl](https://vehikl.com)**
- **[Tighten Co.](https://tighten.co)**
- **[Kirschbaum Development Group](https://kirschbaumdevelopment.com)**
- **[64 Robots](https://64robots.com)**
- **[Curotec](https://www.curotec.com/services/technologies/laravel)**
- **[DevSquad](https://devsquad.com/hire-laravel-developers)**
- **[Redberry](https://redberry.international/laravel-development)**
- **[Active Logic](https://activelogic.com)**

## Contributing

Thank you for considering contributing to the Laravel framework! The contribution guide can be found in the [Laravel documentation](https://laravel.com/docs/contributions).

## Code of Conduct

In order to ensure that the Laravel community is welcoming to all, please review and abide by the [Code of Conduct](https://laravel.com/docs/contributions#code-of-conduct).

## Security Vulnerabilities

If you discover a security vulnerability within Laravel, please send an e-mail to Taylor Otwell via [taylor@laravel.com](mailto:taylor@laravel.com). All security vulnerabilities will be promptly addressed.

## License

The Laravel framework is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).

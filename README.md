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
- 💳 **Dual Input Modes** - Chat with AI or use manual entry forms
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

## 💡 Usage

### Using the AI Chatbot

Simply click the chatbot button and type naturally:

```
"I spent 500 taka on groceries at Agora"
"Received salary of 50000 BDT today"
"Paid 1200 for electricity bill"
"Got paid $1500 for freelance work"
```

The AI will:
- ✅ Extract amount, currency, category, and vendor
- ✅ Detect transaction type (income/expense)
- ✅ Parse dates (yesterday, last week, etc.)
- ✅ Show confidence score
- ✅ Ask for confirmation before saving


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

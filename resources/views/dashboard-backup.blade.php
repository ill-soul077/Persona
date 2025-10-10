<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Persona</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Segoe+UI:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.js"></script>
    <style>
        :root {
            --primary: #0ea5e9;
            --primary-dark: #0369a1;
            --secondary: #06b6d4;
            --accent: #f59e0b;
            --accent-dark: #d97706;
            --text: #f8fafc;
            --text-secondary: #cbd5e1;
            --bg-primary: #0f172a;
            --bg-secondary: #1e293b;
            --bg-glass: rgba(30, 41, 59, 0.7);
            --bg-glass-light: rgba(30, 41, 59, 0.4);
            --shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.5);
            --shadow-glow: 0 0 20px rgba(14, 165, 233, 0.3);
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Segoe UI', system-ui, -apple-system, sans-serif;
        }

        body {
            background: linear-gradient(135deg, var(--bg-primary) 0%, #1e1b4b 100%);
            color: var(--text);
            min-height: 100vh;
            overflow-x: hidden;
            position: relative;
        }

        /* Animated Background */
        .bg-clouds {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            z-index: -1;
            overflow: hidden;
        }

        .cloud {
            position: absolute;
            background: rgba(99, 102, 241, 0.1);
            border-radius: 50%;
            filter: blur(40px);
            animation: float 20s infinite linear;
        }

        .cloud:nth-child(1) {
            width: 300px;
            height: 100px;
            top: 10%;
            left: 5%;
            animation-duration: 25s;
        }

        .cloud:nth-child(2) {
            width: 200px;
            height: 70px;
            top: 30%;
            right: 10%;
            animation-duration: 30s;
            animation-delay: -5s;
        }

        .cloud:nth-child(3) {
            width: 250px;
            height: 80px;
            bottom: 20%;
            left: 15%;
            animation-duration: 35s;
            animation-delay: -10s;
        }

        .cloud:nth-child(4) {
            width: 180px;
            height: 60px;
            bottom: 40%;
            right: 20%;
            animation-duration: 28s;
            animation-delay: -15s;
        }

        @keyframes float {
            0% {
                transform: translateX(0) translateY(0);
            }
            25% {
                transform: translateX(20px) translateY(10px);
            }
            50% {
                transform: translateX(0) translateY(20px);
            }
            75% {
                transform: translateX(-20px) translateY(10px);
            }
            100% {
                transform: translateX(0) translateY(0);
            }
        }

        /* Glassmorphism Cards */
        .glass-card {
            background: var(--bg-glass);
            backdrop-filter: blur(12px);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 16px;
            box-shadow: var(--shadow);
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
        }

        .glass-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.1), transparent);
            transition: left 0.7s ease;
        }

        .glass-card:hover::before {
            left: 100%;
        }

        .glass-card:hover {
            transform: translateY(-5px);
            box-shadow: var(--shadow), var(--shadow-glow);
        }

        /* Navigation */
        .navbar {
            background: var(--bg-glass);
            backdrop-filter: blur(12px);
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
            position: sticky;
            top: 0;
            z-index: 100;
            transition: all 0.3s ease;
        }

        .navbar.scrolled {
            background: var(--bg-glass-light);
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.2);
        }

        .nav-links a {
            position: relative;
            color: var(--text-secondary);
            transition: color 0.3s ease;
        }

        .nav-links a::after {
            content: '';
            position: absolute;
            bottom: -5px;
            left: 0;
            width: 0;
            height: 2px;
            background: var(--primary);
            transition: width 0.3s ease;
        }

        .nav-links a:hover {
            color: var(--text);
        }

        .nav-links a:hover::after {
            width: 100%;
        }

        .nav-links a.active {
            color: var(--text);
        }

        .nav-links a.active::after {
            width: 100%;
        }

        /* Mobile Menu */
        .mobile-menu {
            display: none;
            background: var(--bg-glass);
            backdrop-filter: blur(12px);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 12px;
            position: absolute;
            top: 100%;
            right: 1rem;
            width: 200px;
            box-shadow: var(--shadow);
            transform: translateY(10px);
            opacity: 0;
            visibility: hidden;
            transition: all 0.3s ease;
        }

        .mobile-menu.active {
            display: block;
            transform: translateY(0);
            opacity: 1;
            visibility: visible;
        }

        /* Buttons */
        .btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            padding: 10px 20px;
            border-radius: 10px;
            font-weight: 600;
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
            cursor: pointer;
        }

        .btn-primary {
            background: linear-gradient(135deg, var(--primary) 0%, var(--secondary) 100%);
            color: white;
            border: none;
            box-shadow: 0 4px 15px rgba(14, 165, 233, 0.3);
        }

        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(14, 165, 233, 0.4);
        }

        .btn-secondary {
            background: var(--bg-glass);
            color: var(--text);
            border: 1px solid rgba(255, 255, 255, 0.2);
        }

        .btn-secondary:hover {
            background: rgba(255, 255, 255, 0.1);
            transform: translateY(-2px);
        }

        /* Stats Cards */
        .stat-card {
            background: linear-gradient(135deg, var(--bg-glass) 0%, var(--bg-secondary) 100%);
            backdrop-filter: blur(12px);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 16px;
            padding: 1.5rem;
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
        }

        .stat-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 4px;
            background: linear-gradient(90deg, var(--primary), var(--secondary), var(--accent));
        }

        .stat-card:hover {
            transform: translateY(-5px);
            box-shadow: var(--shadow), var(--shadow-glow);
        }

        /* Animations */
        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        @keyframes slideInRight {
            from {
                opacity: 0;
                transform: translateX(100%);
            }
            to {
                opacity: 1;
                transform: translateX(0);
            }
        }

        @keyframes fadeOut {
            from {
                opacity: 1;
                transform: translateX(0);
            }
            to {
                opacity: 0;
                transform: translateX(100%);
            }
        }

        .animate-fade-in-up {
            animation: fadeInUp 0.6s ease-out forwards;
        }

        .animate-slide-in-right {
            animation: slideInRight 0.5s ease-out forwards;
        }

        .animate-fade-out {
            animation: fadeOut 0.5s ease-out forwards;
        }

        /* Toast Notification */
        .toast {
            position: fixed;
            top: 100px;
            right: 20px;
            background: var(--bg-glass);
            backdrop-filter: blur(12px);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 12px;
            padding: 1rem 1.5rem;
            box-shadow: var(--shadow);
            z-index: 1000;
            display: flex;
            align-items: center;
            gap: 10px;
            transform: translateX(120%);
            transition: transform 0.3s ease;
        }

        .toast.show {
            transform: translateX(0);
        }

        .toast.success {
            border-left: 4px solid #10b981;
        }

        .toast .icon {
            width: 24px;
            height: 24px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            background: #10b981;
            color: white;
        }

        /* Scroll to Top */
        .scroll-to-top {
            position: fixed;
            bottom: 30px;
            right: 30px;
            width: 50px;
            height: 50px;
            background: var(--bg-glass);
            backdrop-filter: blur(12px);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            opacity: 0;
            visibility: hidden;
            transition: all 0.3s ease;
            box-shadow: var(--shadow);
            z-index: 99;
        }

        .scroll-to-top.show {
            opacity: 1;
            visibility: visible;
        }

        .scroll-to-top:hover {
            background: var(--primary);
            transform: translateY(-3px);
        }

        /* Quick Links */
        .quick-link {
            display: flex;
            align-items: center;
            padding: 12px 16px;
            background: rgba(255, 255, 255, 0.05);
            border-radius: 12px;
            color: rgba(255, 255, 255, 0.8);
            text-decoration: none;
            transition: all 0.3s ease;
        }

        .quick-link:hover {
            background: rgba(99, 102, 241, 0.2);
            color: white;
            transform: translateX(5px);
        }

        .quick-link-icon {
            width: 20px;
            height: 20px;
            margin-right: 12px;
            color: rgba(99, 102, 241, 0.8);
        }

        /* Responsive */
        @media (max-width: 768px) {
            .nav-links {
                display: none;
            }

            .mobile-menu-btn {
                display: block;
            }

            .grid-cols-4 {
                grid-template-columns: repeat(2, 1fr);
            }
        }

        @media (max-width: 480px) {
            .grid-cols-4 {
                grid-template-columns: 1fr;
            }

            .toast {
                right: 10px;
                left: 10px;
            }
        }

        /* Action Card Micro-interaction */
        .action-card {
            transition: transform 0.2s ease;
        }

        .action-card:active {
            transform: scale(0.98);
        }

        /* Search Form */
        .search-form {
            position: relative;
        }

        .search-input {
            background: var(--bg-glass);
            backdrop-filter: blur(12px);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 12px;
            padding: 12px 16px 12px 44px;
            color: var(--text);
            width: 100%;
            transition: all 0.3s ease;
        }

        .search-input:focus {
            outline: none;
            border-color: var(--primary);
            box-shadow: 0 0 0 3px rgba(14, 165, 233, 0.1);
        }

        .search-icon {
            position: absolute;
            left: 16px;
            top: 50%;
            transform: translateY(-50%);
            color: var(--text-secondary);
        }

        .spinner {
            width: 20px;
            height: 20px;
            border: 2px solid transparent;
            border-top: 2px solid var(--primary);
            border-radius: 50%;
            animation: spin 1s linear infinite;
            display: none;
        }

        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }

        .search-form.loading .spinner {
            display: block;
        }

        .search-form.loading .search-btn-text {
            display: none;
        }
    </style>
</head>
<body>
    <!-- Animated Background -->
    <div class="bg-clouds">
        <div class="cloud"></div>
        <div class="cloud"></div>
        <div class="cloud"></div>
        <div class="cloud"></div>
    </div>

    <!-- Navigation -->
    <nav class="navbar">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between items-center h-16">
                <div class="flex items-center">
                    <div class="text-xl font-bold bg-gradient-to-r from-[var(--primary)] to-[var(--secondary)] bg-clip-text text-transparent">
                        Persona
                    </div>
                </div>
                
                <div class="hidden md:flex nav-links space-x-8">
                    <a href="#dashboard" class="active">Dashboard</a>
                    <a href="{{ route('finance.dashboard') }}">Finance</a>
                    <a href="{{ route('tasks.index') }}">Tasks</a>
                    <a href="{{ route('chatbot') }}">Chatbot</a>
                    <a href="{{ route('reports.index') }}">Reports</a>
                    <a href="{{ route('settings.index') }}">Settings</a>
                </div>
                
                <div class="flex items-center space-x-4">
                    <button class="md:hidden mobile-menu-btn p-2 rounded-lg bg-[var(--bg-glass)]">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                        </svg>
                    </button>
                </div>
            </div>
        </div>
        
        <!-- Mobile Menu -->
        <div class="mobile-menu">
            <div class="py-2">
                <a href="#dashboard" class="block px-4 py-2 hover:bg-[var(--bg-glass-light)]">Dashboard</a>
                <a href="{{ route('finance.dashboard') }}" class="block px-4 py-2 hover:bg-[var(--bg-glass-light)]">Finance</a>
                <a href="{{ route('tasks.index') }}" class="block px-4 py-2 hover:bg-[var(--bg-glass-light)]">Tasks</a>
                <a href="{{ route('chatbot') }}" class="block px-4 py-2 hover:bg-[var(--bg-glass-light)]">Chatbot</a>
                <a href="{{ route('reports.index') }}" class="block px-4 py-2 hover:bg-[var(--bg-glass-light)]">Reports</a>
                <a href="{{ route('settings.index') }}" class="block px-4 py-2 hover:bg-[var(--bg-glass-light)]">Settings</a>
            </div>
        </div>
    </nav>

    <!-- Toast Notification -->
    <div class="toast success">
        <div class="icon">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
            </svg>
        </div>
        <div>
            <p class="font-semibold">Success!</p>
            <p class="text-sm text-[var(--text-secondary)]">Your changes have been saved</p>
        </div>
    </div>

    <!-- Main Content -->
    <main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <!-- Main Dashboard Content -->
        <div class="space-y-6">
            <!-- Finance Dashboard Header -->
            <div class="glass-card p-6 animate-fade-in-up">
                <div class="flex flex-col md:flex-row justify-between items-start md:items-center">
                    <div>
                        <h1 class="text-2xl font-bold">Finance Dashboard</h1>
                        <p class="text-[var(--text-secondary)] mt-1">Analyze income and expenses at a glance</p>
                    </div>
                    <div class="mt-4 md:mt-0 flex space-x-3">
                        <button class="btn btn-secondary">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                            </svg>
                            Refresh
                        </button>
                        <button class="btn btn-primary">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                            </svg>
                            Add Transaction
                        </button>
                    </div>
                </div>
            </div>

            <!-- Stats Cards -->
            <div class="grid grid-cols-2 md:grid-cols-4 gap-6 animate-slide-up">
                <!-- Total Income -->
                <div class="stat-card">
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="text-[var(--text-secondary)] text-sm font-medium">Total Income</h3>
                        <div class="p-2 rounded-lg bg-green-500/20">
                            <svg class="w-5 h-5 text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6" />
                            </svg>
                        </div>
                    </div>
                    <div class="text-2xl font-bold" id="income-counter">0</div>
                    <div class="text-sm text-[var(--text-secondary)] mt-2">
                        <span class="text-green-400 font-medium">+2.5%</span> from last month
                    </div>
                </div>

                <!-- Total Expense -->
                <div class="stat-card">
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="text-[var(--text-secondary)] text-sm font-medium">Total Expense</h3>
                        <div class="p-2 rounded-lg bg-red-500/20">
                            <svg class="w-5 h-5 text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 17h8m0 0V9m0 8l-8-8-4 4-6-6" />
                            </svg>
                        </div>
                    </div>
                    <div class="text-2xl font-bold" id="expense-counter">0</div>
                    <div class="text-sm text-[var(--text-secondary)] mt-2">
                        <span class="text-red-400 font-medium">-1.2%</span> from last month
                    </div>
                </div>

                <!-- Balance -->
                <div class="stat-card">
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="text-[var(--text-secondary)] text-sm font-medium">Balance</h3>
                        <div class="p-2 rounded-lg bg-blue-500/20">
                            <svg class="w-5 h-5 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1" />
                            </svg>
                        </div>
                    </div>
                    <div class="text-2xl font-bold {{ $balance >= 0 ? 'text-green-400' : 'text-red-400' }}" id="balance-counter">0</div>
                    <div class="text-sm text-[var(--text-secondary)] mt-2">
                        @php
                            $savingsRate = $monthlyIncome > 0 ? (($monthlyIncome - $monthlyExpenses) / $monthlyIncome) * 100 : 0;
                        @endphp
                        <span class="{{ $savingsRate >= 0 ? 'text-green-400' : 'text-red-400' }} font-medium">
                            {{ number_format($savingsRate, 1) }}%
                        </span> savings rate
                    </div>
                </div>

                <!-- Tasks Today -->
                <div class="stat-card">
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="text-[var(--text-secondary)] text-sm font-medium">Tasks Today</h3>
                        <div class="p-2 rounded-lg bg-purple-500/20">
                            <svg class="w-5 h-5 text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4" />
                            </svg>
                        </div>
                    </div>
                    <div class="text-2xl font-bold" id="tasks-counter">0</div>
                    <div class="text-sm text-[var(--text-secondary)] mt-2">
                        <span class="text-green-400 font-medium">{{ $tasksDueToday > 0 ? 'Active' : 'Clear' }}</span> for today
                    </div>
                </div>
            </div>

            <!-- Main Content Grid -->
            <div class="grid grid-cols-1 lg:grid-cols-4 gap-6">
                <!-- Sidebar / Quick Links -->
                <div class="lg:col-span-1 space-y-6">
                    <!-- Quick Links -->
                    <div class="glass-card p-6 animate-slide-up-delay">
                        <h3 class="text-lg font-semibold mb-4">Quick links</h3>
                        <div class="space-y-3">
                            <a href="{{ route('finance.transactions.create') }}" class="quick-link action-card">
                                <svg class="quick-link-icon w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                                </svg>
                                <span>Add Transaction</span>
                            </a>
                            <a href="{{ route('finance.transactions.index') }}" class="quick-link action-card">
                                <svg class="quick-link-icon w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                                </svg>
                                <span>All Transactions</span>
                            </a>
                            <a href="{{ route('tasks.create') }}" class="quick-link action-card">
                                <svg class="quick-link-icon w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                                </svg>
                                <span>Add Task</span>
                            </a>
                            <a href="{{ route('tasks.index') }}" class="quick-link action-card">
                                <svg class="quick-link-icon w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4" />
                                </svg>
                                <span>My Tasks</span>
                            </a>
                            <a href="{{ route('chatbot') }}" class="quick-link action-card">
                                <svg class="quick-link-icon w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z" />
                                </svg>
                                <span>AI Chatbot</span>
                            </a>
                            <a href="{{ route('finance.reports') }}" class="quick-link action-card">
                                <svg class="quick-link-icon w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                                </svg>
                                <span>Reports</span>
                            </a>
                            <a href="{{ route('profile.show') }}" class="quick-link action-card">
                                <svg class="quick-link-icon w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" />
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                </svg>
                                <span>Settings</span>
                            </a>
                        </div>
                    </div>
                </div>

                <!-- Main Dashboard Content -->
                <div class="lg:col-span-3 space-y-6">
                    <!-- Charts Row -->
                    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 animate-slide-up-delay">
                        <!-- Expense Distribution Chart -->
                        <div class="glass-card p-6">
                            <h3 class="text-lg font-semibold mb-4">Expense Distribution</h3>
                            <div class="relative h-64">
                                <canvas id="expenseDistributionChart" width="400" height="200"></canvas>
                            </div>
                        </div>

                        <!-- 7-Day Trend Chart -->
                        <div class="glass-card p-6">
                            <h3 class="text-lg font-semibold mb-4">7-Day Trend</h3>
                            <div class="relative h-64">
                                <canvas id="weeklyTrendChart" width="400" height="200"></canvas>
                            </div>
                        </div>
                    </div>

                    <!-- Recent Activity -->
                    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 animate-fade-in-delay">
                        <!-- Recent Transactions -->
                        <div class="glass-card p-6">
                            <div class="flex items-center justify-between mb-6">
                                <h3 class="text-lg font-semibold">Recent Transactions</h3>
                                <a href="{{ route('finance.transactions.index') }}" class="text-sm text-[var(--primary)] hover:text-[var(--secondary)] transition-colors flex items-center">
                                    View All <svg class="w-4 h-4 ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path></svg>
                                </a>
                            </div>
                            <div class="space-y-4">
                                @forelse($recentTransactions as $transaction)
                                    <div class="flex items-center justify-between p-3 rounded-xl bg-[var(--bg-glass-light)] hover:bg-[var(--bg-glass)] transition-all duration-200 group action-card">
                                        <div class="flex items-center">
                                            <div class="flex-shrink-0 mr-3">
                                                @if($transaction->type === 'expense')
                                                    <div class="p-2 rounded-lg bg-red-500/20 group-hover:scale-110 transition-transform">
                                                        <svg class="w-5 h-5 text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 12H4" />
                                                        </svg>
                                                    </div>
                                                @else
                                                    <div class="p-2 rounded-lg bg-green-500/20 group-hover:scale-110 transition-transform">
                                                        <svg class="w-5 h-5 text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                                                        </svg>
                                                    </div>
                                                @endif
                                            </div>
                                            <div>
                                                <p class="text-sm font-medium text-white">{{ $transaction->description }}</p>
                                                <p class="text-xs text-[var(--text-secondary)]">{{ $transaction->category->name ?? 'N/A' }}</p>
                                            </div>
                                        </div>
                                        <div class="text-sm font-semibold {{ $transaction->type === 'expense' ? 'text-red-400' : 'text-green-400' }}">
                                            {{ $transaction->type === 'expense' ? '-' : '+' }}${{ number_format($transaction->amount, 2) }}
                                        </div>
                                    </div>
                                @empty
                                    <div class="text-center py-8">
                                        <svg class="w-12 h-12 mx-auto text-[var(--text-secondary)]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                        </svg>
                                        <p class="mt-2 text-sm text-[var(--text-secondary)]">No recent transactions</p>
                                    </div>
                                @endforelse
                            </div>
                        </div>

                        <!-- Recent Tasks -->
                        <div class="glass-card p-6">
                            <div class="flex items-center justify-between mb-6">
                                <h3 class="text-lg font-semibold">Recent Tasks</h3>
                                <a href="{{ route('tasks.index') }}" class="text-sm text-[var(--primary)] hover:text-[var(--secondary)] transition-colors flex items-center">
                                    View All <svg class="w-4 h-4 ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path></svg>
                                </a>
                            </div>
                            <div class="space-y-4">
                                @forelse($recentTasks as $task)
                                    <div class="flex items-center justify-between p-3 rounded-xl bg-[var(--bg-glass-light)] hover:bg-[var(--bg-glass)] transition-all duration-200 group action-card">
                                        <div class="flex items-center">
                                            <div class="flex-shrink-0 mr-3">
                                                @if($task->status === 'completed')
                                                    <div class="p-2 rounded-lg bg-green-500/20 group-hover:scale-110 transition-transform">
                                                        <svg class="w-5 h-5 text-green-400" fill="currentColor" viewBox="0 0 20 20">
                                                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                                                        </svg>
                                                    </div>
                                                @else
                                                    <div class="p-2 rounded-lg bg-[var(--bg-glass)] group-hover:scale-110 transition-transform">
                                                        <svg class="w-5 h-5 text-[var(--text-secondary)]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                                        </svg>
                                                    </div>
                                                @endif
                                            </div>
                                            <div>
                                                <p class="text-sm font-medium text-white">{{ $task->title }}</p>
                                                <p class="text-xs text-[var(--text-secondary)]">
                                                    Due: {{ $task->due_date ? $task->due_date->format('M j, Y') : 'No due date' }}
                                                </p>
                                            </div>
                                        </div>
                                        <div class="text-xs px-2 py-1 rounded-full font-medium
                                            @if($task->priority === 'high') bg-red-500/20 text-red-400
                                            @elseif($task->priority === 'medium') bg-yellow-500/20 text-yellow-400
                                            @else bg-[var(--bg-glass)] text-[var(--text-secondary)]
                                            @endif">
                                            {{ ucfirst($task->priority) }}
                                        </div>
                                    </div>
                                @empty
                                    <div class="text-center py-8">
                                        <svg class="w-12 h-12 mx-auto text-[var(--text-secondary)]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                                        </svg>
                                        <p class="mt-2 text-sm text-[var(--text-secondary)]">No recent tasks</p>
                                    </div>
                                @endforelse
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <!-- Scroll to Top Button -->
    <div class="scroll-to-top">
        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 10l7-7m0 0l7 7m-7-7v18" />
        </svg>
    </div>

    <script>
        // Mobile Menu Toggle
        document.addEventListener('DOMContentLoaded', function() {
            const mobileMenuBtn = document.querySelector('.mobile-menu-btn');
            const mobileMenu = document.querySelector('.mobile-menu');
            
            if (mobileMenuBtn && mobileMenu) {
                mobileMenuBtn.addEventListener('click', function() {
                    mobileMenu.classList.toggle('active');
                });
                
                // Close mobile menu when clicking outside
                document.addEventListener('click', function(event) {
                    if (!mobileMenu.contains(event.target) && !mobileMenuBtn.contains(event.target)) {
                        mobileMenu.classList.remove('active');
                    }
                });
            }
            
            // Navbar Scroll Effect
            const navbar = document.querySelector('.navbar');
            window.addEventListener('scroll', function() {
                if (window.scrollY > 100) {
                    navbar.classList.add('scrolled');
                } else {
                    navbar.classList.remove('scrolled');
                }
            });
            
            // Smooth Scrolling for Hash Links
            document.querySelectorAll('a[href^="#"]').forEach(anchor => {
                anchor.addEventListener('click', function(e) {
                    e.preventDefault();
                    const target = document.querySelector(this.getAttribute('href'));
                    if (target) {
                        target.scrollIntoView({
                            behavior: 'smooth',
                            block: 'start'
                        });
                    }
                });
            });
            
            // Action Card Micro-interaction
            document.querySelectorAll('.action-card').forEach(card => {
                card.addEventListener('click', function() {
                    this.style.transform = 'scale(0.98)';
                    setTimeout(() => {
                        this.style.transform = '';
                    }, 150);
                });
            });
            
            // Stats Counter Animation
            function animateValue(element, start, end, duration, suffix = '') {
                const range = end - start;
                const increment = end > start ? 1 : -1;
                const stepTime = Math.abs(Math.floor(duration / range));
                let current = start;
                
                const timer = setInterval(function() {
                    current += increment;
                    element.textContent = current.toLocaleString() + suffix;
                    if (current === end) {
                        clearInterval(timer);
                    }
                }, stepTime);
            }
            
            // Initialize counters when they come into view
            const incomeCounter = document.getElementById('income-counter');
            const expenseCounter = document.getElementById('expense-counter');
            const balanceCounter = document.getElementById('balance-counter');
            const tasksCounter = document.getElementById('tasks-counter');
            
            const observer = new IntersectionObserver((entries) => {
                entries.forEach(entry => {
                    if (entry.isIntersecting) {
                        // Animate income counter
                        animateValue(incomeCounter, 0, {{ $monthlyIncome }}, 1500);
                        
                        // Animate expense counter
                        animateValue(expenseCounter, 0, {{ $monthlyExpenses }}, 1500);
                        
                        // Animate balance counter
                        animateValue(balanceCounter, 0, {{ $balance }}, 1500);
                        
                        // Animate tasks counter
                        animateValue(tasksCounter, 0, {{ $tasksDueToday }}, 1000);
                        
                        observer.unobserve(entry.target);
                    }
                });
            }, { threshold: 0.5 });
            
            if (incomeCounter) observer.observe(incomeCounter);
            
            // Scroll-to-top Button
            const scrollToTopBtn = document.querySelector('.scroll-to-top');
            window.addEventListener('scroll', function() {
                if (window.scrollY > 300) {
                    scrollToTopBtn.classList.add('show');
                } else {
                    scrollToTopBtn.classList.remove('show');
                }
            });
            
            scrollToTopBtn.addEventListener('click', function() {
                window.scrollTo({
                    top: 0,
                    behavior: 'smooth'
                });
            });
            
            // Toast Notification
            function showToast(message) {
                const toast = document.querySelector('.toast');
                const toastMessage = toast.querySelector('p:last-child');
                
                toastMessage.textContent = message;
                toast.classList.add('show');
                
                setTimeout(() => {
                    toast.classList.remove('show');
                }, 3000);
            }
            
            // Show initial toast after page load
            setTimeout(() => {
                showToast('Welcome to Persona!');
            }, 1000);
            
            // Initialize Charts
            setTimeout(initializeCharts, 500);
        });
        
        function initializeCharts() {
            // Expense Distribution Pie Chart
            const expenseData = @json($expenseDistribution);
            const expenseCtx = document.getElementById('expenseDistributionChart').getContext('2d');
            
            new Chart(expenseCtx, {
                type: 'doughnut',
                data: {
                    labels: expenseData.map(item => item.label),
                    datasets: [{
                        data: expenseData.map(item => item.value),
                        backgroundColor: [
                            'rgba(14, 165, 233, 0.8)',
                            'rgba(6, 182, 212, 0.8)',
                            'rgba(139, 92, 246, 0.8)',
                            'rgba(245, 158, 11, 0.8)',
                            'rgba(16, 185, 129, 0.8)'
                        ],
                        borderWidth: 0,
                        hoverOffset: 15
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            position: 'bottom',
                            labels: {
                                padding: 20,
                                usePointStyle: true,
                                pointStyle: 'circle',
                                font: {
                                    size: 11,
                                    color: 'rgb(248, 250, 252)'
                                }
                            }
                        },
                        tooltip: {
                            callbacks: {
                                label: function(context) {
                                    const label = context.label || '';
                                    const value = context.parsed || 0;
                                    const item = expenseData[context.dataIndex];
                                    return label + ': $' + value.toFixed(2) + ' (' + item.percentage + '%)';
                                }
                            }
                        }
                    },
                    animation: {
                        animateScale: true,
                        animateRotate: true,
                        duration: 1000,
                        easing: 'easeOutQuart'
                    }
                }
            });

            // 7-Day Trend Line Chart
            const trendData = @json($weeklyTrend);
            const trendCtx = document.getElementById('weeklyTrendChart').getContext('2d');
            
            // Create gradients for the trend lines
            const expenseGradient = trendCtx.createLinearGradient(0, 0, 0, 200);
            expenseGradient.addColorStop(0, 'rgba(239, 68, 68, 0.4)');
            expenseGradient.addColorStop(1, 'rgba(239, 68, 68, 0.05)');
            
            const incomeGradient = trendCtx.createLinearGradient(0, 0, 0, 200);
            incomeGradient.addColorStop(0, 'rgba(16, 185, 129, 0.4)');
            incomeGradient.addColorStop(1, 'rgba(16, 185, 129, 0.05)');
            
            new Chart(trendCtx, {
                type: 'line',
                data: {
                    labels: trendData.labels,
                    datasets: [
                        {
                            label: 'Expenses',
                            data: trendData.expenses,
                            borderColor: '#EF4444',
                            backgroundColor: expenseGradient,
                            borderWidth: 3,
                            fill: true,
                            tension: 0.4,
                            pointBackgroundColor: '#EF4444',
                            pointBorderColor: '#FFFFFF',
                            pointBorderWidth: 2,
                            pointRadius: 5,
                            pointHoverRadius: 7
                        },
                        {
                            label: 'Income',
                            data: trendData.income,
                            borderColor: '#10B981',
                            backgroundColor: incomeGradient,
                            borderWidth: 3,
                            fill: true,
                            tension: 0.4,
                            pointBackgroundColor: '#10B981',
                            pointBorderColor: '#FFFFFF',
                            pointBorderWidth: 2,
                            pointRadius: 5,
                            pointHoverRadius: 7
                        }
                    ]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            display: true,
                            position: 'top',
                            labels: {
                                padding: 15,
                                usePointStyle: true,
                                pointStyle: 'circle',
                                color: 'rgb(248, 250, 252)'
                            }
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            grid: {
                                drawBorder: false,
                                color: 'rgba(255, 255, 255, 0.1)'
                            },
                            ticks: {
                                color: 'rgba(255, 255, 255, 0.7)',
                                callback: function(value) {
                                    return '$' + value.toFixed(0);
                                }
                            }
                        },
                        x: {
                            grid: {
                                display: false
                            },
                            ticks: {
                                color: 'rgba(255, 255, 255, 0.7)'
                            }
                        }
                    },
                    animation: {
                        duration: 1000,
                        easing: 'easeOutQuart'
                    }
                }
            });
        }
    </script>
</body>
</html>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Dashboard') - Persona</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Segoe+UI:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    @yield('additional-scripts')
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
            padding-top: 80px; /* Add padding to account for fixed navigation */
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
            background: linear-gradient(135deg, rgba(255, 255, 255, 0.1), rgba(255, 255, 255, 0.05));
            border-radius: 50px;
            opacity: 0.3;
            animation: float 20s infinite linear;
        }

        .cloud:nth-child(1) {
            width: 100px;
            height: 50px;
            top: 20%;
            left: -100px;
            animation-duration: 25s;
        }

        .cloud:nth-child(2) {
            width: 150px;
            height: 75px;
            top: 60%;
            left: -150px;
            animation-duration: 30s;
            animation-delay: -10s;
        }

        .cloud:nth-child(3) {
            width: 80px;
            height: 40px;
            top: 80%;
            left: -80px;
            animation-duration: 35s;
            animation-delay: -20s;
        }

        @keyframes float {
            from { transform: translateX(0); }
            to { transform: translateX(calc(100vw + 200px)); }
        }

        /* Glass Morphism Components */
        .glass-nav {
            background: var(--bg-glass);
            backdrop-filter: blur(20px);
            border: 1px solid rgba(255, 255, 255, 0.1);
            box-shadow: var(--shadow);
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            z-index: 1000;
            width: 100%;
        }

        .glass-card {
            background: var(--bg-glass);
            backdrop-filter: blur(20px);
            border: 1px solid rgba(255, 255, 255, 0.1);
            box-shadow: var(--shadow);
            transition: all 0.3s ease;
        }

        .glass-card:hover {
            background: var(--bg-glass-light);
            box-shadow: var(--shadow-glow);
            transform: translateY(-2px);
        }

        .glass-button {
            background: linear-gradient(135deg, var(--primary), var(--secondary));
            backdrop-filter: blur(20px);
            border: 1px solid rgba(255, 255, 255, 0.2);
            transition: all 0.3s ease;
        }

        .glass-button:hover {
            background: linear-gradient(135deg, var(--primary-dark), var(--primary));
            box-shadow: var(--shadow-glow);
            transform: translateY(-2px);
        }

        /* Animations */
        .animate-fade-in {
            animation: fadeIn 0.6s ease-out forwards;
        }

        .animate-slide-up {
            animation: slideUp 0.8s ease-out forwards;
        }

        .animate-bounce-in {
            animation: bounceIn 1s ease-out forwards;
        }

        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }

        @keyframes slideUp {
            from { opacity: 0; transform: translateY(40px); }
            to { opacity: 1; transform: translateY(0); }
        }

        @keyframes bounceIn {
            0% { opacity: 0; transform: scale(0.3); }
            50% { opacity: 1; transform: scale(1.05); }
            70% { transform: scale(0.9); }
            100% { opacity: 1; transform: scale(1); }
        }

        /* Custom scrollbar */
        ::-webkit-scrollbar {
            width: 8px;
        }

        ::-webkit-scrollbar-track {
            background: var(--bg-secondary);
        }

        ::-webkit-scrollbar-thumb {
            background: var(--primary);
            border-radius: 4px;
        }

        ::-webkit-scrollbar-thumb:hover {
            background: var(--primary-dark);
        }

        /* Mobile menu styles */
        .mobile-menu {
            display: none;
        }

        .mobile-menu.active {
            display: block;
        }

        /* Logout button special styling */
        .logout-btn {
            position: relative;
            overflow: hidden;
        }

        .logout-btn::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.1), transparent);
            transition: left 0.6s ease;
        }

        .logout-btn:hover::before {
            left: 100%;
        }

        .logout-btn:hover {
            background: linear-gradient(135deg, rgba(239, 68, 68, 0.2), rgba(220, 38, 38, 0.3)) !important;
            border-color: rgba(239, 68, 68, 0.5) !important;
            transform: translateY(-1px);
            box-shadow: 0 10px 25px -5px rgba(239, 68, 68, 0.2), 0 8px 10px -6px rgba(239, 68, 68, 0.1);
        }

        /* User profile avatar animation */
        .user-avatar {
            background: linear-gradient(135deg, #3b82f6, #06b6d4);
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
        }

        .user-avatar::before {
            content: '';
            position: absolute;
            top: -2px;
            left: -2px;
            right: -2px;
            bottom: -2px;
            background: linear-gradient(45deg, #3b82f6, #06b6d4, #8b5cf6, #3b82f6);
            border-radius: inherit;
            z-index: -1;
            animation: rotate 3s linear infinite;
            opacity: 0;
            transition: opacity 0.3s ease;
        }

        .user-avatar:hover::before {
            opacity: 1;
        }

        .user-avatar:hover {
            transform: scale(1.1);
        }

        @keyframes rotate {
            from { transform: rotate(0deg); }
            to { transform: rotate(360deg); }
        }

        /* Dropdown animation improvements */
        .dropdown-enter {
            animation: dropdownEnter 0.2s ease-out forwards;
        }

        .dropdown-leave {
            animation: dropdownLeave 0.15s ease-in forwards;
        }

        @keyframes dropdownEnter {
            from {
                opacity: 0;
                transform: scale(0.95) translateY(-10px);
            }
            to {
                opacity: 1;
                transform: scale(1) translateY(0);
            }
        }

        @keyframes dropdownLeave {
            from {
                opacity: 1;
                transform: scale(1) translateY(0);
            }
            to {
                opacity: 0;
                transform: scale(0.95) translateY(-10px);
            }
        }

        @yield('additional-styles')
    </style>
</head>
<body>
    <!-- Animated Background -->
    <div class="bg-clouds">
        <div class="cloud"></div>
        <div class="cloud"></div>
        <div class="cloud"></div>
    </div>

    <!-- Navigation Bar -->
    <nav class="glass-nav animate-fade-in">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between items-center py-4">
                <div class="flex items-center space-x-4">
                    <div class="text-2xl font-bold bg-gradient-to-r from-blue-400 to-cyan-400 bg-clip-text text-transparent">
                        @yield('page-icon', '🏠') @yield('page-title', 'Dashboard')
                    </div>
                </div>
                
                <!-- Navigation Links -->
                <div class="hidden md:flex items-center space-x-6">
                    <a href="{{ route('dashboard') }}" class="text-gray-300 hover:text-white transition-colors flex items-center space-x-2 {{ request()->routeIs('dashboard') ? 'text-white bg-white/20 px-3 py-2 rounded-lg' : '' }}">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>
                        </svg>
                        <span>Dashboard</span>
                    </a>
                    <a href="{{ route('finance.dashboard') }}" class="text-gray-300 hover:text-white transition-colors flex items-center space-x-2 {{ request()->routeIs('finance.*') ? 'text-white bg-white/20 px-3 py-2 rounded-lg' : '' }}">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1"/>
                        </svg>
                        <span>Finance</span>
                    </a>
                    <a href="{{ route('tasks.index') }}" class="text-gray-300 hover:text-white transition-colors flex items-center space-x-2 {{ request()->routeIs('tasks.*') ? 'text-white bg-white/20 px-3 py-2 rounded-lg' : '' }}">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                        </svg>
                        <span>Tasks</span>
                    </a>
                    <a href="{{ url('/chatbot') }}" class="text-gray-300 hover:text-white transition-colors flex items-center space-x-2 {{ request()->is('chatbot') ? 'text-white bg-white/20 px-3 py-2 rounded-lg' : '' }}">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z"/>
                        </svg>
                        <span>Chatbot</span>
                    </a>
                    <a href="{{ route('reports.index') }}" class="text-gray-300 hover:text-white transition-colors flex items-center space-x-2 {{ request()->routeIs('reports.*') ? 'text-white bg-white/20 px-3 py-2 rounded-lg' : '' }}">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                        </svg>
                        <span>Reports</span>
                    </a>
                    <a href="{{ route('settings.index') }}" class="text-gray-300 hover:text-white transition-colors flex items-center space-x-2 {{ request()->routeIs('settings.*') ? 'text-white bg-white/20 px-3 py-2 rounded-lg' : '' }}">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                        </svg>
                        <span>Settings</span>
                    </a>
                    
                    <!-- User Profile & Logout Dropdown -->
                    <div class="relative" x-data="{ open: false }">
                        <!-- User Profile Button -->
                        <button @click="open = !open" class="text-gray-300 hover:text-white transition-colors flex items-center space-x-2 px-3 py-2 rounded-lg hover:bg-white/10">
                            <div class="w-8 h-8 user-avatar rounded-full flex items-center justify-center text-white font-semibold text-sm">
                                @auth
                                    {{ strtoupper(substr(auth()->user()->name, 0, 1)) }}
                                @else
                                    U
                                @endauth
                            </div>
                            <span class="font-medium">
                                @auth
                                    {{ auth()->user()->name }}
                                @else
                                    User
                                @endauth
                            </span>
                            <svg class="w-4 h-4 transition-transform" :class="{'rotate-180': open}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                            </svg>
                        </button>

                        <!-- Dropdown Menu -->
                        <div x-show="open" @click.away="open = false" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 transform scale-95" x-transition:enter-end="opacity-100 transform scale-100" x-transition:leave="transition ease-in duration-75" x-transition:leave-start="opacity-100 transform scale-100" x-transition:leave-end="opacity-0 transform scale-95" class="absolute right-0 mt-2 w-48 glass-card rounded-xl shadow-lg z-50">
                            <div class="p-2">
                                <a href="{{ route('profile.edit') }}" class="flex items-center space-x-3 px-4 py-3 text-gray-300 hover:text-white hover:bg-white/10 rounded-lg transition-colors">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                                    </svg>
                                    <span>Profile</span>
                                </a>
                                <div class="border-t border-white/10 my-2"></div>
                                <form method="POST" action="{{ route('logout') }}" class="w-full" onsubmit="return confirm('Are you sure you want to logout?')">
                                    @csrf
                                    <button type="submit" class="logout-btn flex items-center space-x-3 px-4 py-3 text-red-300 hover:text-red-100 rounded-lg transition-all w-full text-left group border border-transparent">
                                        <svg class="w-5 h-5 group-hover:animate-pulse transition-transform group-hover:scale-110" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
                                        </svg>
                                        <span class="font-medium">Logout</span>
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Action Buttons & Mobile Menu -->
                <div class="flex items-center space-x-3">
                    @yield('action-buttons')
                    
                    <!-- Mobile menu button -->
                    <button id="mobile-menu-btn" class="md:hidden glass-button text-white p-2 rounded-lg">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
                        </svg>
                    </button>
                </div>
            </div>

            <!-- Mobile Navigation Menu -->
            <div id="mobile-menu" class="mobile-menu md:hidden border-t border-white/10 mt-4 pt-4">
                <div class="space-y-2">
                    <a href="{{ route('dashboard') }}" class="block px-4 py-3 text-gray-300 hover:text-white hover:bg-white/10 rounded-lg transition-colors {{ request()->routeIs('dashboard') ? 'text-white bg-white/20' : '' }}">
                        <div class="flex items-center space-x-3">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>
                            </svg>
                            <span>Dashboard</span>
                        </div>
                    </a>
                    <a href="{{ route('finance.dashboard') }}" class="block px-4 py-3 text-gray-300 hover:text-white hover:bg-white/10 rounded-lg transition-colors {{ request()->routeIs('finance.*') ? 'text-white bg-white/20' : '' }}">
                        <div class="flex items-center space-x-3">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1"/>
                            </svg>
                            <span>Finance</span>
                        </div>
                    </a>
                    <a href="{{ route('tasks.index') }}" class="block px-4 py-3 text-gray-300 hover:text-white hover:bg-white/10 rounded-lg transition-colors {{ request()->routeIs('tasks.*') ? 'text-white bg-white/20' : '' }}">
                        <div class="flex items-center space-x-3">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                            </svg>
                            <span>Tasks</span>
                        </div>
                    </a>
                    <a href="{{ url('/chatbot') }}" class="block px-4 py-3 text-gray-300 hover:text-white hover:bg-white/10 rounded-lg transition-colors {{ request()->is('chatbot') ? 'text-white bg-white/20' : '' }}">
                        <div class="flex items-center space-x-3">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z"/>
                            </svg>
                            <span>Chatbot</span>
                        </div>
                    </a>
                    <a href="{{ route('reports.index') }}" class="block px-4 py-3 text-gray-300 hover:text-white hover:bg-white/10 rounded-lg transition-colors {{ request()->routeIs('reports.*') ? 'text-white bg-white/20' : '' }}">
                        <div class="flex items-center space-x-3">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                            </svg>
                            <span>Reports</span>
                        </div>
                    </a>
                    <a href="{{ route('settings.index') }}" class="block px-4 py-3 text-gray-300 hover:text-white hover:bg-white/10 rounded-lg transition-colors {{ request()->routeIs('settings.*') ? 'text-white bg-white/20' : '' }}">
                        <div class="flex items-center space-x-3">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                            </svg>
                            <span>Settings</span>
                        </div>
                    </a>
                    
                    <!-- User Profile & Logout (Mobile) -->
                    <div class="border-t border-white/10 mt-4 pt-4">
                        <div class="px-4 py-3 text-gray-400 text-sm font-medium">
                            @auth
                                Logged in as {{ auth()->user()->name }}
                            @else
                                User Account
                            @endauth
                        </div>
                        <a href="{{ route('profile.edit') }}" class="block px-4 py-3 text-gray-300 hover:text-white hover:bg-white/10 rounded-lg transition-colors">
                            <div class="flex items-center space-x-3">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                                </svg>
                                <span>Profile</span>
                            </div>
                        </a>
                        <form method="POST" action="{{ route('logout') }}" class="w-full" onsubmit="return confirm('Are you sure you want to logout?')">
                            @csrf
                            <button type="submit" class="logout-btn block w-full px-4 py-3 text-red-300 hover:text-red-100 rounded-lg transition-all text-left border border-transparent">
                                <div class="flex items-center space-x-3">
                                    <svg class="w-5 h-5 transition-transform hover:scale-110" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
                                    </svg>
                                    <span class="font-medium">Logout</span>
                                </div>
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </nav>

    <!-- Flash Messages -->
    @if(session('success'))
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 pt-8">
            <div class="glass-card rounded-xl p-4 bg-green-500/20 border-green-400/50 animate-fade-in">
                <div class="flex items-center">
                    <svg class="w-5 h-5 text-green-300 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                    </svg>
                    <span class="text-green-300 font-medium">{{ session('success') }}</span>
                </div>
            </div>
        </div>
    @endif

    @if(session('error'))
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 pt-8">
            <div class="glass-card rounded-xl p-4 bg-red-500/20 border-red-400/50 animate-fade-in">
                <div class="flex items-center">
                    <svg class="w-5 h-5 text-red-300 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                    <span class="text-red-300 font-medium">{{ session('error') }}</span>
                </div>
            </div>
        </div>
    @endif

    <!-- Main Content -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 space-y-8">
        @yield('content')
    </div>

    @yield('modals')

    <!-- Alpine.js for interactive components -->
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>

    <!-- Mobile Menu Toggle Script -->
    <script>
        document.getElementById('mobile-menu-btn').addEventListener('click', function() {
            const mobileMenu = document.getElementById('mobile-menu');
            mobileMenu.classList.toggle('active');
        });

        // Close mobile menu when clicking outside
        document.addEventListener('click', function(e) {
            const mobileMenu = document.getElementById('mobile-menu');
            const mobileMenuBtn = document.getElementById('mobile-menu-btn');
            
            if (!mobileMenu.contains(e.target) && !mobileMenuBtn.contains(e.target)) {
                mobileMenu.classList.remove('active');
            }
        });

        // Logout confirmation functionality (disabled - using browser confirm for now)
        /*
        document.addEventListener('DOMContentLoaded', function() {
            // Custom confirmation dialog code here if needed
        });
        */

        // Simple logout functionality with browser confirm
        // The forms now use onsubmit="return confirm('Are you sure you want to logout?')"
    </script>

    @yield('scripts')
</body>
</html>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Persona - AI-powered personal finance and task management that feels like texting a friend">
    <title>Persona - AI Personal Tracker | Manage Money & Tasks Through Natural Conversation</title>
    
    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    
    <!-- Alpine.js -->
    <script defer src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script>
    
    <style>
        /* Custom Glassmorphism Styles */
        .glass-card {
            background: rgba(255, 255, 255, 0.05);
            backdrop-filter: blur(10px);
            -webkit-backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.1);
        }
        
        .glass-button {
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(10px);
            -webkit-backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.2);
            transition: all 0.3s ease;
        }
        
        .glass-button:hover {
            background: rgba(255, 255, 255, 0.2);
            transform: translateY(-2px);
            box-shadow: 0 10px 25px rgba(59, 130, 246, 0.3);
        }
        
        /* Gradient Background */
        .gradient-bg {
            background: linear-gradient(135deg, #0f172a 0%, #1e293b 50%, #0f172a 100%);
        }
        
        /* Animated Gradient */
        .animated-gradient {
            background: linear-gradient(270deg, #3b82f6, #8b5cf6, #3b82f6);
            background-size: 400% 400%;
            animation: gradientShift 15s ease infinite;
        }
        
        @keyframes gradientShift {
            0% { background-position: 0% 50%; }
            50% { background-position: 100% 50%; }
            100% { background-position: 0% 50%; }
        }
        
        /* Scroll Animations */
        .animate-on-scroll {
            opacity: 0;
            transform: translateY(30px);
            transition: all 0.6s ease-out;
        }
        
        .animate-on-scroll.visible {
            opacity: 1;
            transform: translateY(0);
        }
        
        /* Stagger Children */
        .stagger-children > *:nth-child(1) { transition-delay: 0.1s; }
        .stagger-children > *:nth-child(2) { transition-delay: 0.2s; }
        .stagger-children > *:nth-child(3) { transition-delay: 0.3s; }
        .stagger-children > *:nth-child(4) { transition-delay: 0.4s; }
        .stagger-children > *:nth-child(5) { transition-delay: 0.5s; }
        
        /* Floating Animation */
        @keyframes float {
            0%, 100% { transform: translateY(0px); }
            50% { transform: translateY(-20px); }
        }
        
        .float {
            animation: float 6s ease-in-out infinite;
        }
        
        /* Glow Effect */
        .glow {
            box-shadow: 0 0 20px rgba(59, 130, 246, 0.5);
        }
        
        .glow-text {
            text-shadow: 0 0 20px rgba(59, 130, 246, 0.5);
        }
        
        /* Particles */
        .particle {
            position: absolute;
            background: rgba(255, 255, 255, 0.1);
            border-radius: 50%;
            pointer-events: none;
        }
        
        /* Smooth Scroll */
        html {
            scroll-behavior: smooth;
        }
        
        /* Hide scrollbar */
        .hide-scrollbar::-webkit-scrollbar {
            display: none;
        }
        .hide-scrollbar {
            -ms-overflow-style: none;
            scrollbar-width: none;
        }
        
        /* Custom Logo Animation */
        @keyframes logo-pulse {
            0%, 100% { transform: scale(1); }
            50% { transform: scale(1.05); }
        }
        
        .logo-pulse {
            animation: logo-pulse 3s ease-in-out infinite;
        }
        
        /* Custom Brain Icon Styling */
        .brain-icon {
            filter: drop-shadow(0 0 10px rgba(59, 130, 246, 0.5));
        }

        /* Priority badge colors */
        .priority-low { background: rgba(107, 114, 128, 0.2); color: rgb(156, 163, 175); }
        .priority-medium { background: rgba(245, 158, 11, 0.2); color: rgb(245, 158, 11); }
        .priority-high { background: rgba(249, 115, 22, 0.2); color: rgb(249, 115, 22); }
        .priority-urgent { background: rgba(239, 68, 68, 0.2); color: rgb(239, 68, 68); }

        /* Status badge colors */
        .status-pending { background: rgba(107, 114, 128, 0.2); color: rgb(156, 163, 175); }
        .status-in-progress { background: rgba(59, 130, 246, 0.2); color: rgb(59, 130, 246); }
        .status-completed { background: rgba(16, 185, 129, 0.2); color: rgb(16, 185, 129); }
        .status-cancelled { background: rgba(239, 68, 68, 0.2); color: rgb(239, 68, 68); }

        /* Task card animations */
        @keyframes pulse-gentle {
            0%, 100% { opacity: 1; }
            50% { opacity: 0.8; }
        }
        
        .pulse-gentle {
            animation: pulse-gentle 2s ease-in-out infinite;
        }

        /* Checkbox animation */
        .task-checkbox:checked + .task-label {
            text-decoration: line-through;
            color: rgb(107, 114, 128);
        }
    </style>
</head>
<body class="gradient-bg text-white overflow-x-hidden">
    
    <!-- Navigation -->
    <nav class="fixed top-0 left-0 right-0 z-50 glass-card" x-data="{ mobileMenuOpen: false }">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between items-center h-16">
                <!-- Logo -->
                <div class="flex items-center space-x-3">
                    <!-- New Brain AI Logo -->
                    <svg class="w-8 h-8 text-blue-500 brain-icon logo-pulse" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path d="M12 4C8 4 6 6 6 10C6 12 7 13 8 14C7 15 6 16 6 17C6 19 7 20 9 20C9 21 10 22 12 22C14 22 15 21 15 20C17 20 18 19 18 17C18 16 17 15 16 14C17 13 18 12 18 10C18 6 16 4 12 4Z" fill="currentColor"/>
                        <path d="M9 10C9.55228 10 10 9.55228 10 9C10 8.44772 9.55228 8 9 8C8.44772 8 8 8.44772 8 9C8 9.55228 8.44772 10 9 10Z" fill="#1e293b"/>
                        <path d="M15 10C15.5523 10 16 9.55228 16 9C16 8.44772 15.5523 8 15 8C14.4477 8 14 8.44772 14 9C14 9.55228 14.4477 10 15 10Z" fill="#1e293b"/>
                        <path d="M10 15H14" stroke="#1e293b" stroke-width="2" stroke-linecap="round"/>
                    </svg>
                    <span class="text-2xl font-bold bg-gradient-to-r from-blue-400 to-purple-500 bg-clip-text text-transparent">Persona</span>
                </div>
                
                <!-- Desktop Navigation -->
                <div class="hidden md:flex items-center space-x-8">
                    <a href="#features" class="hover:text-blue-400 transition-colors">Features</a>
                    <a href="#task-management" class="hover:text-blue-400 transition-colors">Tasks</a>
                    <a href="#how-it-works" class="hover:text-blue-400 transition-colors">How It Works</a>
                    <a href="#pricing" class="hover:text-blue-400 transition-colors">Pricing</a>
                    @auth
                        <a href="{{ route('dashboard') }}" class="glass-button px-6 py-2 rounded-xl font-medium">
                            Dashboard
                        </a>
                    @else
                        <a href="{{ route('login') }}" class="glass-button px-6 py-2 rounded-xl font-medium">
                            Login
                        </a>
                        <a href="{{ route('register') }}" class="bg-blue-600 hover:bg-blue-700 px-6 py-2 rounded-xl font-medium transition-colors">
                            Sign Up Free
                        </a>
                    @endauth
                </div>
                
                <!-- Mobile Menu Button -->
                <button @click="mobileMenuOpen = !mobileMenuOpen" class="md:hidden">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path>
                    </svg>
                </button>
            </div>
        </div>
        
        <!-- Mobile Menu -->
        <div x-show="mobileMenuOpen" x-transition class="md:hidden glass-card border-t border-white/10">
            <div class="px-4 py-4 space-y-3">
                <a href="#features" class="block hover:text-blue-400 transition-colors">Features</a>
                <a href="#task-management" class="block hover:text-blue-400 transition-colors">Tasks</a>
                <a href="#how-it-works" class="block hover:text-blue-400 transition-colors">How It Works</a>
                <a href="#pricing" class="block hover:text-blue-400 transition-colors">Pricing</a>
                @auth
                    <a href="{{ route('dashboard') }}" class="block glass-button px-4 py-2 rounded-xl text-center">Dashboard</a>
                @else
                    <a href="{{ route('login') }}" class="block glass-button px-4 py-2 rounded-xl text-center">Login</a>
                    <a href="{{ route('register') }}" class="block bg-blue-600 px-4 py-2 rounded-xl text-center">Sign Up Free</a>
                @endauth
            </div>
        </div>
    </nav>
    
    <!-- Hero Section -->
    <section class="min-h-screen flex items-center justify-center relative overflow-hidden pt-16" id="hero">
        <!-- Floating Particles Background -->
        <div id="particles-container" class="absolute inset-0 overflow-hidden"></div>
        
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 relative z-10">
            <div class="text-center animate-on-scroll">
                <!-- Logo -->
                <div class="flex justify-center mb-6">
                    <!-- New Large Brain AI Logo -->
                    <svg class="w-24 h-24 md:w-32 md:h-32 text-blue-500 brain-icon float" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path d="M12 4C8 4 6 6 6 10C6 12 7 13 8 14C7 15 6 16 6 17C6 19 7 20 9 20C9 21 10 22 12 22C14 22 15 21 15 20C17 20 18 19 18 17C18 16 17 15 16 14C17 13 18 12 18 10C18 6 16 4 12 4Z" fill="currentColor"/>
                        <path d="M9 10C9.55228 10 10 9.55228 10 9C10 8.44772 9.55228 8 9 8C8.44772 8 8 8.44772 8 9C8 9.55228 8.44772 10 9 10Z" fill="#1e293b"/>
                        <path d="M15 10C15.5523 10 16 9.55228 16 9C16 8.44772 15.5523 8 15 8C14.4477 8 14 8.44772 14 9C14 9.55228 14.4477 10 15 10Z" fill="#1e293b"/>
                        <path d="M10 15H14" stroke="#1e293b" stroke-width="2" stroke-linecap="round"/>
                    </svg>
                </div>
                
                <!-- Headline -->
                <h1 class="text-4xl md:text-6xl lg:text-7xl font-bold mb-6 glow-text">
                    Manage Money & Tasks<br/>
                    <span class="bg-gradient-to-r from-blue-400 to-purple-500 bg-clip-text text-transparent">
                        Through Natural Conversation
                    </span>
                </h1>
                
                <!-- Subheadline -->
                <p class="text-xl md:text-2xl text-gray-300 mb-12 max-w-3xl mx-auto">
                    AI-powered personal finance and task management that feels like texting a friend. No more spreadsheets, just smart conversations.
                </p>
                
                <!-- CTA Buttons -->
                <div class="flex flex-col sm:flex-row gap-4 justify-center items-center mb-16">
                    <a href="{{ route('register') }}" class="bg-blue-600 hover:bg-blue-700 px-8 py-4 rounded-xl text-lg font-semibold glow transition-all hover:scale-105">
                        ✨ Start Your Free Trial
                    </a>
                    <a href="#demo" class="glass-button px-8 py-4 rounded-xl text-lg font-semibold hover:scale-105">
                        🎬 Watch Demo Video
                    </a>
                </div>
                
                <!-- Hero Visual -->
                <div class="relative max-w-5xl mx-auto">
                    <div class="glass-card rounded-2xl p-8 float">
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                            <!-- Stat Card 1 -->
                            <div class="glass-card rounded-xl p-6">
                                <div class="text-green-400 text-3xl mb-2">↑ +$5,420</div>
                                <div class="text-gray-400">Total Income</div>
                            </div>
                            <!-- Stat Card 2 -->
                            <div class="glass-card rounded-xl p-6">
                                <div class="text-red-400 text-3xl mb-2">↓ -$3,210</div>
                                <div class="text-gray-400">Total Expenses</div>
                            </div>
                            <!-- Stat Card 3 -->
                            <div class="glass-card rounded-xl p-6">
                                <div class="text-blue-400 text-3xl mb-2">💰 $2,210</div>
                                <div class="text-gray-400">Net Balance</div>
                            </div>
                        </div>
                        
                        <!-- AI Chat Bubble -->
                        <div class="mt-6 glass-card rounded-xl p-4">
                            <div class="flex items-start space-x-3">
                                <div class="text-2xl">🤖</div>
                                <div>
                                    <div class="font-semibold text-purple-400 mb-1">AI Assistant</div>
                                    <div class="text-gray-300">You've spent 64% of your budget. Consider reducing entertainment expenses by $200 to stay on track.</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
    
    <!-- Problem Statement Section -->
    <section class="py-20 relative" id="problem">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="grid md:grid-cols-2 gap-12 items-center">
                <!-- Left: Pain Points -->
                <div class="animate-on-scroll">
                    <h2 class="text-3xl md:text-5xl font-bold mb-8 glow-text">
                        Tired of Manual Budget Spreadsheets?
                    </h2>
                    <div class="space-y-4 stagger-children">
                        <div class="flex items-start space-x-3 animate-on-scroll">
                            <div class="text-red-400 text-2xl">❌</div>
                            <div>
                                <div class="font-semibold text-lg">Filling forms for every expense</div>
                                <div class="text-gray-400">Tedious manual data entry wastes hours</div>
                            </div>
                        </div>
                        <div class="flex items-start space-x-3 animate-on-scroll">
                            <div class="text-red-400 text-2xl">❌</div>
                            <div>
                                <div class="font-semibold text-lg">Forgetting to log transactions</div>
                                <div class="text-gray-400">Missing data leads to inaccurate budgets</div>
                            </div>
                        </div>
                        <div class="flex items-start space-x-3 animate-on-scroll">
                            <div class="text-red-400 text-2xl">❌</div>
                            <div>
                                <div class="font-semibold text-lg">Complex budgeting tools</div>
                                <div class="text-gray-400">Overwhelming interfaces you never use</div>
                            </div>
                        </div>
                        <div class="flex items-start space-x-3 animate-on-scroll">
                            <div class="text-red-400 text-2xl">❌</div>
                            <div>
                                <div class="font-semibold text-lg">No intelligent insights</div>
                                <div class="text-gray-400">Just numbers, no actionable advice</div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Right: Solution -->
                <div class="animate-on-scroll">
                    <div class="glass-card rounded-2xl p-8">
                        <div class="text-center mb-6">
                            <div class="text-4xl mb-2">💬</div>
                            <div class="text-2xl font-bold text-green-400">With Persona</div>
                        </div>
                        <div class="space-y-4">
                            <div class="glass-card rounded-xl p-4">
                                <div class="text-sm text-gray-400 mb-1">You say:</div>
                                <div class="bg-blue-600/20 rounded-lg p-3">"I spent $50 on groceries"</div>
                            </div>
                            <div class="text-center text-2xl">↓</div>
                            <div class="glass-card rounded-xl p-4">
                                <div class="text-sm text-gray-400 mb-1">AI does:</div>
                                <div class="bg-purple-600/20 rounded-lg p-3">✅ Automatically logs expense<br/>✅ Categorizes as "Groceries"<br/>✅ Updates budget tracking</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
    
    <!-- AI Features Showcase -->
    <section class="py-20 relative" id="features">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-16 animate-on-scroll">
                <h2 class="text-4xl md:text-5xl font-bold mb-4 glow-text">
                    Meet Your AI Financial Assistant
                </h2>
                <p class="text-xl text-gray-300 max-w-3xl mx-auto">
                    Powered by Google Gemini 2.5 Flash, Persona understands your money like a personal advisor
                </p>
            </div>
            
            <div class="grid md:grid-cols-3 gap-8 stagger-children">
                <!-- Feature 1: AI Receipt Scanner -->
                <div class="glass-card rounded-2xl p-8 hover:scale-105 transition-all cursor-pointer animate-on-scroll">
                    <div class="text-5xl mb-4">📸</div>
                    <h3 class="text-2xl font-bold mb-3">Snap & Automate</h3>
                    <p class="text-gray-300 mb-6">
                        Upload receipts, AI extracts all details automatically. No more manual typing.
                    </p>
                    <div class="glass-card rounded-xl p-4 text-sm">
                        <div class="text-green-400 mb-2">✓ Merchant detected</div>
                        <div class="text-green-400 mb-2">✓ Amount extracted</div>
                        <div class="text-green-400">✓ Category assigned</div>
                    </div>
                </div>
                
                <!-- Feature 2: Smart Budget Advisor -->
                <div class="glass-card rounded-2xl p-8 hover:scale-105 transition-all cursor-pointer animate-on-scroll">
                    <div class="text-5xl mb-4">🧠</div>
                    <h3 class="text-2xl font-bold mb-3">Intelligent Insights</h3>
                    <p class="text-gray-300 mb-6">
                        Get personalized spending advice and budget forecasts powered by AI.
                    </p>
                    <div class="glass-card rounded-xl p-4 text-sm">
                        <div class="mb-2">Budget Progress:</div>
                        <div class="w-full bg-gray-700 rounded-full h-2 mb-2">
                            <div class="bg-blue-600 h-2 rounded-full" style="width: 70%"></div>
                        </div>
                        <div class="text-yellow-400">⚠️ Reduce dining by $150</div>
                    </div>
                </div>
                
                <!-- Feature 3: Conversational Interface -->
                <div class="glass-card rounded-2xl p-8 hover:scale-105 transition-all cursor-pointer animate-on-scroll">
                    <div class="text-5xl mb-4">💬</div>
                    <h3 class="text-2xl font-bold mb-3">Chat, Don't Type</h3>
                    <p class="text-gray-300 mb-6">
                        Log expenses and manage tasks through natural conversation.
                    </p>
                    <div class="space-y-2">
                        <div class="glass-card rounded-xl p-3 text-sm bg-blue-600/20">
                            "Add $30 coffee meeting"
                        </div>
                        <div class="glass-card rounded-xl p-3 text-sm bg-purple-600/20">
                            ✓ Logged as Entertainment
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
    
    <!-- Task Management Section -->
    <section class="py-20 relative" id="task-management">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-16 animate-on-scroll">
                <h2 class="text-4xl md:text-5xl font-bold mb-4 glow-text">
                    Smart Task Management
                </h2>
                <p class="text-xl text-gray-300 max-w-3xl mx-auto">
                    AI-powered task management with intelligent prioritization, recurring tasks, and natural language input
                </p>
            </div>
            
            <!-- Task Management Features Grid -->
            <div class="grid md:grid-cols-2 gap-8 mb-16 stagger-children">
                <!-- Feature 1: Natural Language Tasks -->
                <div class="glass-card rounded-2xl p-8 animate-on-scroll hover:scale-105 transition-all">
                    <div class="text-5xl mb-4">🗣️</div>
                    <h3 class="text-2xl font-bold mb-3">Natural Language Creation</h3>
                    <p class="text-gray-300 mb-6">
                        Create tasks by simply talking to AI. No forms, no complexity.
                    </p>
                    <div class="space-y-3">
                        <div class="glass-card rounded-xl p-4 bg-blue-600/20">
                            <div class="text-sm text-gray-400 mb-1">You say:</div>
                            <div>"Remind me to submit project by Friday high priority"</div>
                        </div>
                        <div class="text-center text-xl">↓</div>
                        <div class="glass-card rounded-xl p-4 bg-purple-600/20">
                            <div class="text-sm text-gray-400 mb-1">AI creates:</div>
                            <div class="space-y-2">
                                <div>✓ Title: "Submit project"</div>
                                <div>✓ Due: Friday</div>
                                <div>✓ Priority: High</div>
                                <div>✓ Tags: work, deadline</div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Feature 2: Priority System -->
                <div class="glass-card rounded-2xl p-8 animate-on-scroll hover:scale-105 transition-all">
                    <div class="text-5xl mb-4">🎯</div>
                    <h3 class="text-2xl font-bold mb-3">Smart Priority System</h3>
                    <p class="text-gray-300 mb-6">
                        Color-coded priorities help you focus on what matters most.
                    </p>
                    <div class="space-y-3">
                        <div class="flex items-center justify-between p-3 rounded-lg priority-urgent">
                            <span>Urgent - Do immediately</span>
                            <span class="text-xs px-2 py-1 rounded-full bg-red-500/30">Critical</span>
                        </div>
                        <div class="flex items-center justify-between p-3 rounded-lg priority-high">
                            <span>High - Needs attention soon</span>
                            <span class="text-xs px-2 py-1 rounded-full bg-orange-500/30">Important</span>
                        </div>
                        <div class="flex items-center justify-between p-3 rounded-lg priority-medium">
                            <span>Medium - Important but flexible</span>
                            <span class="text-xs px-2 py-1 rounded-full bg-yellow-500/30">Flexible</span>
                        </div>
                        <div class="flex items-center justify-between p-3 rounded-lg priority-low">
                            <span>Low - Can wait</span>
                            <span class="text-xs px-2 py-1 rounded-full bg-gray-500/30">Optional</span>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Task Dashboard Preview -->
            <div class="glass-card rounded-2xl p-8 animate-on-scroll">
                <h3 class="text-2xl font-bold mb-6 text-center">Your Task Dashboard</h3>
                
                <div class="grid md:grid-cols-2 gap-8">
                    <!-- Today's Tasks -->
                    <div class="glass-card rounded-xl p-6">
                        <div class="flex items-center justify-between mb-4">
                            <h4 class="text-lg font-semibold text-blue-400">📅 Today's Tasks</h4>
                            <span class="text-sm text-gray-400">Monday, October 20</span>
                        </div>
                        <div class="space-y-3">
                            <!-- Task 1 -->
                            <div class="flex items-center space-x-3 p-3 rounded-lg hover:bg-white/5 transition-all">
                                <input type="checkbox" class="task-checkbox w-5 h-5 rounded border-white/30 bg-white/10">
                                <label class="task-label flex-1">Morning standup meeting</label>
                                <span class="priority-medium text-xs px-2 py-1 rounded-full">Medium</span>
                            </div>
                            
                            <!-- Task 2 -->
                            <div class="flex items-center space-x-3 p-3 rounded-lg hover:bg-white/5 transition-all">
                                <input type="checkbox" class="task-checkbox w-5 h-5 rounded border-white/30 bg-white/10">
                                <label class="task-label flex-1">Complete project proposal</label>
                                <span class="priority-high text-xs px-2 py-1 rounded-full">High</span>
                            </div>
                            
                            <!-- Task 3 -->
                            <div class="flex items-center space-x-3 p-3 rounded-lg bg-green-500/10 border border-green-500/20">
                                <input type="checkbox" checked class="task-checkbox w-5 h-5 rounded border-white/30 bg-white/10">
                                <label class="task-label flex-1 line-through text-gray-500">Review team submissions</label>
                                <span class="status-completed text-xs px-2 py-1 rounded-full">✓ Done</span>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Tomorrow's Tasks -->
                    <div class="glass-card rounded-xl p-6">
                        <div class="flex items-center justify-between mb-4">
                            <h4 class="text-lg font-semibold text-purple-400">⏭️ Tomorrow's Tasks</h4>
                            <span class="text-sm text-gray-400">Tuesday, October 21</span>
                        </div>
                        <div class="space-y-3">
                            <!-- Task 1 -->
                            <div class="flex items-center space-x-3 p-3 rounded-lg hover:bg-white/5 transition-all">
                                <input type="checkbox" class="task-checkbox w-5 h-5 rounded border-white/30 bg-white/10">
                                <label class="task-label flex-1">Client presentation prep</label>
                                <span class="priority-urgent text-xs px-2 py-1 rounded-full pulse-gentle">Urgent</span>
                            </div>
                            
                            <!-- Task 2 -->
                            <div class="flex items-center space-x-3 p-3 rounded-lg hover:bg-white/5 transition-all">
                                <input type="checkbox" class="task-checkbox w-5 h-5 rounded border-white/30 bg-white/10">
                                <label class="task-label flex-1">Weekly team lunch</label>
                                <span class="priority-low text-xs px-2 py-1 rounded-full">Low</span>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Recurring Tasks Section -->
                <div class="mt-8 glass-card rounded-xl p-6">
                    <h4 class="text-lg font-semibold mb-4 text-green-400">🔄 Recurring Tasks</h4>
                    <div class="grid md:grid-cols-3 gap-4 text-sm">
                        <div class="text-center p-4 rounded-lg bg-blue-500/10">
                            <div class="text-2xl mb-2">📅</div>
                            <div>Weekly Reports</div>
                            <div class="text-gray-400 text-xs">Every Monday</div>
                        </div>
                        <div class="text-center p-4 rounded-lg bg-purple-500/10">
                            <div class="text-2xl mb-2">💰</div>
                            <div>Monthly Invoicing</div>
                            <div class="text-gray-400 text-xs">1st of each month</div>
                        </div>
                        <div class="text-center p-4 rounded-lg bg-green-500/10">
                            <div class="text-2xl mb-2">🔄</div>
                            <div>Database Backup</div>
                            <div class="text-gray-400 text-xs">Every Sunday</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
    
    <!-- Dashboard Preview Section -->
    <section class="py-20 relative" id="demo">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-16 animate-on-scroll">
                <h2 class="text-4xl md:text-5xl font-bold mb-4 glow-text">
                    See Persona In Action
                </h2>
                <p class="text-xl text-gray-300">
                    A glimpse of your future dashboard
                </p>
            </div>
            
            <div class="glass-card rounded-2xl p-8 animate-on-scroll">
                <!-- Dashboard Mockup -->
                <div class="space-y-6">
                    <!-- Stats Row -->
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                        <div class="glass-card rounded-xl p-6 hover:scale-105 transition-all cursor-pointer" title="Real-time income tracking">
                            <div class="text-green-400 text-3xl mb-2">↑ +$5,420</div>
                            <div class="text-gray-400">Total Income</div>
                            <div class="text-xs text-gray-500 mt-2">+12% from last month</div>
                        </div>
                        <div class="glass-card rounded-xl p-6 hover:scale-105 transition-all cursor-pointer" title="Expense monitoring">
                            <div class="text-red-400 text-3xl mb-2">↓ -$3,210</div>
                            <div class="text-gray-400">Total Expenses</div>
                            <div class="text-xs text-gray-500 mt-2">+8% from last month</div>
                        </div>
                        <div class="glass-card rounded-xl p-6 hover:scale-105 transition-all cursor-pointer" title="Net balance calculation">
                            <div class="text-blue-400 text-3xl mb-2">💰 $2,210</div>
                            <div class="text-gray-400">Net Balance</div>
                            <div class="text-xs text-gray-500 mt-2">Current month</div>
                        </div>
                    </div>
                    
                    <!-- Budget & AI Summary -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div class="glass-card rounded-xl p-6">
                            <h3 class="text-lg font-semibold mb-4">Monthly Budget</h3>
                            <div class="mb-4">
                                <div class="flex justify-between text-sm mb-2">
                                    <span>$3,210 / $5,000</span>
                                    <span class="text-yellow-400">64%</span>
                                </div>
                                <div class="w-full bg-gray-700 rounded-full h-3">
                                    <div class="bg-yellow-400 h-3 rounded-full" style="width: 64%"></div>
                                </div>
                            </div>
                            <div class="text-sm text-gray-400">15 days remaining</div>
                        </div>
                        
                        <div class="glass-card rounded-xl p-6">
                            <div class="flex justify-between items-center mb-4">
                                <h3 class="text-lg font-semibold">AI Insights</h3>
                                <button class="glass-button px-3 py-1 rounded-lg text-sm">🔄 Refresh</button>
                            </div>
                            <p class="text-sm text-gray-300">
                                You're spending faster than planned. Consider reducing entertainment ($800) by $200 to stay on budget.
                            </p>
                        </div>
                    </div>
                    
                    <!-- Tasks -->
                    <div class="glass-card rounded-xl p-6">
                        <h3 class="text-lg font-semibold mb-4">Today's Tasks</h3>
                        <div class="space-y-3">
                            <div class="flex items-center space-x-3">
                                <input type="checkbox" checked class="w-5 h-5 rounded">
                                <span class="line-through text-gray-500">Project submission</span>
                                <span class="ml-auto bg-gray-500/20 text-gray-400 px-2 py-1 rounded text-xs">Low</span>
                            </div>
                            <div class="flex items-center space-x-3">
                                <input type="checkbox" class="w-5 h-5 rounded">
                                <span>Team meeting prep</span>
                                <span class="ml-auto bg-red-500/20 text-red-400 px-2 py-1 rounded text-xs">High</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
    
    <!-- How It Works -->
    <section class="py-20 relative" id="how-it-works">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-16 animate-on-scroll">
                <h2 class="text-4xl md:text-5xl font-bold mb-4 glow-text">
                    How It Works
                </h2>
                <p class="text-xl text-gray-300">
                    From conversation to insights in 4 simple steps
                </p>
            </div>
            
            <div class="grid md:grid-cols-4 gap-8 stagger-children">
                <!-- Step 1 -->
                <div class="animate-on-scroll">
                    <div class="glass-card rounded-2xl p-8 text-center hover:scale-105 transition-all">
                        <div class="w-16 h-16 bg-blue-600 rounded-full flex items-center justify-center text-2xl font-bold mx-auto mb-4">1</div>
                        <div class="text-4xl mb-4">💬</div>
                        <h3 class="text-xl font-bold mb-2">Chat with AI</h3>
                        <p class="text-gray-300 text-sm">
                            "I spent $50 on groceries"
                        </p>
                    </div>
                    <div class="text-center text-3xl my-4">→</div>
                </div>
                
                <!-- Step 2 -->
                <div class="animate-on-scroll">
                    <div class="glass-card rounded-2xl p-8 text-center hover:scale-105 transition-all">
                        <div class="w-16 h-16 bg-purple-600 rounded-full flex items-center justify-center text-2xl font-bold mx-auto mb-4">2</div>
                        <div class="text-4xl mb-4">🤖</div>
                        <h3 class="text-xl font-bold mb-2">AI Processes</h3>
                        <p class="text-gray-300 text-sm">
                            Gemini extracts amount, category, date
                        </p>
                    </div>
                    <div class="text-center text-3xl my-4">→</div>
                </div>
                
                <!-- Step 3 -->
                <div class="animate-on-scroll">
                    <div class="glass-card rounded-2xl p-8 text-center hover:scale-105 transition-all">
                        <div class="w-16 h-16 bg-green-600 rounded-full flex items-center justify-center text-2xl font-bold mx-auto mb-4">3</div>
                        <div class="text-4xl mb-4">✅</div>
                        <h3 class="text-xl font-bold mb-2">Auto-Logged</h3>
                        <p class="text-gray-300 text-sm">
                            Transaction saved to your dashboard
                        </p>
                    </div>
                    <div class="text-center text-3xl my-4">→</div>
                </div>
                
                <!-- Step 4 -->
                <div class="animate-on-scroll">
                    <div class="glass-card rounded-2xl p-8 text-center hover:scale-105 transition-all">
                        <div class="w-16 h-16 bg-yellow-600 rounded-full flex items-center justify-center text-2xl font-bold mx-auto mb-4">4</div>
                        <div class="text-4xl mb-4">📊</div>
                        <h3 class="text-xl font-bold mb-2">Get Insights</h3>
                        <p class="text-gray-300 text-sm">
                            AI analyzes and recommends budget adjustments
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </section>
    
    <!-- Testimonials -->
    <section class="py-20 relative" id="testimonials">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-16 animate-on-scroll">
                <h2 class="text-4xl md:text-5xl font-bold mb-4 glow-text">
                    Loved by Thousands
                </h2>
                <p class="text-xl text-gray-300">
                    See what our users are saying
                </p>
            </div>
            
            <div class="grid md:grid-cols-3 gap-8 stagger-children">
                <!-- Testimonial 1 -->
                <div class="glass-card rounded-2xl p-8 animate-on-scroll hover:scale-105 transition-all">
                    <div class="flex items-center mb-4">
                        <div class="w-12 h-12 bg-gradient-to-r from-blue-400 to-purple-500 rounded-full flex items-center justify-center text-2xl">👨</div>
                        <div class="ml-3">
                            <div class="font-bold">Alex Chen</div>
                            <div class="text-sm text-gray-400">Software Engineer</div>
                        </div>
                    </div>
                    <div class="text-yellow-400 mb-2">⭐⭐⭐⭐⭐</div>
                    <p class="text-gray-300">
                        "Persona saved me 3 hours weekly on expense tracking! The AI is incredibly accurate."
                    </p>
                </div>
                
                <!-- Testimonial 2 -->
                <div class="glass-card rounded-2xl p-8 animate-on-scroll hover:scale-105 transition-all">
                    <div class="flex items-center mb-4">
                        <div class="w-12 h-12 bg-gradient-to-r from-green-400 to-blue-500 rounded-full flex items-center justify-center text-2xl">👩</div>
                        <div class="ml-3">
                            <div class="font-bold">Sarah Johnson</div>
                            <div class="text-sm text-gray-400">Freelance Designer</div>
                        </div>
                    </div>
                    <div class="text-yellow-400 mb-2">⭐⭐⭐⭐⭐</div>
                    <p class="text-gray-300">
                        "The AI actually understands my spending patterns and gives useful recommendations!"
                    </p>
                </div>
                
                <!-- Testimonial 3 -->
                <div class="glass-card rounded-2xl p-8 animate-on-scroll hover:scale-105 transition-all">
                    <div class="flex items-center mb-4">
                        <div class="w-12 h-12 bg-gradient-to-r from-purple-400 to-pink-500 rounded-full flex items-center justify-center text-2xl">👨</div>
                        <div class="ml-3">
                            <div class="font-bold">Marcus Rivera</div>
                            <div class="text-sm text-gray-400">Small Business Owner</div>
                        </div>
                    </div>
                    <div class="text-yellow-400 mb-2">⭐⭐⭐⭐⭐</div>
                    <p class="text-gray-300">
                        "Finally, a finance app that doesn't feel like work. It's like having a financial advisor in my pocket."
                    </p>
                </div>
            </div>
        </div>
    </section>
    
    <!-- Pricing -->
    <section class="py-20 relative" id="pricing">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-16 animate-on-scroll">
                <h2 class="text-4xl md:text-5xl font-bold mb-4 glow-text">
                    Simple, Transparent Pricing
                </h2>
                <p class="text-xl text-gray-300">
                    Start free, upgrade when you're ready
                </p>
            </div>
            
            <div class="grid md:grid-cols-3 gap-8 stagger-children">
                <!-- Free Tier -->
                <div class="glass-card rounded-2xl p-8 animate-on-scroll hover:scale-105 transition-all">
                    <h3 class="text-2xl font-bold mb-2">Free</h3>
                    <div class="text-4xl font-bold mb-6">$0<span class="text-lg text-gray-400">/mo</span></div>
                    <ul class="space-y-3 mb-8">
                        <li class="flex items-center"><span class="text-green-400 mr-2">✓</span> Up to 50 transactions/month</li>
                        <li class="flex items-center"><span class="text-green-400 mr-2">✓</span> Basic AI insights</li>
                        <li class="flex items-center"><span class="text-green-400 mr-2">✓</span> Task management</li>
                        <li class="flex items-center"><span class="text-green-400 mr-2">✓</span> Mobile app</li>
                    </ul>
                    <a href="{{ route('register') }}" class="block w-full glass-button text-center px-6 py-3 rounded-xl font-semibold">
                        Get Started
                    </a>
                </div>
                
                <!-- Pro Tier (Highlighted) -->
                <div class="glass-card rounded-2xl p-8 animate-on-scroll hover:scale-105 transition-all border-2 border-blue-500 relative">
                    <div class="absolute -top-4 left-1/2 transform -translate-x-1/2 bg-blue-600 px-4 py-1 rounded-full text-sm font-semibold">
                        Most Popular
                    </div>
                    <h3 class="text-2xl font-bold mb-2">Pro</h3>
                    <div class="text-4xl font-bold mb-6">$9<span class="text-lg text-gray-400">/mo</span></div>
                    <ul class="space-y-3 mb-8">
                        <li class="flex items-center"><span class="text-green-400 mr-2">✓</span> Unlimited transactions</li>
                        <li class="flex items-center"><span class="text-green-400 mr-2">✓</span> Advanced AI insights</li>
                        <li class="flex items-center"><span class="text-green-400 mr-2">✓</span> Receipt scanning</li>
                        <li class="flex items-center"><span class="text-green-400 mr-2">✓</span> Custom categories</li>
                        <li class="flex items-center"><span class="text-green-400 mr-2">✓</span> Export reports</li>
                        <li class="flex items-center"><span class="text-green-400 mr-2">✓</span> Priority support</li>
                    </ul>
                    <a href="{{ route('register') }}" class="block w-full bg-blue-600 hover:bg-blue-700 text-center px-6 py-3 rounded-xl font-semibold transition-colors">
                        Start Free Trial
                    </a>
                </div>
                
                <!-- Enterprise Tier -->
                <div class="glass-card rounded-2xl p-8 animate-on-scroll hover:scale-105 transition-all">
                    <h3 class="text-2xl font-bold mb-2">Enterprise</h3>
                    <div class="text-4xl font-bold mb-6">Custom</div>
                    <ul class="space-y-3 mb-8">
                        <li class="flex items-center"><span class="text-green-400 mr-2">✓</span> Everything in Pro</li>
                        <li class="flex items-center"><span class="text-green-400 mr-2">✓</span> Multi-user accounts</li>
                        <li class="flex items-center"><span class="text-green-400 mr-2">✓</span> API access</li>
                        <li class="flex items-center"><span class="text-green-400 mr-2">✓</span> Custom integrations</li>
                        <li class="flex items-center"><span class="text-green-400 mr-2">✓</span> Dedicated support</li>
                        <li class="flex items-center"><span class="text-green-400 mr-2">✓</span> SLA guarantee</li>
                    </ul>
                    <a href="#contact" class="block w-full glass-button text-center px-6 py-3 rounded-xl font-semibold">
                        Contact Sales
                    </a>
                </div>
            </div>
        </div>
    </section>
    
    <!-- Final CTA -->
    <section class="py-32 relative overflow-hidden">
        <div class="absolute inset-0 animated-gradient opacity-20"></div>
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 text-center relative z-10 animate-on-scroll">
            <h2 class="text-4xl md:text-6xl font-bold mb-6 glow-text">
                Ready to Transform Your Financial Life?
            </h2>
            <p class="text-xl md:text-2xl text-gray-300 mb-12">
                Join thousands who manage money smarter with AI
            </p>
            <div class="flex flex-col sm:flex-row gap-4 justify-center items-center mb-8">
                <a href="{{ route('register') }}" class="bg-blue-600 hover:bg-blue-700 px-10 py-5 rounded-xl text-xl font-semibold glow transition-all hover:scale-110">
                    🚀 Start Free Trial
                </a>
                <a href="#features" class="glass-button px-10 py-5 rounded-xl text-xl font-semibold hover:scale-110 transition-all">
                    📚 Learn More
                </a>
            </div>
            <div class="flex flex-wrap justify-center gap-6 text-sm text-gray-400">
                <div class="flex items-center">
                    <svg class="w-5 h-5 text-green-400 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                    </svg>
                    Secure & Private
                </div>
                <div class="flex items-center">
                    <svg class="w-5 h-5 text-green-400 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                    </svg>
                    No Credit Card Required
                </div>
                <div class="flex items-center">
                    <svg class="w-5 h-5 text-green-400 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                    </svg>
                    Cancel Anytime
                </div>
            </div>
        </div>
    </section>
    
    <!-- Improved Footer -->
    <footer class="bg-gradient-to-b from-slate-900 to-slate-800 border-t border-white/10">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-16">
            <div class="grid grid-cols-1 md:grid-cols-4 gap-12 mb-12">
                <!-- Brand Column -->
                <div class="md:col-span-1">
                    <div class="flex items-center space-x-3 mb-6">
                        <svg class="w-10 h-10 text-blue-500 brain-icon" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <path d="M12 4C8 4 6 6 6 10C6 12 7 13 8 14C7 15 6 16 6 17C6 19 7 20 9 20C9 21 10 22 12 22C14 22 15 21 15 20C17 20 18 19 18 17C18 16 17 15 16 14C17 13 18 12 18 10C18 6 16 4 12 4Z" fill="currentColor"/>
                            <path d="M9 10C9.55228 10 10 9.55228 10 9C10 8.44772 9.55228 8 9 8C8.44772 8 8 8.44772 8 9C8 9.55228 8.44772 10 9 10Z" fill="#1e293b"/>
                            <path d="M15 10C15.5523 10 16 9.55228 16 9C16 8.44772 15.5523 8 15 8C14.4477 8 14 8.44772 14 9C14 9.55228 14.4477 10 15 10Z" fill="#1e293b"/>
                            <path d="M10 15H14" stroke="#1e293b" stroke-width="2" stroke-linecap="round"/>
                        </svg>
                        <span class="text-2xl font-bold bg-gradient-to-r from-blue-400 to-purple-500 bg-clip-text text-transparent">Persona</span>
                    </div>
                    <p class="text-gray-400 text-sm leading-relaxed mb-6">
                        AI-powered personal finance and task management for smarter living. 
                        Manage your money and tasks through natural conversation with AI.
                    </p>
                    <div class="flex space-x-4">
                        <a href="#" class="text-gray-400 hover:text-blue-400 transition-colors">
                            <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 24 24">
                                <path d="M24 4.557c-.883.392-1.832.656-2.828.775 1.017-.609 1.798-1.574 2.165-2.724-.951.564-2.005.974-3.127 1.195-.897-.957-2.178-1.555-3.594-1.555-3.179 0-5.515 2.966-4.797 6.045-4.091-.205-7.719-2.165-10.148-5.144-1.29 2.213-.669 5.108 1.523 6.574-.806-.026-1.566-.247-2.229-.616-.054 2.281 1.581 4.415 3.949 4.89-.693.188-1.452.232-2.224.084.626 1.956 2.444 3.379 4.6 3.419-2.07 1.623-4.678 2.348-7.29 2.04 2.179 1.397 4.768 2.212 7.548 2.212 9.142 0 14.307-7.721 13.995-14.646.962-.695 1.797-1.562 2.457-2.549z"/>
                            </svg>
                        </a>
                        <a href="#" class="text-gray-400 hover:text-blue-400 transition-colors">
                            <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 24 24">
                                <path d="M12 2.163c3.204 0 3.584.012 4.85.07 3.252.148 4.771 1.691 4.919 4.919.058 1.265.069 1.645.069 4.849 0 3.205-.012 3.584-.069 4.849-.149 3.225-1.664 4.771-4.919 4.919-1.266.058-1.644.07-4.85.07-3.204 0-3.584-.012-4.849-.07-3.26-.149-4.771-1.699-4.919-4.92-.058-1.265-.07-1.644-.07-4.849 0-3.204.013-3.583.07-4.849.149-3.227 1.664-4.771 4.919-4.919 1.266-.057 1.645-.069 4.849-.069zm0-2.163c-3.259 0-3.667.014-4.947.072-4.358.2-6.78 2.618-6.98 6.98-.059 1.281-.073 1.689-.073 4.948 0 3.259.014 3.668.072 4.948.2 4.358 2.618 6.78 6.98 6.98 1.281.058 1.689.072 4.948.072 3.259 0 3.668-.014 4.948-.072 4.354-.2 6.782-2.618 6.979-6.98.059-1.28.073-1.689.073-4.948 0-3.259-.014-3.667-.072-4.947-.196-4.354-2.617-6.78-6.979-6.98-1.281-.059-1.69-.073-4.949-.073zm0 5.838c-3.403 0-6.162 2.759-6.162 6.162s2.759 6.163 6.162 6.163 6.162-2.759 6.162-6.163c0-3.403-2.759-6.162-6.162-6.162zm0 10.162c-2.209 0-4-1.79-4-4 0-2.209 1.791-4 4-4s4 1.791 4 4c0 2.21-1.791 4-4 4zm6.406-11.845c-.796 0-1.441.645-1.441 1.44s.645 1.44 1.441 1.44c.795 0 1.439-.645 1.439-1.44s-.644-1.44-1.439-1.44z"/>
                            </svg>
                        </a>
                        <a href="#" class="text-gray-400 hover:text-blue-400 transition-colors">
                            <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 24 24">
                                <path d="M19 0h-14c-2.761 0-5 2.239-5 5v14c0 2.761 2.239 5 5 5h14c2.762 0 5-2.239 5-5v-14c0-2.761-2.238-5-5-5zm-11 19h-3v-11h3v11zm-1.5-12.268c-.966 0-1.75-.79-1.75-1.764s.784-1.764 1.75-1.764 1.75.79 1.75 1.764-.783 1.764-1.75 1.764zm13.5 12.268h-3v-5.604c0-3.368-4-3.113-4 0v5.604h-3v-11h3v1.765c1.396-2.586 7-2.777 7 2.476v6.759z"/>
                            </svg>
                        </a>
                    </div>
                </div>
                
                <!-- Product Column -->
                <div>
                    <h4 class="font-semibold text-lg mb-4 text-white">Product</h4>
                    <ul class="space-y-3 text-sm text-gray-400">
                        <li><a href="#features" class="hover:text-blue-400 transition-colors flex items-center">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                            </svg>
                            Features
                        </a></li>
                        <li><a href="#task-management" class="hover:text-blue-400 transition-colors flex items-center">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                            </svg>
                            Task Management
                        </a></li>
                        <li><a href="#pricing" class="hover:text-blue-400 transition-colors flex items-center">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1"/>
                            </svg>
                            Pricing
                        </a></li>
                        <li><a href="#demo" class="hover:text-blue-400 transition-colors flex items-center">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z"/>
                            </svg>
                            Demo
                        </a></li>
                    </ul>
                </div>
                
                <!-- Company Column -->
                <div>
                    <h4 class="font-semibold text-lg mb-4 text-white">Company</h4>
                    <ul class="space-y-3 text-sm text-gray-400">
                        <li><a href="#" class="hover:text-blue-400 transition-colors flex items-center">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                            About
                        </a></li>
                        <li><a href="#" class="hover:text-blue-400 transition-colors flex items-center">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 20H5a2 2 0 01-2-2V6a2 2 0 012-2h10a2 2 0 012 2v1m2 13a2 2 0 01-2-2V7m2 13a2 2 0 002-2V9a2 2 0 00-2-2h-2m-4-3H9M7 16h6M7 8h6v4H7V8z"/>
                            </svg>
                            Blog
                        </a></li>
                        <li><a href="#" class="hover:text-blue-400 transition-colors flex items-center">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2-2v2m8 0V6a2 2 0 012 2v6a2 2 0 01-2 2H8a2 2 0 01-2-2V8a2 2 0 012-2V6"/>
                            </svg>
                            Careers
                        </a></li>
                        <li><a href="#" class="hover:text-blue-400 transition-colors flex items-center">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/>
                            </svg>
                            Contact
                        </a></li>
                    </ul>
                </div>
                
                <!-- Legal Column -->
                <div>
                    <h4 class="font-semibold text-lg mb-4 text-white">Legal</h4>
                    <ul class="space-y-3 text-sm text-gray-400">
                        <li><a href="#" class="hover:text-blue-400 transition-colors flex items-center">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                            </svg>
                            Privacy
                        </a></li>
                        <li><a href="#" class="hover:text-blue-400 transition-colors flex items-center">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                            </svg>
                            Terms
                        </a></li>
                        <li><a href="#" class="hover:text-blue-400 transition-colors flex items-center">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                            </svg>
                            Security
                        </a></li>
                        <li><a href="#" class="hover:text-blue-400 transition-colors flex items-center">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.228 9c.549-1.165 2.03-2 3.772-2 2.21 0 4 1.343 4 3 0 1.4-1.278 2.575-3.006 2.907-.542.104-.994.54-.994 1.093m0 3h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                            Compliance
                        </a></li>
                    </ul>
                </div>
            </div>
            
            <div class="border-t border-white/10 mt-12 pt-8 flex flex-col md:flex-row justify-between items-center">
                <div class="text-sm text-gray-400 mb-4 md:mb-0">
                    <p>&copy; 2025 Persona. All rights reserved. AI-powered personal finance for smarter living.</p>
                </div>
                <div class="flex items-center space-x-6 text-sm text-gray-400">
                    <span>Made with 💙 for better financial health</span>
                </div>
            </div>
        </div>
    </footer>
    
    <!-- Scroll Animation Script -->
    <script>
        // Scroll Animation Observer
        const observerOptions = {
            threshold: 0.1,
            rootMargin: '0px 0px -50px 0px'
        };
        
        const observer = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    entry.target.classList.add('visible');
                }
            });
        }, observerOptions);
        
        // Observe all animate-on-scroll elements
        document.addEventListener('DOMContentLoaded', () => {
            document.querySelectorAll('.animate-on-scroll').forEach(el => {
                observer.observe(el);
            });
            
            // Create floating particles
            createParticles();
            
            // Add task checkbox functionality
            document.querySelectorAll('.task-checkbox').forEach(checkbox => {
                checkbox.addEventListener('change', function() {
                    const label = this.nextElementSibling;
                    if (this.checked) {
                        label.classList.add('line-through', 'text-gray-500');
                    } else {
                        label.classList.remove('line-through', 'text-gray-500');
                    }
                });
            });
        });
        
        // Create Particles
        function createParticles() {
            const container = document.getElementById('particles-container');
            if (!container) return;
            
            for (let i = 0; i < 50; i++) {
                const particle = document.createElement('div');
                particle.className = 'particle';
                
                const size = Math.random() * 4 + 2;
                particle.style.width = size + 'px';
                particle.style.height = size + 'px';
                particle.style.left = Math.random() * 100 + '%';
                particle.style.top = Math.random() * 100 + '%';
                particle.style.opacity = Math.random() * 0.5 + 0.1;
                
                const duration = Math.random() * 10 + 10;
                particle.style.animation = `float ${duration}s ease-in-out infinite`;
                particle.style.animationDelay = Math.random() * 5 + 's';
                
                container.appendChild(particle);
            }
        }
        
        // Smooth scroll for anchor links
        document.querySelectorAll('a[href^="#"]').forEach(anchor => {
            anchor.addEventListener('click', function (e) {
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
    </script>
</body>
</html>
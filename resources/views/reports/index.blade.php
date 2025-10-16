@extends('layouts.app-master')

@section('title', 'Reports')
@section('page-icon', '📊')
@section('page-title', 'Reports')

@section('content')
<!-- Reports Header -->
<div class="glass-card rounded-xl p-6 animate-fade-in">
    <div class="flex flex-col md:flex-row justify-between items-start md:items-center">
        <div>
            <h1 class="text-3xl font-bold text-white">Financial Reports</h1>
            <p class="text-gray-300 mt-2">Analyze your financial performance and trends</p>
        </div>
        <div class="mt-4 md:mt-0 flex space-x-3">
            <button class="glass-button text-white px-4 py-2 rounded-xl font-medium flex items-center space-x-2">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
                </svg>
                <span>Export PDF</span>
            </button>
            <button class="glass-button text-white px-4 py-2 rounded-xl font-medium flex items-center space-x-2">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                </svg>
                <span>Date Range</span>
            </button>
        </div>
    </div>
</div>

<!-- Report Type Cards -->
<div class="grid grid-cols-1 md:grid-cols-3 gap-6 animate-slide-up">
    <div class="glass-card rounded-xl p-6 animate-bounce-in">
        <div class="flex items-center justify-between mb-4">
            <div class="text-blue-400">
                <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                </svg>
            </div>
            <span class="text-xs px-3 py-1 rounded-full bg-blue-400/20 text-blue-300 border border-blue-400/30">Coming Soon</span>
        </div>
        <h3 class="text-xl font-bold text-white mb-2">Monthly Report</h3>
        <p class="text-gray-300 text-sm mb-4">Income vs Expense summary with detailed category breakdown and spending patterns</p>
        <button class="w-full glass-button text-white py-2 rounded-lg font-medium opacity-50 cursor-not-allowed">
            Generate Monthly
        </button>
    </div>

    <div class="glass-card rounded-xl p-6 animate-bounce-in" style="animation-delay: 0.1s;">
        <div class="flex items-center justify-between mb-4">
            <div class="text-green-400">
                <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"/>
                </svg>
            </div>
            <span class="text-xs px-3 py-1 rounded-full bg-green-400/20 text-green-300 border border-green-400/30">Coming Soon</span>
        </div>
        <h3 class="text-xl font-bold text-white mb-2">Quarterly Report</h3>
        <p class="text-gray-300 text-sm mb-4">Comprehensive trends analysis across the quarter with growth insights and projections</p>
        <button class="w-full glass-button text-white py-2 rounded-lg font-medium opacity-50 cursor-not-allowed">
            Generate Quarterly
        </button>
    </div>

    <div class="glass-card rounded-xl p-6 animate-bounce-in" style="animation-delay: 0.2s;">
        <div class="flex items-center justify-between mb-4">
            <div class="text-purple-400">
                <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 3.055A9.001 9.001 0 1020.945 13H11V3.055z"/>
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.488 9H15V3.512A9.025 9.025 0 0120.488 9z"/>
                </svg>
            </div>
            <span class="text-xs px-3 py-1 rounded-full bg-purple-400/20 text-purple-300 border border-purple-400/30">Coming Soon</span>
        </div>
        <h3 class="text-xl font-bold text-white mb-2">Annual Report</h3>
        <p class="text-gray-300 text-sm mb-4">Complete yearly financial performance overview with savings analysis and goals</p>
        <button class="w-full glass-button text-white py-2 rounded-lg font-medium opacity-50 cursor-not-allowed">
            Generate Annual
        </button>
    </div>
</div>

<!-- Quick Stats Preview -->
<div class="grid grid-cols-1 lg:grid-cols-2 gap-6 animate-fade-in">
    <!-- Report Preview -->
    <div class="glass-card rounded-xl p-6">
        <h3 class="text-xl font-bold text-white mb-4 flex items-center">
            <svg class="w-6 h-6 mr-2 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
            </svg>
            Report Preview
        </h3>
        <div class="space-y-4">
            <div class="flex justify-between items-center py-3 border-b border-white/10">
                <span class="text-gray-300">Total Income (This Month)</span>
                <span class="text-green-400 font-semibold">${{ number_format(0, 2) }}</span>
            </div>
            <div class="flex justify-between items-center py-3 border-b border-white/10">
                <span class="text-gray-300">Total Expenses (This Month)</span>
                <span class="text-red-400 font-semibold">${{ number_format(0, 2) }}</span>
            </div>
            <div class="flex justify-between items-center py-3 border-b border-white/10">
                <span class="text-gray-300">Net Savings</span>
                <span class="text-blue-400 font-semibold">${{ number_format(0, 2) }}</span>
            </div>
            <div class="flex justify-between items-center py-3">
                <span class="text-gray-300">Savings Rate</span>
                <span class="text-purple-400 font-semibold">0%</span>
            </div>
        </div>
    </div>

    <!-- Coming Soon Features -->
    <div class="glass-card rounded-xl p-6">
        <h3 class="text-xl font-bold text-white mb-4 flex items-center">
            <svg class="w-6 h-6 mr-2 text-yellow-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
            </svg>
            Upcoming Features
        </h3>
        <div class="space-y-3">
            <div class="flex items-center space-x-3 p-3 bg-white/5 rounded-lg">
                <div class="w-2 h-2 bg-blue-400 rounded-full"></div>
                <span class="text-gray-300">Interactive Charts & Graphs</span>
            </div>
            <div class="flex items-center space-x-3 p-3 bg-white/5 rounded-lg">
                <div class="w-2 h-2 bg-green-400 rounded-full"></div>
                <span class="text-gray-300">PDF Export Functionality</span>
            </div>
            <div class="flex items-center space-x-3 p-3 bg-white/5 rounded-lg">
                <div class="w-2 h-2 bg-purple-400 rounded-full"></div>
                <span class="text-gray-300">Email Report Scheduling</span>
            </div>
            <div class="flex items-center space-x-3 p-3 bg-white/5 rounded-lg">
                <div class="w-2 h-2 bg-yellow-400 rounded-full"></div>
                <span class="text-gray-300">Budget vs Actual Analysis</span>
            </div>
            <div class="flex items-center space-x-3 p-3 bg-white/5 rounded-lg">
                <div class="w-2 h-2 bg-red-400 rounded-full"></div>
                <span class="text-gray-300">Financial Goal Tracking</span>
            </div>
        </div>
    </div>
</div>

<!-- Central Message -->
<div class="glass-card rounded-xl p-8 animate-fade-in text-center">
    <div class="max-w-md mx-auto">
        <div class="text-blue-400 mb-4">
            <svg class="w-16 h-16 mx-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
            </svg>
        </div>
        <h3 class="text-2xl font-bold text-white mb-4">Advanced Reporting System</h3>
        <p class="text-gray-300 mb-6">
            We're building a comprehensive reporting system that will provide detailed insights into your financial patterns, spending habits, and savings opportunities. Stay tuned for these powerful analytics tools!
        </p>
        <button class="glass-button text-white px-6 py-3 rounded-xl font-medium">
            Notify Me When Ready
        </button>
    </div>
</div>
@endsection

@extends('layouts.app-master')

@section('title', 'Settings')
@section('page-icon', '⚙️')
@section('page-title', 'Settings')

@section('content')
<!-- Settings Header -->
<div class="glass-card rounded-xl p-6 animate-fade-in">
    <div class="flex flex-col md:flex-row justify-between items-start md:items-center">
        <div>
            <h1 class="text-3xl font-bold text-white">Account Settings</h1>
            <p class="text-gray-300 mt-2">Manage your account preferences and configurations</p>
        </div>
        <div class="mt-4 md:mt-0 flex space-x-3">
            <button class="glass-button text-white px-4 py-2 rounded-xl font-medium flex items-center space-x-2">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-3m-1 4l-3 3m0 0l-3-3m3 3V4"/>
                </svg>
                <span>Export Data</span>
            </button>
        </div>
    </div>
</div>

<!-- Settings Grid -->
<div class="grid grid-cols-1 lg:grid-cols-3 gap-6 animate-slide-up">
    <!-- Profile Settings Card -->
    <div class="glass-card rounded-xl p-6 animate-bounce-in">
        <div class="flex items-center justify-between mb-4">
            <div class="flex items-center space-x-3">
                <div class="text-blue-400">
                    <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                    </svg>
                </div>
                <h3 class="text-xl font-bold text-white">Profile</h3>
            </div>
        </div>
        <p class="text-gray-300 text-sm mb-4">Update your personal information and profile details</p>
        <a href="{{ route('profile.edit') }}" class="block w-full glass-button text-white py-2 rounded-lg font-medium text-center">
            Edit Profile
        </a>
    </div>

    <!-- Notifications Card -->
    <div class="glass-card rounded-xl p-6 animate-bounce-in" style="animation-delay: 0.1s;">
        <div class="flex items-center justify-between mb-4">
            <div class="flex items-center space-x-3">
                <div class="text-green-400">
                    <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>
                    </svg>
                </div>
                <h3 class="text-xl font-bold text-white">Notifications</h3>
            </div>
            <span class="text-xs px-3 py-1 rounded-full bg-green-400/20 text-green-300 border border-green-400/30">Soon</span>
        </div>
        <p class="text-gray-300 text-sm mb-4">Configure email and push notification preferences</p>
        <button class="w-full glass-button text-white py-2 rounded-lg font-medium opacity-50 cursor-not-allowed">
            Configure
        </button>
    </div>

    <!-- Security Card -->
    <div class="glass-card rounded-xl p-6 animate-bounce-in" style="animation-delay: 0.2s;">
        <div class="flex items-center justify-between mb-4">
            <div class="flex items-center space-x-3">
                <div class="text-yellow-400">
                    <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                    </svg>
                </div>
                <h3 class="text-xl font-bold text-white">Security</h3>
            </div>
            <span class="text-xs px-3 py-1 rounded-full bg-yellow-400/20 text-yellow-300 border border-yellow-400/30">Soon</span>
        </div>
        <p class="text-gray-300 text-sm mb-4">Manage password and two-factor authentication</p>
        <button class="w-full glass-button text-white py-2 rounded-lg font-medium opacity-50 cursor-not-allowed">
            Security Settings
        </button>
    </div>
</div>

<!-- Detailed Settings Sections -->
<div class="grid grid-cols-1 lg:grid-cols-2 gap-6 animate-fade-in">
    <!-- Appearance Settings -->
    <div class="glass-card rounded-xl p-6">
        <h3 class="text-xl font-bold text-white mb-4 flex items-center">
            <svg class="w-6 h-6 mr-2 text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21a4 4 0 01-4-4V5a2 2 0 012-2h4a2 2 0 012 2v12a4 4 0 01-4 4zm0 0h12a2 2 0 002-2v-4a2 2 0 00-2-2h-2.343M11 7.343l1.657-1.657a2 2 0 012.828 0l2.829 2.829a2 2 0 010 2.828l-8.486 8.485M7 17h.01"/>
            </svg>
            Appearance & Display
        </h3>
        <div class="space-y-4">
            <div class="flex justify-between items-center py-3 border-b border-white/10">
                <div>
                    <span class="text-white font-medium">Theme</span>
                    <p class="text-gray-400 text-sm">Current: Glassmorphism Dark</p>
                </div>
                <button class="px-4 py-2 bg-white/10 rounded-lg text-white text-sm opacity-50 cursor-not-allowed">
                    Change
                </button>
            </div>
            <div class="flex justify-between items-center py-3 border-b border-white/10">
                <div>
                    <span class="text-white font-medium">Language</span>
                    <p class="text-gray-400 text-sm">English (US)</p>
                </div>
                <button class="px-4 py-2 bg-white/10 rounded-lg text-white text-sm opacity-50 cursor-not-allowed">
                    Change
                </button>
            </div>
            <div class="flex justify-between items-center py-3">
                <div>
                    <span class="text-white font-medium">Date Format</span>
                    <p class="text-gray-400 text-sm">MM/DD/YYYY</p>
                </div>
                <button class="px-4 py-2 bg-white/10 rounded-lg text-white text-sm opacity-50 cursor-not-allowed">
                    Change
                </button>
            </div>
        </div>
    </div>

    <!-- Privacy Settings -->
    <div class="glass-card rounded-xl p-6">
        <h3 class="text-xl font-bold text-white mb-4 flex items-center">
            <svg class="w-6 h-6 mr-2 text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
            </svg>
            Privacy & Data
        </h3>
        <div class="space-y-4">
            <div class="flex justify-between items-center py-3 border-b border-white/10">
                <div>
                    <span class="text-white font-medium">Data Collection</span>
                    <p class="text-gray-400 text-sm">Analytics enabled</p>
                </div>
                <label class="relative inline-flex items-center cursor-not-allowed opacity-50">
                    <input type="checkbox" class="sr-only peer" disabled checked>
                    <div class="w-11 h-6 bg-gray-600 rounded-full peer peer-checked:bg-blue-500"></div>
                </label>
            </div>
            <div class="flex justify-between items-center py-3 border-b border-white/10">
                <div>
                    <span class="text-white font-medium">Share Usage Data</span>
                    <p class="text-gray-400 text-sm">Help improve Persona</p>
                </div>
                <label class="relative inline-flex items-center cursor-not-allowed opacity-50">
                    <input type="checkbox" class="sr-only peer" disabled>
                    <div class="w-11 h-6 bg-gray-600 rounded-full peer peer-checked:bg-blue-500"></div>
                </label>
            </div>
            <div class="flex justify-between items-center py-3">
                <div>
                    <span class="text-white font-medium">Cookie Preferences</span>
                    <p class="text-gray-400 text-sm">Manage cookies</p>
                </div>
                <button class="px-4 py-2 bg-white/10 rounded-lg text-white text-sm opacity-50 cursor-not-allowed">
                    Manage
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Integration Settings -->
<div class="glass-card rounded-xl p-6 animate-fade-in">
    <h3 class="text-xl font-bold text-white mb-6 flex items-center">
        <svg class="w-6 h-6 mr-2 text-cyan-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 14v6m-3-3h6M6 10h2a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v2a2 2 0 002 2zm10 0h2a2 2 0 002-2V6a2 2 0 00-2-2h-2a2 2 0 00-2 2v2a2 2 0 002 2zM6 20h2a2 2 0 002-2v-2a2 2 0 00-2-2H6a2 2 0 00-2 2v2a2 2 0 002 2z"/>
        </svg>
        Integrations & Connected Apps
    </h3>
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
        <div class="bg-white/5 rounded-lg p-4 border border-white/10">
            <div class="flex items-center justify-between mb-2">
                <div class="flex items-center space-x-3">
                    <div class="w-10 h-10 bg-blue-500/20 rounded-lg flex items-center justify-center">
                        <svg class="w-6 h-6 text-blue-400" fill="currentColor" viewBox="0 0 24 24">
                            <path d="M12 2L2 7l10 5 10-5-10-5zM2 17l10 5 10-5M2 12l10 5 10-5"/>
                        </svg>
                    </div>
                    <div>
                        <h4 class="text-white font-semibold">Google</h4>
                        <p class="text-gray-400 text-xs">Not connected</p>
                    </div>
                </div>
            </div>
            <button class="w-full mt-3 px-4 py-2 bg-white/10 rounded-lg text-white text-sm opacity-50 cursor-not-allowed">
                Connect
            </button>
        </div>

        <div class="bg-white/5 rounded-lg p-4 border border-white/10">
            <div class="flex items-center justify-between mb-2">
                <div class="flex items-center space-x-3">
                    <div class="w-10 h-10 bg-purple-500/20 rounded-lg flex items-center justify-center">
                        <svg class="w-6 h-6 text-purple-400" fill="currentColor" viewBox="0 0 24 24">
                            <path d="M12 0c-6.626 0-12 5.373-12 12 0 5.302 3.438 9.8 8.207 11.387.599.111.793-.261.793-.577v-2.234c-3.338.726-4.033-1.416-4.033-1.416-.546-1.387-1.333-1.756-1.333-1.756-1.089-.745.083-.729.083-.729 1.205.084 1.839 1.237 1.839 1.237 1.07 1.834 2.807 1.304 3.492.997.107-.775.418-1.305.762-1.604-2.665-.305-5.467-1.334-5.467-5.931 0-1.311.469-2.381 1.236-3.221-.124-.303-.535-1.524.117-3.176 0 0 1.008-.322 3.301 1.23.957-.266 1.983-.399 3.003-.404 1.02.005 2.047.138 3.006.404 2.291-1.552 3.297-1.23 3.297-1.23.653 1.653.242 2.874.118 3.176.77.84 1.235 1.911 1.235 3.221 0 4.609-2.807 5.624-5.479 5.921.43.372.823 1.102.823 2.222v3.293c0 .319.192.694.801.576 4.765-1.589 8.199-6.086 8.199-11.386 0-6.627-5.373-12-12-12z"/>
                        </svg>
                    </div>
                    <div>
                        <h4 class="text-white font-semibold">GitHub</h4>
                        <p class="text-gray-400 text-xs">Not connected</p>
                    </div>
                </div>
            </div>
            <button class="w-full mt-3 px-4 py-2 bg-white/10 rounded-lg text-white text-sm opacity-50 cursor-not-allowed">
                Connect
            </button>
        </div>

        <div class="bg-white/5 rounded-lg p-4 border border-white/10">
            <div class="flex items-center justify-between mb-2">
                <div class="flex items-center space-x-3">
                    <div class="w-10 h-10 bg-green-500/20 rounded-lg flex items-center justify-center">
                        <svg class="w-6 h-6 text-green-400" fill="currentColor" viewBox="0 0 24 24">
                            <path d="M23.953 4.57a10 10 0 01-2.825.775 4.958 4.958 0 002.163-2.723c-.951.555-2.005.959-3.127 1.184a4.92 4.92 0 00-8.384 4.482C7.69 8.095 4.067 6.13 1.64 3.162a4.822 4.822 0 00-.666 2.475c0 1.71.87 3.213 2.188 4.096a4.904 4.904 0 01-2.228-.616v.06a4.923 4.923 0 003.946 4.827 4.996 4.996 0 01-2.212.085 4.936 4.936 0 004.604 3.417 9.867 9.867 0 01-6.102 2.105c-.39 0-.779-.023-1.17-.067a13.995 13.995 0 007.557 2.209c9.053 0 13.998-7.496 13.998-13.985 0-.21 0-.42-.015-.63A9.935 9.935 0 0024 4.59z"/>
                        </svg>
                    </div>
                    <div>
                        <h4 class="text-white font-semibold">Twitter</h4>
                        <p class="text-gray-400 text-xs">Not connected</p>
                    </div>
                </div>
            </div>
            <button class="w-full mt-3 px-4 py-2 bg-white/10 rounded-lg text-white text-sm opacity-50 cursor-not-allowed">
                Connect
            </button>
        </div>
    </div>
</div>

<!-- Danger Zone -->
<div class="glass-card rounded-xl p-6 animate-fade-in border-2 border-red-500/30">
    <h3 class="text-xl font-bold text-red-400 mb-4 flex items-center">
        <svg class="w-6 h-6 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
        </svg>
        Danger Zone
    </h3>
    <div class="space-y-4">
        <div class="flex justify-between items-center py-3 border-b border-red-500/20">
            <div>
                <span class="text-white font-medium">Clear All Data</span>
                <p class="text-gray-400 text-sm">Permanently delete all transactions and tasks</p>
            </div>
            <button class="px-4 py-2 bg-red-500/20 border border-red-500/50 rounded-lg text-red-300 text-sm hover:bg-red-500/30 transition-colors">
                Clear Data
            </button>
        </div>
        <div class="flex justify-between items-center py-3">
            <div>
                <span class="text-white font-medium">Delete Account</span>
                <p class="text-gray-400 text-sm">Permanently delete your account and all data</p>
            </div>
            <button class="px-4 py-2 bg-red-500/20 border border-red-500/50 rounded-lg text-red-300 text-sm hover:bg-red-500/30 transition-colors">
                Delete Account
            </button>
        </div>
    </div>
</div>
@endsection

@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4 py-6">
    <div class="mb-6">
        <h1 class="text-3xl font-bold text-gray-900 dark:text-white">Settings</h1>
        <p class="text-gray-600 dark:text-gray-400 mt-2">Manage your account and preferences</p>
    </div>

    <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
        <div class="space-y-6">
            <div>
                <h3 class="text-lg font-medium text-gray-900 dark:text-white">Profile Settings</h3>
                <p class="text-sm text-gray-600 dark:text-gray-400 mt-1">
                    Update your profile information
                    <a href="{{ route('profile.edit') }}" class="text-indigo-600 hover:text-indigo-500 ml-2">
                        Edit Profile →
                    </a>
                </p>
            </div>

            <div class="border-t border-gray-200 dark:border-gray-700 pt-6">
                <h3 class="text-lg font-medium text-gray-900 dark:text-white">Notifications</h3>
                <p class="text-sm text-gray-600 dark:text-gray-400 mt-1">Coming soon...</p>
            </div>

            <div class="border-t border-gray-200 dark:border-gray-700 pt-6">
                <h3 class="text-lg font-medium text-gray-900 dark:text-white">Preferences</h3>
                <p class="text-sm text-gray-600 dark:text-gray-400 mt-1">Coming soon...</p>
            </div>
        </div>
    </div>
</div>
@endsection

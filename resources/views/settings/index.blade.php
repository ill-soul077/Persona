<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">Settings</h2>
            <span class="text-sm text-gray-500 dark:text-gray-400">Manage your preferences</span>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8 space-y-6">
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
    </div>
</x-app-layout>

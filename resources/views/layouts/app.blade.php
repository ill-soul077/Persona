<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'Laravel') }}</title>
    @stack('head')

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body x-data class="font-sans antialiased">
        <!-- Top loading bar -->
        <div id="page-loading-bar" class="fixed top-0 left-0 h-1 w-0 bg-indigo-500 z-50 transition-all duration-300"></div>

        <div class="min-h-screen bg-gray-50 dark:bg-gray-900 bg-gradient-to-b from-white to-gray-100 dark:from-gray-900 dark:to-gray-950">
            @include('layouts.navigation')

            <!-- Page Heading -->
            @isset($header)
                <header class="bg-white/80 dark:bg-gray-800/80 backdrop-blur shadow-sm">
                    <div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
                        {{ $header }}
                    </div>
                </header>
            @endisset

            <!-- Page Content -->
            <main class="animate-fade-in">
                {{ $slot }}
            </main>
        </div>

        <!-- Toasts Container -->
        <div x-data="toastStore" class="fixed inset-0 pointer-events-none z-50">
            <div class="absolute top-16 right-4 space-y-3 w-80 max-w-[90vw]">
                <template x-for="t in toasts" :key="t.id">
                    <div x-show="t.show" x-transition.opacity.duration.200ms
                         :class="{
                            'bg-white dark:bg-gray-800 border-l-4 shadow-lg rounded-md p-4 pointer-events-auto': true,
                            'border-green-500': t.type==='success',
                            'border-red-500': t.type==='error',
                            'border-yellow-500': t.type==='warning',
                            'border-blue-500': t.type==='info'
                         }">
                        <div class="flex items-start">
                            <div class="flex-1">
                                <p class="text-sm font-semibold text-gray-900 dark:text-gray-100" x-text="t.title"></p>
                                <p class="mt-1 text-sm text-gray-600 dark:text-gray-300" x-text="t.message"></p>
                            </div>
                            <button class="ml-3 text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200" @click="dismiss(t.id)">✕</button>
                        </div>
                    </div>
                </template>
            </div>
        </div>

        @stack('scripts')
    </body>
</html>

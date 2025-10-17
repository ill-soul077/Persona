@extends('layouts.app-master')

@section('title', 'Profile')
@section('page-icon', '👤')
@section('page-title', 'Profile')

@section('content')
<!-- Success Message -->
@if (session('status') === 'profile-updated' || session('status') === 'password-updated')
<div class="glass-card rounded-xl p-4 mb-6 bg-green-500/20 border border-green-500/50 animate-fade-in">
    <div class="flex items-center">
        <svg class="w-6 h-6 text-green-400 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
        </svg>
        <span class="text-white">{{ __('Saved successfully!') }}</span>
    </div>
</div>
@endif

@if (session('status') === 'verification-link-sent')
<div class="glass-card rounded-xl p-4 mb-6 bg-blue-500/20 border border-blue-500/50 animate-fade-in">
    <div class="flex items-center">
        <svg class="w-6 h-6 text-blue-400 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
        </svg>
        <span class="text-white">{{ __('A new verification link has been sent to your email address.') }}</span>
    </div>
</div>
@endif

<!-- Profile Header -->
<div class="glass-card rounded-xl p-6 animate-fade-in">
    <div class="flex flex-col md:flex-row items-center md:items-start space-y-4 md:space-y-0 md:space-x-6">
        <div class="relative">
            <div class="w-24 h-24 rounded-full bg-gradient-to-br from-blue-500 to-purple-500 flex items-center justify-center text-white text-3xl font-bold shadow-xl">
                {{ strtoupper(substr($user->name, 0, 1)) }}
            </div>
            <div class="absolute bottom-0 right-0 w-6 h-6 bg-green-500 rounded-full border-4 border-gray-900"></div>
        </div>
        <div class="text-center md:text-left flex-1">
            <h1 class="text-3xl font-bold text-white">{{ $user->name }}</h1>
            <p class="text-gray-300 mt-1">{{ $user->email }}</p>
            <div class="flex items-center justify-center md:justify-start mt-2 space-x-2">
                @if ($user instanceof \Illuminate\Contracts\Auth\MustVerifyEmail && $user->hasVerifiedEmail())
                    <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-green-500/20 text-green-300 border border-green-500/30">
                        <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                        Verified
                    </span>
                @else
                    <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-yellow-500/20 text-yellow-300 border border-yellow-500/30">
                        <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
                        </svg>
                        Unverified
                    </span>
                @endif
                <span class="text-gray-400 text-sm">Member since {{ $user->created_at->format('M Y') }}</span>
            </div>
        </div>
    </div>
</div>

<!-- Profile Information -->
<div class="glass-card rounded-xl p-6 animate-slide-up">
    @include('profile.partials.update-profile-information-form')
</div>

<!-- Update Password -->
<div class="glass-card rounded-xl p-6 animate-slide-up" style="animation-delay: 0.1s;">
    @include('profile.partials.update-password-form')
</div>

<!-- Delete Account -->
<div class="glass-card rounded-xl p-6 animate-slide-up border-2 border-red-500/30" style="animation-delay: 0.2s;">
    @include('profile.partials.delete-user-form')
</div>
@endsection

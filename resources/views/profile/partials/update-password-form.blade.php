<section>
    <header class="mb-6">
        <h3 class="text-2xl font-bold text-white flex items-center">
            <svg class="w-7 h-7 mr-3 text-yellow-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z"/>
            </svg>
            {{ __('Update Password') }}
        </h3>
        <p class="text-gray-300 text-sm mt-2">
            {{ __('Ensure your account is using a long, random password to stay secure.') }}
        </p>
    </header>

    <form method="post" action="{{ route('password.update') }}" class="space-y-6">
        @csrf
        @method('put')

        <div>
            <label for="update_password_current_password" class="text-gray-300 text-sm font-medium mb-2 block">{{ __('Current Password') }}</label>
            <input id="update_password_current_password" name="current_password" type="password" 
                   class="w-full bg-white/10 border border-white/20 rounded-xl px-4 py-3 text-white placeholder-gray-400 focus:outline-none focus:border-yellow-400 focus:ring-2 focus:ring-yellow-400/50 transition-all" 
                   autocomplete="current-password">
            @if($errors->updatePassword->get('current_password'))
                <p class="mt-2 text-sm text-red-400">{{ $errors->updatePassword->first('current_password') }}</p>
            @endif
        </div>

        <div>
            <label for="update_password_password" class="text-gray-300 text-sm font-medium mb-2 block">{{ __('New Password') }}</label>
            <input id="update_password_password" name="password" type="password" 
                   class="w-full bg-white/10 border border-white/20 rounded-xl px-4 py-3 text-white placeholder-gray-400 focus:outline-none focus:border-yellow-400 focus:ring-2 focus:ring-yellow-400/50 transition-all" 
                   autocomplete="new-password">
            <p class="text-gray-400 text-xs mt-1">Minimum 8 characters</p>
            @if($errors->updatePassword->get('password'))
                <p class="mt-2 text-sm text-red-400">{{ $errors->updatePassword->first('password') }}</p>
            @endif
        </div>

        <div>
            <label for="update_password_password_confirmation" class="text-gray-300 text-sm font-medium mb-2 block">{{ __('Confirm Password') }}</label>
            <input id="update_password_password_confirmation" name="password_confirmation" type="password" 
                   class="w-full bg-white/10 border border-white/20 rounded-xl px-4 py-3 text-white placeholder-gray-400 focus:outline-none focus:border-yellow-400 focus:ring-2 focus:ring-yellow-400/50 transition-all" 
                   autocomplete="new-password">
            @if($errors->updatePassword->get('password_confirmation'))
                <p class="mt-2 text-sm text-red-400">{{ $errors->updatePassword->first('password_confirmation') }}</p>
            @endif
        </div>

        <div class="flex items-center gap-4 pt-4">
            <button type="submit" 
                    class="px-6 py-3 bg-gradient-to-r from-yellow-500 to-orange-500 rounded-xl text-white font-medium hover:from-yellow-600 hover:to-orange-600 transition-all duration-200 shadow-lg">
                {{ __('Update Password') }}
            </button>
        </div>
    </form>
</section>

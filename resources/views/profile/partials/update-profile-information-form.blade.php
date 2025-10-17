<section>
    <header class="mb-6">
        <h3 class="text-2xl font-bold text-white flex items-center">
            <svg class="w-7 h-7 mr-3 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
            </svg>
            {{ __('Profile Information') }}
        </h3>
        <p class="text-gray-300 text-sm mt-2">
            {{ __("Update your account's profile information and email address.") }}
        </p>
    </header>

    <form id="send-verification" method="post" action="{{ route('verification.send') }}">
        @csrf
    </form>

    <form method="post" action="{{ route('profile.update') }}" class="space-y-6">
        @csrf
        @method('patch')

        <div>
            <label for="name" class="text-gray-300 text-sm font-medium mb-2 block">{{ __('Name') }}</label>
            <input id="name" name="name" type="text" 
                   class="w-full bg-white/10 border border-white/20 rounded-xl px-4 py-3 text-white placeholder-gray-400 focus:outline-none focus:border-blue-400 focus:ring-2 focus:ring-blue-400/50 transition-all" 
                   value="{{ old('name', $user->name) }}" 
                   required autofocus autocomplete="name">
            @if($errors->get('name'))
                <p class="mt-2 text-sm text-red-400">{{ $errors->first('name') }}</p>
            @endif
        </div>

        <div>
            <label for="email" class="text-gray-300 text-sm font-medium mb-2 block">{{ __('Email') }}</label>
            <input id="email" name="email" type="email" 
                   class="w-full bg-white/10 border border-white/20 rounded-xl px-4 py-3 text-white placeholder-gray-400 focus:outline-none focus:border-blue-400 focus:ring-2 focus:ring-blue-400/50 transition-all" 
                   value="{{ old('email', $user->email) }}" 
                   required autocomplete="username">
            @if($errors->get('email'))
                <p class="mt-2 text-sm text-red-400">{{ $errors->first('email') }}</p>
            @endif

            @if ($user instanceof \Illuminate\Contracts\Auth\MustVerifyEmail && ! $user->hasVerifiedEmail())
                <div class="mt-3 bg-yellow-500/10 border border-yellow-500/30 rounded-xl p-4">
                    <p class="text-sm text-yellow-300">
                        {{ __('Your email address is unverified.') }}
                    </p>
                    <button form="send-verification" 
                            class="mt-2 text-sm text-yellow-400 hover:text-yellow-300 underline font-medium focus:outline-none focus:ring-2 focus:ring-yellow-400 rounded">
                        {{ __('Click here to re-send the verification email.') }}
                    </button>
                </div>
            @endif
        </div>

        <div class="flex items-center gap-4 pt-4">
            <button type="submit" 
                    class="px-6 py-3 bg-gradient-to-r from-blue-500 to-purple-500 rounded-xl text-white font-medium hover:from-blue-600 hover:to-purple-600 transition-all duration-200 shadow-lg">
                {{ __('Save Changes') }}
            </button>
        </div>
    </form>
</section>

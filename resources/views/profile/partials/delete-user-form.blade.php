<section class="space-y-6">
    <header class="mb-6">
        <h3 class="text-2xl font-bold text-red-400 flex items-center">
            <svg class="w-7 h-7 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
            </svg>
            {{ __('Delete Account') }}
        </h3>
        <p class="text-gray-300 text-sm mt-2">
            {{ __('Once your account is deleted, all of its resources and data will be permanently deleted. Before deleting your account, please download any data or information that you wish to retain.') }}
        </p>
    </header>

    <button
        x-data=""
        x-on:click.prevent="$dispatch('open-modal', 'confirm-user-deletion')"
        class="px-6 py-3 bg-red-500/20 border border-red-500/50 rounded-xl text-red-300 font-medium hover:bg-red-500/30 transition-all duration-200">
        {{ __('Delete Account') }}
    </button>

    <x-modal name="confirm-user-deletion" :show="$errors->userDeletion->isNotEmpty()" focusable>
        <form method="post" action="{{ route('profile.destroy') }}" class="p-6 bg-gray-900 rounded-2xl">
            @csrf
            @method('delete')

            <div class="flex items-start mb-6">
                <div class="flex-shrink-0">
                    <svg class="w-12 h-12 text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                    </svg>
                </div>
                <div class="ml-4">
                    <h2 class="text-xl font-bold text-white">
                        {{ __('Are you sure you want to delete your account?') }}
                    </h2>
                    <p class="mt-2 text-sm text-gray-300">
                        {{ __('Once your account is deleted, all of its resources and data will be permanently deleted. Please enter your password to confirm you would like to permanently delete your account.') }}
                    </p>
                </div>
            </div>

            <div class="mb-6">
                <label for="password" class="text-gray-300 text-sm font-medium mb-2 block">{{ __('Password') }}</label>
                <input
                    id="password"
                    name="password"
                    type="password"
                    class="w-full bg-white/10 border border-white/20 rounded-xl px-4 py-3 text-white placeholder-gray-400 focus:outline-none focus:border-red-400 focus:ring-2 focus:ring-red-400/50 transition-all"
                    placeholder="{{ __('Enter your password') }}"
                />
                @if($errors->userDeletion->get('password'))
                    <p class="mt-2 text-sm text-red-400">{{ $errors->userDeletion->first('password') }}</p>
                @endif
            </div>

            <div class="flex justify-end space-x-3">
                <button type="button" x-on:click="$dispatch('close')"
                        class="px-6 py-3 bg-white/10 rounded-xl text-white font-medium hover:bg-white/20 transition-all duration-200">
                    {{ __('Cancel') }}
                </button>
                <button type="submit"
                        class="px-6 py-3 bg-gradient-to-r from-red-500 to-red-600 rounded-xl text-white font-medium hover:from-red-600 hover:to-red-700 transition-all duration-200 shadow-lg">
                    {{ __('Delete Account') }}
                </button>
            </div>
        </form>
    </x-modal>
</section>

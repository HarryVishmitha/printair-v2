<x-guest-layout>
    <div class="space-y-6">
        {{-- Title / intro --}}
        <div class="text-center mb-2">
            <h1 class="text-xl font-semibold text-slate-900">
                Reset your password
            </h1>
            <p class="mt-1 text-sm text-slate-600">
                Enter your email and a new password to regain access to your Printair account.
            </p>
        </div>

        {{-- Form --}}
        <form method="POST" action="{{ route('password.store') }}" class="space-y-4">
            @csrf

            {{-- Hidden reset token --}}
            <input type="hidden" name="token" value="{{ $request->route('token') }}">

            {{-- Email --}}
            <div>
                <x-input-label for="email" :value="__('Email')" />
                <x-text-input id="email" class="mt-1 block w-full" type="email" name="email" :value="old('email', $request->email)"
                    required autofocus autocomplete="username" />
                <x-input-error :messages="$errors->get('email')" class="mt-2" />
            </div>

            {{-- New password --}}
            <div>
                <div class="flex items-center justify-between">
                    <x-input-label for="password" :value="__('New password')" />
                    <span class="text-[11px] text-slate-400">
                        At least 8 characters recommended
                    </span>
                </div>
                <x-text-input id="password" class="mt-1 block w-full" type="password" name="password" required
                    autocomplete="new-password" />
                <x-input-error :messages="$errors->get('password')" class="mt-2" />
            </div>

            {{-- Confirm password --}}
            <div>
                <x-input-label for="password_confirmation" :value="__('Confirm new password')" />
                <x-text-input id="password_confirmation" class="mt-1 block w-full" type="password"
                    name="password_confirmation" required autocomplete="new-password" />
                <x-input-error :messages="$errors->get('password_confirmation')" class="mt-2" />
            </div>

            <div class="flex items-center justify-between pt-2">
                <a href="{{ route('login') }}"
                    class="text-xs underline text-slate-500 hover:text-slate-800 rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-amber-500">
                    {{ __('Back to login') }}
                </a>

                <x-primary-button class="ms-4">
                    {{ __('Reset Password') }}
                </x-primary-button>
            </div>
        </form>
    </div>
</x-guest-layout>

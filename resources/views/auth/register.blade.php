<x-guest-layout :seo="$seo">
    <div class="space-y-6">
        {{-- Title / Intro --}}
        <div class="text-center mb-2">
            <h1 class="text-xl font-semibold text-slate-900">
                Create your Printair account
            </h1>
            <p class="mt-1 text-sm text-slate-600">
                Sign up to manage quotations, orders, and your design files in one place.
            </p>
        </div>

        @if (session('error'))
            <div class="mb-4 rounded-md border border-red-200 bg-red-50 px-3 py-2 text-sm text-red-700">
                {{ session('error') }}
            </div>
        @endif


        {{-- Social logins --}}
        <div class="space-y-3">
            <h6 class="text-center font-semibold">Continue with</h6>
            <div class="grid grid-cols-1 gap-3 sm:grid-cols-2">
                {{-- Google --}}
                <a href="{{ route('social.redirect', ['provider' => 'google']) }}"
                    class="inline-flex items-center justify-center gap-2 rounded-lg border border-slate-200 bg-white px-3 py-2.5 text-sm font-medium text-slate-700 shadow-sm hover:bg-slate-50 transition">
                    <iconify-icon icon="logos:google-icon" class="w-4 h-4"></iconify-icon>
                    <span>Google</span>
                </a>

                {{-- Facebook --}}
                <a href="{{ route('social.redirect', ['provider' => 'facebook']) }}"
                    class="inline-flex items-center justify-center gap-2 rounded-lg border border-slate-200 bg-[#1877F2]/90 px-3 py-2.5 text-sm font-medium text-white shadow-sm hover:bg-[#1664c7] transition">
                    <iconify-icon icon="logos:facebook" class="w-4 h-4"></iconify-icon>
                    <span>Facebook</span>
                </a>
            </div>

            {{-- Divider --}}
            <div class="relative mt-4">
                <div class="absolute inset-0 flex items-center">
                    <div class="w-full border-t border-slate-200"></div>
                </div>
                <div class="relative flex justify-center">
                    <span class="bg-white px-2 text-[11px] font-medium uppercase tracking-wide text-slate-400">
                        Or create an account with email
                    </span>
                </div>
            </div>
        </div>

        {{-- Email registration form --}}
        <form method="POST" action="{{ route('register') }}" class="space-y-4">
            @csrf

            {{-- Name row --}}
            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                <!-- First Name -->
                <div>
                    <x-input-label for="first_name" :value="__('First Name')" />
                    <x-text-input id="first_name" class="block mt-1 w-full" type="text" name="first_name"
                        :value="old('first_name')" required autofocus autocomplete="given-name" />
                    <x-input-error :messages="$errors->get('first_name')" class="mt-2" />
                </div>

                <!-- Last Name -->
                <div>
                    <x-input-label for="last_name" :value="__('Last Name')" />
                    <x-text-input id="last_name" class="block mt-1 w-full" type="text" name="last_name"
                        :value="old('last_name')" required autocomplete="family-name" />
                    <x-input-error :messages="$errors->get('last_name')" class="mt-2" />
                </div>
            </div>

            <!-- WhatsApp Number -->
            <div>
                <div class="flex items-center justify-between">
                    <x-input-label for="whatsapp_number" :value="__('WhatsApp Number')" />
                    <span class="text-[11px] text-slate-400">Optional, for faster updates</span>
                </div>
                <x-text-input id="whatsapp_number" class="block mt-1 w-full" type="text" name="whatsapp_number"
                    :value="old('whatsapp_number')" autocomplete="tel" placeholder="+94 7X XXX XXXX" />
                <x-input-error :messages="$errors->get('whatsapp_number')" class="mt-2" />
            </div>

            <!-- Email Address -->
            <div>
                <x-input-label for="email" :value="__('Email')" />
                <x-text-input id="email" class="block mt-1 w-full" type="email" name="email" :value="old('email')"
                    required autocomplete="username" />
                <x-input-error :messages="$errors->get('email')" class="mt-2" />
            </div>

            <!-- Password -->
            <div>
                <div class="flex items-center justify-between">
                    <x-input-label for="password" :value="__('Password')" />
                    <span class="text-[11px] text-slate-400">
                        At least 8 characters recommended
                    </span>
                </div>

                <x-text-input id="password" class="block mt-1 w-full" type="password" name="password" required
                    autocomplete="new-password" />
                <x-input-error :messages="$errors->get('password')" class="mt-2" />
            </div>

            <!-- Confirm Password -->
            <div>
                <x-input-label for="password_confirmation" :value="__('Confirm Password')" />
                <x-text-input id="password_confirmation" class="block mt-1 w-full" type="password"
                    name="password_confirmation" required autocomplete="new-password" />
                <x-input-error :messages="$errors->get('password_confirmation')" class="mt-2" />
            </div>

            <div class="flex items-center justify-between pt-2">
                <a class="underline text-xs text-slate-500 hover:text-slate-800 rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-amber-500"
                    href="{{ route('login') }}">
                    {{ __('Already registered?') }}
                </a>

                <x-primary-button class="ms-4">
                    {{ __('Create account') }}
                </x-primary-button>
            </div>
        </form>
    </div>
</x-guest-layout>

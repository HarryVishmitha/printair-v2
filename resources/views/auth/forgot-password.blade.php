<x-guest-layout>
    <div class="space-y-6">
        {{-- Title / intro --}}
        <div class="text-center mb-2">
            <h1 class="text-xl font-semibold text-slate-900">
                Forgot your password?
            </h1>
            <p class="mt-1 text-sm text-slate-600">
                Enter the email address linked to your Printair account and we’ll send you a link to reset your
                password.
            </p>
        </div>

        {{-- Session Status --}}
        @if (session('status'))
            <div class="mb-4 rounded-md border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800">
                {{ session('status') }}
            </div>
        @endif

        {{-- Form --}}
        <form method="POST" action="{{ route('password.email') }}" class="space-y-4">
            @csrf

            {{-- Email --}}
            <div>
                <x-input-label for="email" :value="__('Email')" />
                <x-text-input id="email" class="mt-1 block w-full" type="email" name="email" :value="old('email')"
                    required autofocus />
                <x-input-error :messages="$errors->get('email')" class="mt-2" />
            </div>

            <div class="flex items-center justify-between pt-2">
                <a href="{{ route('login') }}"
                    class="text-xs underline text-slate-500 hover:text-slate-800 rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-amber-500">
                    {{ __('Back to login') }}
                </a>

                <x-primary-button class="ms-4">
                    {{ __('Email password reset link') }}
                </x-primary-button>
            </div>
        </form>
    </div>
</x-guest-layout>

<x-guest-layout :seo="$seo">
    <div class="space-y-6">

        {{-- Title / intro --}}
        <div class="text-center mb-2">
            <h1 class="text-xl font-semibold text-slate-900">
                Welcome back
            </h1>
            <p class="mt-1 text-sm text-slate-600">
                Sign in to access your Printair quotations, orders, and design files.
            </p>
        </div>

        {{-- Session Status --}}
        @if (session('status'))
            <div class="mb-4 rounded-md border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800">
                {{ session('status') }}
            </div>
        @endif
        <h6 class="text-center font-semibold">Continue with</h6>
        {{-- Social login --}}
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">

            <a href="{{ route('social.redirect', ['provider' => 'google']) }}"
                class="inline-flex items-center justify-center gap-2 rounded-lg border border-slate-200 bg-white px-3 py-2.5 text-sm font-medium text-slate-700 shadow-sm hover:bg-slate-50 transition">
                <iconify-icon icon="logos:google-icon" class="w-4 h-4"></iconify-icon>
                Google
            </a>

            <a href="{{ route('social.redirect', ['provider' => 'facebook']) }}"
                class="inline-flex items-center justify-center gap-2 rounded-lg border border-slate-200 bg-[#1877F2] px-3 py-2.5 text-sm font-medium text-white shadow-sm hover:bg-[#1664c7] transition">
                <iconify-icon icon="logos:facebook" class="w-4 h-4"></iconify-icon>
                Facebook
            </a>
        </div>

        {{-- Divider --}}
        <div class="relative mt-4">
            <div class="absolute inset-0 flex items-center">
                <div class="w-full border-t border-slate-200"></div>
            </div>
            <div class="relative flex justify-center">
                <span class="bg-white px-2 text-[11px] font-medium uppercase tracking-wide text-slate-400">
                    Or sign in with email
                </span>
            </div>
        </div>

        {{-- Login Form --}}
        <form method="POST" action="{{ route('login') }}" class="space-y-4">
            @csrf

            {{-- Email --}}
            <div class="space-y-1.5">
                <label for="email" class="text-sm font-medium text-slate-700">
                    Email
                </label>

                <input id="email" type="email" name="email" value="{{ old('email') }}" required autofocus
                    autocomplete="username"
                    class="w-full px-3.5 py-2.5 border border-slate-300 rounded-lg bg-white text-slate-900 placeholder-slate-400 shadow-sm
                           focus:outline-none focus:ring-2 focus:ring-amber-500/40 focus:border-amber-600 transition" />

                @error('email')
                    <p class="text-sm text-red-600 mt-1">{{ $message }}</p>
                @enderror
            </div>

            {{-- Password --}}
            <div class="space-y-1.5">
                <div class="flex items-center justify-between">
                    <label for="password" class="text-sm font-medium text-slate-700">
                        Password
                    </label>

                    @if (Route::has('password.request'))
                        <a href="{{ route('password.request') }}"
                            class="text-xs text-slate-500 hover:text-slate-800 underline-offset-2 underline transition">
                            Forgot?
                        </a>
                    @endif
                </div>

                <input id="password" type="password" name="password" required autocomplete="current-password"
                    class="w-full px-3.5 py-2.5 border border-slate-300 rounded-lg bg-white text-slate-900 placeholder-slate-400 shadow-sm
                           focus:outline-none focus:ring-2 focus:ring-amber-500/40 focus:border-amber-600 transition" />

                @error('password')
                    <p class="text-sm text-red-600 mt-1">{{ $message }}</p>
                @enderror
            </div>

            {{-- Remember me --}}
            <div class="flex items-center mt-2">
                <input id="remember_me" name="remember" type="checkbox"
                    class="h-4 w-4 rounded border-slate-300 text-amber-600 focus:ring-amber-500">
                <label for="remember_me" class="ml-2 text-sm text-slate-600">
                    Remember me
                </label>
            </div>

            {{-- Submit --}}
            <div class="flex items-center justify-between pt-3">
                <a href="{{ route('register') }}"
                    class="text-xs underline text-slate-500 hover:text-slate-800 transition">
                    Create an account
                </a>

                <button type="submit"
                    class="inline-flex items-center gap-2 rounded-lg bg-amber-600 text-white font-medium text-sm px-5 py-2.5 shadow-sm
                           hover:bg-amber-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-amber-500 transition">
                    Log in
                </button>
            </div>
        </form>

    </div>
</x-guest-layout>

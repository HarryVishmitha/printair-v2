<x-guest-layout>
    <div class="space-y-6">
        {{-- Title / intro --}}
        <div class="text-center mb-2">
            <h1 class="text-xl font-semibold text-slate-900">
                Confirm your password
            </h1>
            <p class="mt-1 text-sm text-slate-600">
                You’re entering a secure area. Please confirm your password to continue.
            </p>
        </div>

        {{-- Form --}}
        <form method="POST" action="{{ route('password.confirm') }}" class="space-y-5">
            @csrf

            {{-- Password --}}
            <div class="space-y-1.5">
                <label for="password" class="text-sm font-medium text-slate-700">
                    Password
                </label>

                <input id="password" name="password" type="password" required autocomplete="current-password"
                    class="w-full px-3.5 py-2.5 border border-slate-300 rounded-lg bg-white text-slate-900 placeholder-slate-400 shadow-sm
                           focus:outline-none focus:ring-2 focus:ring-amber-500/40 focus:border-amber-600 transition duration-150 ease-in-out" />

                @error('password')
                    <p class="text-sm text-red-600 mt-1">{{ $message }}</p>
                @enderror
            </div>

            {{-- Actions --}}
            <div class="flex justify-end pt-2">
                <button type="submit"
                    class="inline-flex items-center gap-2 rounded-lg bg-amber-600 text-white font-medium text-sm px-5 py-2.5 shadow-sm
                           hover:bg-amber-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-amber-500 transition">
                    Confirm
                </button>
            </div>
        </form>
    </div>
</x-guest-layout>

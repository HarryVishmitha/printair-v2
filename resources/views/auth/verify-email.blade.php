<x-guest-layout>
    <div class="space-y-6">
        {{-- Heading + intro --}}
        <div class="text-center">
            <h1 class="text-xl font-semibold text-slate-900">
                Verify your email address
            </h1>
            <p class="mt-2 text-sm text-slate-600">
                Thanks for signing up with Printair. We’ve sent a verification link to your email.
                Please confirm your email address to continue.
            </p>
        </div>

        {{-- Status message --}}
        @if (session('status') === 'verification-link-sent')
            <div class="rounded-md border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800">
                {{ __('A new verification link has been sent to the email address you provided during registration.') }}
            </div>
        @else
            <div class="rounded-md border border-slate-200 bg-slate-50 px-4 py-3 text-xs text-slate-600">
                {{ __('If you didn\'t receive the email, you can request another verification link below.') }}
            </div>
        @endif

        {{-- Actions --}}
        <div class="mt-2 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            {{-- Resend form --}}
            <form method="POST" action="{{ route('verification.send') }}" class="w-full sm:w-auto">
                @csrf

                <x-primary-button class="w-full sm:w-auto justify-center">
                    {{ __('Resend Verification Email') }}
                </x-primary-button>
            </form>

            {{-- Logout --}}
            <form method="POST" action="{{ route('logout') }}" class="w-full sm:w-auto text-right sm:text-left">
                @csrf

                <button type="submit"
                    class="inline-flex w-full sm:w-auto items-center justify-center rounded-md border border-slate-200 bg-white px-3 py-2 text-xs font-medium text-slate-600 hover:text-slate-900 hover:bg-slate-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-amber-500 transition">
                    {{ __('Log out') }}
                </button>
            </form>
        </div>

        {{-- Small helper text --}}
        <p class="mt-2 text-[11px] text-center text-slate-500">
            Didn’t get the email? Please check your spam or promotions folder before requesting a new link.
        </p>
    </div>
</x-guest-layout>

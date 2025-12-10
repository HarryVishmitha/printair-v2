<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-slate-800 leading-tight">
            Complete your contact details
        </h2>
    </x-slot>

    <div class="py-8">
        <div class="max-w-xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white shadow-sm border border-slate-200 sm:rounded-xl p-6 sm:p-8">
                <h3 class="text-lg font-semibold text-slate-900">
                    One last step, {{ $user->first_name }} 👋
                </h3>
                <p class="mt-2 text-sm text-slate-600">
                    We need your WhatsApp number so we can share quotation updates, proofs, and urgent notices.
                </p>

                @if (session('status'))
                    <div class="mt-4 text-sm text-emerald-600">
                        {{ session('status') }}
                    </div>
                @endif

                <form method="POST" action="{{ route('onboarding.contact.store') }}" class="mt-6 space-y-4">
                    @csrf

                    <div>
                        <x-input-label for="whatsapp_number" :value="__('WhatsApp Number')" />
                        <x-text-input id="whatsapp_number" name="whatsapp_number" type="text"
                            class="block mt-1 w-full" :value="old('whatsapp_number', $user->whatsapp_number)" required autocomplete="tel"
                            placeholder="+94 7X XXX XXXX" />
                        <x-input-error :messages="$errors->get('whatsapp_number')" class="mt-2" />
                    </div>

                    <div class="flex justify-end pt-2">
                        <x-primary-button>
                            {{ __('Save & continue') }}
                        </x-primary-button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>

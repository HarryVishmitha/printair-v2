{{-- resources/views/notifications/settings.blade.php --}}
<x-app-layout>
    <x-slot name="sectionTitle">Notifications</x-slot>
    <x-slot name="pageTitle">Notification Settings</x-slot>

    <x-slot name="breadcrumbs">
        <span class="text-slate-500">Home</span>
        <span class="mx-1">/</span>
        <a href="{{ route('notifications.index') }}" class="text-slate-500 hover:text-slate-800">Notifications</a>
        <span class="mx-1">/</span>
        <span class="text-slate-900 font-medium">Settings</span>
    </x-slot>

    <div class="max-w-2xl space-y-4">
        @if (session('status'))
            <div class="rounded-md border border-emerald-200 bg-emerald-50 text-emerald-800 text-sm px-4 py-3">
                {{ session('status') }}
            </div>
        @endif

        <div class="bg-white border border-slate-200 rounded-xl shadow-sm">
            <div class="border-b border-slate-100 px-5 py-4">
                <h2 class="text-sm font-semibold text-slate-900">Notification preferences</h2>
                <p class="mt-1 text-xs text-slate-500">
                    Choose how you want to be notified about activity in your Printair account.
                </p>
            </div>

            <form method="POST" action="{{ route('notifications.settings.update') }}" class="px-5 py-4 space-y-4">
                @csrf

                {{-- Email notifications --}}
                <div class="flex items-start gap-3">
                    <input type="checkbox" name="email_notifications" id="email_notifications" value="1"
                        class="mt-1 h-4 w-4 rounded border-slate-300 text-sky-600 focus:ring-sky-500"
                        {{ old('email_notifications', $user->email_notifications ?? true) ? 'checked' : '' }}>
                    <div class="flex-1">
                        <label for="email_notifications" class="text-sm font-medium text-slate-900">
                            Email notifications
                        </label>
                        <p class="text-xs text-slate-500">
                            Receive important alerts and updates to your registered email address.
                        </p>
                    </div>
                </div>

                {{-- System notifications --}}
                <div class="flex items-start gap-3">
                    <input type="checkbox" name="system_notifications" id="system_notifications" value="1"
                        class="mt-1 h-4 w-4 rounded border-slate-300 text-sky-600 focus:ring-sky-500"
                        {{ old('system_notifications', $user->system_notifications ?? true) ? 'checked' : '' }}>
                    <div class="flex-1">
                        <label for="system_notifications" class="text-sm font-medium text-slate-900">
                            In-app notifications
                        </label>
                        <p class="text-xs text-slate-500">
                            Show notifications inside the dashboard and notification center.
                        </p>
                    </div>
                </div>

                <div class="pt-3 flex justify-end">
                    <button type="submit"
                        class="inline-flex items-center justify-center rounded-lg bg-slate-900 px-4 py-2 text-xs font-medium text-white hover:bg-slate-800">
                        Save changes
                    </button>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>

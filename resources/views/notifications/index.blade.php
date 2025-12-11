{{-- resources/views/notifications/index.blade.php --}}
<x-app-layout>
    <x-slot name="sectionTitle">Notifications</x-slot>
    <x-slot name="pageTitle">Notification Center</x-slot>

    <x-slot name="breadcrumbs">
        <span class="text-slate-500">Home</span>
        <span class="mx-1">/</span>
        <span class="text-slate-900 font-medium">Notifications</span>
    </x-slot>

    <div class="space-y-4">

        {{-- Tabs + actions --}}
        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-3">
            <div class="inline-flex rounded-full border border-slate-200 bg-slate-50 p-1">
                <a href="{{ route('notifications.index') }}"
                    class="px-4 py-1.5 text-xs font-medium rounded-full
                        {{ $filter === 'all' ? 'bg-white text-slate-900 shadow-sm' : 'text-slate-500 hover:text-slate-900' }}">
                    All
                </a>
                <a href="{{ route('notifications.unread') }}"
                    class="px-4 py-1.5 text-xs font-medium rounded-full
                        {{ $filter === 'unread' ? 'bg-white text-slate-900 shadow-sm' : 'text-slate-500 hover:text-slate-900' }}">
                    Unread
                </a>
            </div>

            <div class="flex items-center gap-3">
                <a href="{{ route('notifications.settings') }}"
                    class="text-xs text-slate-500 hover:text-slate-900 underline underline-offset-4">
                    Notification settings
                </a>

                @if (auth()->user()->unreadNotifications()->count() > 0)
                    <form method="POST" action="{{ route('notifications.markAsRead') }}">
                        @csrf
                        <button type="submit"
                            class="inline-flex items-center gap-2 rounded-lg border border-slate-200 bg-white px-3 py-1.5 text-xs font-medium text-slate-700 hover:bg-slate-50">
                            <iconify-icon icon="solar:check-read-linear" class="text-sm"></iconify-icon>
                            Mark all as read
                        </button>
                    </form>
                @endif
            </div>
        </div>

        {{-- Notifications list --}}
        <div class="bg-white border border-slate-200 rounded-xl shadow-sm">
            @if ($notifications->count() > 0)
                <ul class="divide-y divide-slate-100">
                    @foreach ($notifications as $notification)
                        @php
                            $data = $notification->data ?? [];
                            $type = $data['type'] ?? 'info';
                            $isUnread = is_null($notification->read_at);

                            $colorClass = match ($type) {
                                'success' => 'text-emerald-500 bg-emerald-100',
                                'error' => 'text-rose-500 bg-rose-100',
                                'warning' => 'text-amber-500 bg-amber-100',
                                default => 'text-sky-500 bg-sky-100',
                            };

                            $icon = match ($type) {
                                'success' => 'solar:check-circle-bold',
                                'error' => 'solar:danger-circle-bold',
                                'warning' => 'solar:shield-warning-bold',
                                default => 'solar:info-circle-bold',
                            };
                        @endphp

                        <li class="px-4 py-3 {{ $isUnread ? 'bg-slate-50/60' : 'bg-white' }}">
                            <div class="flex items-start gap-3">
                                <div class="mt-1">
                                    <div
                                        class="h-9 w-9 rounded-full flex items-center justify-center {{ $colorClass }}">
                                        <iconify-icon icon="{{ $icon }}" class="text-lg"></iconify-icon>
                                    </div>
                                </div>

                                <div class="flex-1 min-w-0">
                                    <div class="flex items-center justify-between gap-2">
                                        <div class="flex items-center gap-2">
                                            <p class="text-sm font-medium text-slate-900">
                                                {{ $data['title'] ?? 'Notification' }}
                                            </p>

                                            @if ($isUnread)
                                                <span
                                                    class="text-[10px] font-semibold text-emerald-600 bg-emerald-50 border border-emerald-100 rounded-full px-2 py-0.5">
                                                    New
                                                </span>
                                            @endif
                                        </div>

                                        <span class="text-[11px] text-slate-400 whitespace-nowrap">
                                            {{ $notification->created_at->diffForHumans() }}
                                        </span>
                                    </div>

                                    <p class="text-xs text-slate-600 mt-1">
                                        {{ $data['message'] ?? '' }}
                                    </p>

                                    <div class="flex items-center gap-3 mt-2">
                                        @if (!empty($data['action_url']))
                                            <a href="{{ $data['action_url'] }}"
                                                class="inline-flex items-center gap-1 text-[11px] font-medium text-sky-600 hover:text-sky-800">
                                                <span>View details</span>
                                                <iconify-icon icon="solar:arrow-right-up-linear"
                                                    class="text-xs"></iconify-icon>
                                            </a>
                                        @endif

                                        @if ($isUnread)
                                            <form method="POST"
                                                action="{{ route('notifications.read', $notification->id) }}">
                                                @csrf
                                                <button type="submit"
                                                    class="text-[11px] text-slate-500 hover:text-slate-800">
                                                    Mark as read
                                                </button>
                                            </form>
                                        @endif>
                                    </div>
                                </div>
                            </div>
                        </li>
                    @endforeach
                </ul>

                <div class="border-t border-slate-100 px-4 py-3">
                    {{ $notifications->links() }}
                </div>
            @else
                <div class="px-6 py-10 text-center">
                    <div class="inline-flex items-center justify-center h-14 w-14 rounded-full bg-slate-50 mb-3">
                        <iconify-icon icon="solar:bell-off-linear" class="text-2xl text-slate-400"></iconify-icon>
                    </div>
                    <p class="text-sm font-medium text-slate-800 mb-1">
                        No notifications
                    </p>
                    <p class="text-xs text-slate-500">
                        You’ll see updates here when there are new activities in the system.
                    </p>
                </div>
            @endif
        </div>
    </div>
</x-app-layout>

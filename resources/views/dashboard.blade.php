<x-app-layout>
    {{-- Page Titles for the top bar --}}
    <x-slot name="sectionTitle">Overview</x-slot>
    <x-slot name="pageTitle">Dashboard</x-slot>

    {{-- Optional breadcrumbs (you can remove if not needed) --}}
    <x-slot name="breadcrumbs">
        <span class="text-slate-500">Home</span>
        <span class="mx-1">/</span>
        <span class="text-slate-900 font-medium">Dashboard</span>
    </x-slot>

    {{-- Main Dashboard Content --}}
    <div class="space-y-6">

        {{-- KPI Cards Row --}}
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
            <div class="bg-white border border-slate-200 rounded-xl p-4 h-28 animate-pulse"></div>
            <div class="bg-white border border-slate-200 rounded-xl p-4 h-28 animate-pulse"></div>
            <div class="bg-white border border-slate-200 rounded-xl p-4 h-28 animate-pulse"></div>
            <div class="bg-white border border-slate-200 rounded-xl p-4 h-28 animate-pulse"></div>
        </div>

        {{-- Two-column layout --}}
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

            {{-- Left section (Recent Orders placeholder) --}}
            <div class="bg-white border border-slate-200 rounded-xl p-4 h-72 animate-pulse lg:col-span-2"></div>

            {{-- Right section (Quick Stats placeholder) --}}
            <div class="bg-white border border-slate-200 rounded-xl p-4 h-72 animate-pulse"></div>
        </div>

        {{-- Full-width area (Charts / Activity logs placeholder) --}}
        <div class="bg-white border border-slate-200 rounded-xl p-4 h-80 animate-pulse"></div>

    </div>
</x-app-layout>

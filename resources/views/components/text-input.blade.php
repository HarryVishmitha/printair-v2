@props(['disabled' => false])

<input
    {{ $attributes->merge([
        'class' =>
            'w-full px-3.5 py-2.5 
                border border-slate-300 
                rounded-lg 
                bg-white text-slate-900
                placeholder-slate-400
                shadow-sm 
                focus:outline-none
                focus:ring-2 focus:ring-blue-500/50
                focus:border-amber-600
                transition duration-150 ease-in-out
                ' . ($disabled ? 'opacity-60 cursor-not-allowed bg-slate-100' : 'hover:border-slate-400'),
    ]) }}
    @disabled($disabled) />

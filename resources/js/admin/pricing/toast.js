export function toast(type, message) {
    const el = document.createElement('div');
    el.className =
        'fixed z-[9999] right-4 top-4 max-w-sm rounded-2xl px-4 py-3 text-sm shadow-lg border backdrop-blur ' +
        (type === 'success'
            ? 'bg-emerald-50/95 border-emerald-200 text-emerald-800'
            : 'bg-rose-50/95 border-rose-200 text-rose-800');
    el.textContent = message;
    document.body.appendChild(el);
    setTimeout(() => el.remove(), 2600);
}


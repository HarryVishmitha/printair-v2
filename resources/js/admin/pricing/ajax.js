function csrf() {
    const token = document
        .querySelector('meta[name="csrf-token"]')
        ?.getAttribute('content');
    if (!token) throw new Error('Missing CSRF token meta tag.');
    return token;
}

export async function ajax(url, { method = 'POST', body = null } = {}) {
    const res = await fetch(url, {
        method,
        headers: {
            'Content-Type': 'application/json',
            Accept: 'application/json',
            'X-CSRF-TOKEN': csrf(),
            'X-Requested-With': 'XMLHttpRequest',
        },
        body: body ? JSON.stringify(body) : null,
    });

    const data = await res.json().catch(() => ({}));

    if (!res.ok || data?.ok === false) {
        const msg = data?.message || 'Request failed.';
        const errors = data?.errors || null;
        const err = new Error(msg);
        err.errors = errors;
        throw err;
    }

    return data;
}

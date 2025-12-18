import { ajax } from './ajax';
import { toast } from './toast';

function isHubPage() {
    return !!document.querySelector('[data-pricing-hub]');
}

document.addEventListener('click', async (e) => {
    if (!isHubPage()) return;

    const btn = e.target.closest('[data-ajax-toggle]');
    if (!btn) return;

    e.preventDefault();

    const url = btn.dataset.url;
    const method = btn.dataset.method || 'PATCH';
    const payload = btn.dataset.payload ? JSON.parse(btn.dataset.payload) : {};

    const old = btn.innerHTML;
    btn.disabled = true;
    btn.classList.add('opacity-70', 'cursor-wait');
    btn.innerHTML = 'Saving…';

    try {
        const res = await ajax(url, { method, body: payload });
        toast('success', res.message || 'Updated.');
        window.location.reload();
    } catch (err) {
        toast('error', err.message || 'Failed.');
        btn.innerHTML = old;
        btn.disabled = false;
        btn.classList.remove('opacity-70', 'cursor-wait');
    }
});

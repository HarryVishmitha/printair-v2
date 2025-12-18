import { ajax } from './ajax';
import { toast } from './toast';

function qs(root, sel) {
    return root.querySelector(sel);
}
function qsa(root, sel) {
    return Array.from(root.querySelectorAll(sel));
}

function num(v) {
    if (v === '' || v === null || typeof v === 'undefined') return null;
    const n = Number(v);
    return Number.isFinite(n) ? n : null;
}

function boolFrom(el) {
    return !!el?.checked;
}

function updatePricePills(data) {
    const pillPublic = document.querySelector(`[data-price-pill="public"]`);
    const pillWg = document.querySelector(`[data-price-pill="wg"]`);
    if (pillPublic) {
        if (data?.public_price_label !== undefined) {
            pillPublic.textContent = data.public_price_label ?? '—';
        } else if (data?.public_price !== undefined) {
            pillPublic.textContent =
                data.public_price === null ? '—' : Number(data.public_price).toFixed(2);
        }
    }
    if (pillWg) {
        if (data?.wg_price_label !== undefined) {
            pillWg.textContent = data.wg_price_label ?? '—';
        } else if (data?.wg_price !== undefined) {
            pillWg.textContent =
                data.wg_price === null ? '—' : Number(data.wg_price).toFixed(2);
        }
    }
}

function collectRows(root, key) {
    const rows = qsa(root, `[data-row="${key}"]`);
    return rows.map((row) => {
        const id = row.dataset.rowId ? Number(row.dataset.rowId) : null;
        const fields = qsa(row, `[data-field^="${key}."]`);

        const obj = {};
        if (id) obj.id = id;

        fields.forEach((el) => {
            const field = el.dataset.field.replace(`${key}.`, '');
            if (el.type === 'checkbox') obj[field] = boolFrom(el);
            else if (el.tagName === 'SELECT') obj[field] = Number(el.value);
            else obj[field] = num(el.value);
        });

        ['min_qty', 'max_qty', 'variant_set_id', 'finishing_product_id', 'roll_id'].forEach(
            (k) => {
                if (obj[k] !== null && obj[k] !== undefined && obj[k] !== '')
                    obj[k] = Number(obj[k]);
            },
        );

        return obj;
    });
}

function addRowFromTemplate(key) {
    const tpl = document.getElementById(`tpl-${key}-row`);
    const container = document.querySelector(`[data-repeat="${key}"]`);
    if (!tpl || !container) return;

    const node = tpl.content.cloneNode(true);
    container.appendChild(node);
}

function removeRow(btn) {
    const row = btn.closest('[data-row]');
    if (!row) return;
    row.remove();
}

function setLoading(btn, yes) {
    if (yes) {
        btn.dataset._old = btn.innerHTML;
        btn.disabled = true;
        btn.classList.add('opacity-70', 'cursor-wait');
        btn.innerHTML = 'Saving…';
    } else {
        btn.disabled = false;
        btn.classList.remove('opacity-70', 'cursor-wait');
        btn.innerHTML = btn.dataset._old || btn.innerHTML;
    }
}

function applyPricingIds(data) {
    const publicId = data?.public_pricing_id;
    const wgId = data?.wg_pricing_id;

    if (publicId) {
        document
            .querySelectorAll('[data-needs-pricing-id="1"][data-context="public"]')
            .forEach((btn) => {
                btn.dataset.pricingId = String(publicId);
            });
    }

    if (wgId) {
        document
            .querySelectorAll('[data-needs-pricing-id="1"][data-context="wg"]')
            .forEach((btn) => {
                btn.dataset.pricingId = String(wgId);
            });
    }
}

async function saveBase(root, mode) {
    const payload = {};
    const baseUrl = root.dataset.baseUrl;
    const selectedWgId = root.dataset.workingGroupId ? Number(root.dataset.workingGroupId) : null;

    if (mode === 'public') {
        payload.context = 'public';
        if (selectedWgId) payload.working_group_id = selectedWgId;
        payload.base_price = num(qs(root, `[data-field="public.base_price"]`)?.value);
        payload.rate_per_sqft = num(qs(root, `[data-field="public.rate_per_sqft"]`)?.value);
        payload.offcut_rate_per_sqft = num(
            qs(root, `[data-field="public.offcut_rate_per_sqft"]`)?.value,
        );
        payload.min_charge = num(qs(root, `[data-field="public.min_charge"]`)?.value);
    } else {
        const wgId = root.dataset.workingGroupId;
        if (!wgId) throw new Error('Select a working group first.');

        payload.context = 'working_group';
        payload.working_group_id = Number(wgId);
        payload.base_price = num(qs(root, `[data-field="wg.base_price"]`)?.value);
        payload.rate_per_sqft = num(qs(root, `[data-field="wg.rate_per_sqft"]`)?.value);
        payload.offcut_rate_per_sqft = num(
            qs(root, `[data-field="wg.offcut_rate_per_sqft"]`)?.value,
        );
        payload.min_charge = num(qs(root, `[data-field="wg.min_charge"]`)?.value);

        payload.override_base = boolFrom(qs(root, `[data-field="wg.override_base"]`));
        payload.override_variants = boolFrom(qs(root, `[data-field="wg.override_variants"]`));
        payload.override_finishings = boolFrom(qs(root, `[data-field="wg.override_finishings"]`));
    }

    const res = await ajax(baseUrl, { method: 'PATCH', body: payload });
    toast('success', res.message || 'Saved.');
    updatePricePills(res.data);
    applyPricingIds(res.data);
}

async function syncCollection(root, key, url, pricingId) {
    if (!pricingId) throw new Error('Missing pricing ID. Create base pricing first.');

    const rows = collectRows(root, key);
    const selectedWgId = root.dataset.workingGroupId ? Number(root.dataset.workingGroupId) : null;

    let body;
    if (key === 'tiers') {
        body = { product_pricing_id: Number(pricingId), tiers: rows };
    } else {
        body = { product_pricing_id: Number(pricingId), rows };
    }
    if (selectedWgId) body.working_group_id = selectedWgId;

    const res = await ajax(url, { method: 'PATCH', body });
    toast('success', res.message || 'Saved.');
    updatePricePills(res.data);
}

function flipAvailabilityButton(btn, enabled) {
    btn.textContent = enabled ? 'Enabled' : 'Disabled';
    btn.dataset.payload = JSON.stringify({
        working_group_id: JSON.parse(btn.dataset.payload || '{}')?.working_group_id,
        is_enabled: enabled ? 0 : 1,
    });

    btn.classList.remove(
        'border-emerald-200',
        'text-emerald-700',
        'hover:bg-emerald-50',
        'border-slate-200',
        'text-slate-700',
        'hover:bg-slate-50',
        'bg-white',
    );

    btn.classList.add('bg-white');
    if (enabled) {
        btn.classList.add('border-emerald-200', 'text-emerald-700', 'hover:bg-emerald-50');
    } else {
        btn.classList.add('border-slate-200', 'text-slate-700', 'hover:bg-slate-50');
    }
}

document.addEventListener('click', async (e) => {
    const root = document.querySelector('[data-pricing-manager]');
    if (!root) return;

    const toggleBtn = e.target.closest('[data-ajax-toggle]');
    if (toggleBtn) {
        e.preventDefault();
        const url = toggleBtn.dataset.url;
        const method = toggleBtn.dataset.method || 'PATCH';
        const payload = toggleBtn.dataset.payload ? JSON.parse(toggleBtn.dataset.payload) : {};

        const old = toggleBtn.innerHTML;
        toggleBtn.disabled = true;
        toggleBtn.classList.add('opacity-70', 'cursor-wait');
        toggleBtn.innerHTML = 'Saving…';

        try {
            const res = await ajax(url, { method, body: payload });
            toast('success', res.message || 'Updated.');

            if (res?.data && typeof res.data.is_enabled === 'boolean') {
                flipAvailabilityButton(toggleBtn, res.data.is_enabled);
            } else {
                toggleBtn.innerHTML = old;
            }
        } catch (err) {
            toast('error', err.message || 'Failed.');
            toggleBtn.innerHTML = old;
        } finally {
            toggleBtn.disabled = false;
            toggleBtn.classList.remove('opacity-70', 'cursor-wait');
        }

        return;
    }

    const addBtn = e.target.closest('[data-row-add]');
    if (addBtn) {
        e.preventDefault();
        addRowFromTemplate(addBtn.dataset.rowAdd);
        return;
    }

    const rmBtn = e.target.closest('[data-row-remove]');
    if (rmBtn) {
        e.preventDefault();
        removeRow(rmBtn);
        return;
    }

    const saveBtn = e.target.closest('[data-ajax-save]');
    if (!saveBtn) return;

    e.preventDefault();
    const action = saveBtn.dataset.ajaxSave;

    try {
        setLoading(saveBtn, true);

        if (action === 'base-public') await saveBase(root, 'public');
        if (action === 'base-wg') await saveBase(root, 'wg');

        if (action === 'finishings-sync') {
            await syncCollection(root, 'finishings', root.dataset.finishingsUrl, saveBtn.dataset.pricingId);
        }
        if (action === 'rolls-sync') {
            await syncCollection(root, 'rolls', root.dataset.rollsUrl, saveBtn.dataset.pricingId);
        }
        if (action === 'tiers-sync') {
            await syncCollection(root, 'tiers', root.dataset.tiersUrl, saveBtn.dataset.pricingId);
        }
        if (action === 'variants-sync') {
            await syncCollection(root, 'variants', root.dataset.variantsPricingUrl, saveBtn.dataset.pricingId);
        }
    } catch (err) {
        toast('error', err.message || 'Failed.');
    } finally {
        setLoading(saveBtn, false);
    }
});

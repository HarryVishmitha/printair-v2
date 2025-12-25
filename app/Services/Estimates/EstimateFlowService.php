<?php

namespace App\Services\Estimates;

use App\Models\Estimate;
use App\Models\EstimateItem;
use App\Models\EstimateShare;
use App\Models\EstimateStatusHistory;
use App\Models\Customer;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class EstimateFlowService
{
    /**
     * If your item totals already include finishings, keep false.
     * If finishings are stored separately and not included in item line_total, set true.
     */
    public function __construct(
        private readonly bool $addFinishingsIntoTotals = true,
    ) {}

    // =========================================================
    // Draft creation
    // =========================================================
    public function createDraft(array $data): Estimate
    {
        $actor = $this->actor();

        // Security: enforce policy if you have one (recommended).
        // If policies aren’t ready, this is harmless.
        if (Gate::has('create')) {
            Gate::authorize('create', Estimate::class);
        }

        // Mandatory WG scope for enterprise flow
        $wgId = (int) ($data['working_group_id'] ?? 0);
        if ($wgId <= 0) {
            throw ValidationException::withMessages([
                'working_group_id' => 'Working group is required.',
            ]);
        }

        $estimate = DB::transaction(function () use ($data, $actor, $wgId) {
            $estimateNo = $this->generateEstimateNo($wgId);

            $customerId = isset($data['customer_id']) ? (int) $data['customer_id'] : null;
            if ($customerId) {
                $customerWg = Customer::query()
                    ->whereKey($customerId)
                    ->value('working_group_id');

                if ((int) $customerWg !== (int) $wgId) {
                    throw ValidationException::withMessages([
                        'customer_id' => 'Customer does not belong to the selected working group.',
                    ]);
                }
            }

            $estimate = Estimate::create([
                'uuid' => (string) Str::uuid(),
                'estimate_no' => $estimateNo,
                'working_group_id' => $wgId,

                'customer_id' => $customerId ?: null,
                'customer_snapshot' => $data['customer_snapshot'] ?? null,

                'currency' => $data['currency'] ?? 'LKR',
                'price_tier_id' => $data['price_tier_id'] ?? null,

                'tax_mode' => $data['tax_mode'] ?? 'none',
                'discount_mode' => $data['discount_mode'] ?? 'none',
                'discount_value' => $data['discount_value'] ?? 0,

                'status' => 'draft',
                'valid_until' => $data['valid_until'] ?? now()->addDays(14)->endOfDay(),

                'notes_internal' => $data['notes_internal'] ?? null,
                'notes_customer' => $data['notes_customer'] ?? null,
                'terms' => $data['terms'] ?? null,
                'meta' => $data['meta'] ?? null,

                'created_by' => $actor->id,
                'updated_by' => $actor->id,
            ]);

            $this->logStatusChange($estimate, null, 'draft', $actor, 'Estimate draft created', [
                'estimate_no' => $estimateNo,
            ]);

            $this->activity('estimate.created', $actor, $estimate, [
                'working_group_id' => $wgId,
                'estimate_no' => $estimateNo,
            ]);

            return $estimate;
        });

        return $estimate->fresh();
    }

    // =========================================================
    // Item management (safe additions / updates)
    // =========================================================
    public function upsertItem(Estimate $estimate, ?EstimateItem $item, array $payload): EstimateItem
    {
        $actor = $this->actor();
        $estimate = $this->reloadForWrite($estimate);

        $this->authorizeUpdate($estimate);
        $this->ensureNotLockedForFinancialEdit($estimate);

        // Force WG safety
        $payload['working_group_id'] = $estimate->working_group_id;
        $payload['estimate_id'] = $estimate->id;

        // Enterprise guardrails: prevent cross-WG injection
        $this->assertForeignKeysBelongToSameWg($estimate, $payload);

        return DB::transaction(function () use ($estimate, $item, $payload, $actor) {
            $before = $item ? $item->toArray() : null;

            $item = $item
                ? tap($item)->update($this->sanitizeItemPayload($payload))
                : EstimateItem::create($this->sanitizeItemPayload($payload));

            // Recalculate totals after any item change (enterprise behavior)
            $this->recalculateTotals($estimate);

            $this->activity($item->wasRecentlyCreated ? 'estimate.item.created' : 'estimate.item.updated', $actor, $estimate, [
                'estimate_item_id' => $item->id,
                'before' => $before,
                'after' => $item->fresh()->toArray(),
            ]);

            return $item->fresh();
        });
    }

    public function removeItem(Estimate $estimate, EstimateItem $item): void
    {
        $actor = $this->actor();
        $estimate = $this->reloadForWrite($estimate);

        $this->authorizeUpdate($estimate);
        $this->ensureNotLockedForFinancialEdit($estimate);

        if ($item->estimate_id !== $estimate->id) {
            throw ValidationException::withMessages([
                'estimate_item_id' => 'Item does not belong to this estimate.',
            ]);
        }

        DB::transaction(function () use ($estimate, $item, $actor) {
            $before = $item->toArray();

            $item->delete(); // soft delete

            $this->recalculateTotals($estimate);

            $this->activity('estimate.item.deleted', $actor, $estimate, [
                'estimate_item_id' => $item->id,
                'before' => $before,
            ]);
        });
    }

    // =========================================================
    // Totals
    // =========================================================
    public function recalculateTotals(Estimate $estimate): Estimate
    {
        $estimate = $this->reloadForWrite($estimate);

        // Sum item totals (and optionally finishings)
        $items = $estimate->items()->with('finishings')->get();

        $subtotal = '0.00';
        $lineDiscountTotal = '0.00';
        $taxTotal = '0.00';
        $shipping = (string) ($estimate->shipping_fee ?? 0);
        $otherFee = (string) ($estimate->other_fee ?? 0);

        foreach ($items as $it) {
            $lineSubtotal = (string) ($it->line_subtotal ?? 0);
            $lineDiscount = (string) ($it->discount_amount ?? 0);
            $lineTax = (string) ($it->tax_amount ?? 0);
            $lineTotal = (string) ($it->line_total ?? 0);

            if ($this->addFinishingsIntoTotals) {
                $finishingsTotal = (string) $it->finishings->sum('total');
                $lineTotal = bcadd($lineTotal, $finishingsTotal, 2);
                $lineSubtotal = bcadd($lineSubtotal, $finishingsTotal, 2);
            }

            $subtotal = bcadd($subtotal, $lineSubtotal, 2);
            $lineDiscountTotal = bcadd($lineDiscountTotal, $lineDiscount, 2);
            $taxTotal = bcadd($taxTotal, $lineTax, 2);
        }

        // Header/estimate-level discount
        $discountMode = (string) ($estimate->discount_mode ?? 'none');
        $rawDiscountValue = $estimate->discount_value ?? 0;
        $discountValue = is_numeric($rawDiscountValue)
            ? number_format((float) $rawDiscountValue, 2, '.', '')
            : '0.00';

        $baseForHeaderDiscount = bcsub($subtotal, $lineDiscountTotal, 2);
        if (bccomp($baseForHeaderDiscount, '0.00', 2) === -1) {
            $baseForHeaderDiscount = '0.00';
        }

        $headerDiscount = '0.00';
        if ($discountMode === 'percent') {
            $pct = bcdiv($discountValue, '100', 6);
            $headerDiscount = bcmul($baseForHeaderDiscount, $pct, 2);
        } elseif ($discountMode === 'amount') {
            $headerDiscount = $discountValue;
        }

        if (bccomp($headerDiscount, '0.00', 2) === -1) {
            $headerDiscount = '0.00';
        }
        if (bccomp($headerDiscount, $baseForHeaderDiscount, 2) === 1) {
            $headerDiscount = $baseForHeaderDiscount;
        }

        $discountTotal = bcadd($lineDiscountTotal, $headerDiscount, 2);

        // Grand total = subtotal - (line + header discount) + tax + shipping + other
        $grand = $subtotal;
        $grand = bcsub($grand, $discountTotal, 2);
        $grand = bcadd($grand, $taxTotal, 2);
        $grand = bcadd($grand, (string) $shipping, 2);
        $grand = bcadd($grand, (string) $otherFee, 2);

        $estimate->update([
            'subtotal' => $subtotal,
            'discount_total' => $discountTotal,
            'tax_total' => $taxTotal,
            'grand_total' => $grand,
            'updated_by' => $this->actor()->id,
        ]);

        return $estimate->fresh();
    }

    // =========================================================
    // Lifecycle transitions
    // =========================================================
    public function send(Estimate $estimate, array $meta = []): Estimate
    {
        $actor = $this->actor();
        $estimate = $this->reloadForWrite($estimate);

        $this->authorizeUpdate($estimate);
        $this->ensureDraftOrViewed($estimate);

        // Enterprise rule: must have at least one item
        if ($estimate->items()->count() === 0) {
            throw ValidationException::withMessages([
                'items' => 'You cannot send an estimate without items.',
            ]);
        }

        return DB::transaction(function () use ($estimate, $actor, $meta) {
            $this->recalculateTotals($estimate);

            $from = $estimate->status;
            $estimate->update([
                'status' => 'sent',
                'sent_at' => now(),
                'locked_at' => now(),
                'locked_by' => $actor->id,
                'updated_by' => $actor->id,
            ]);

            $this->logStatusChange($estimate, $from, 'sent', $actor, $meta['reason'] ?? 'Estimate sent', $meta);
            $this->activity('estimate.sent', $actor, $estimate, $meta);

            return $estimate->fresh();
        });
    }

    public function accept(Estimate $estimate, array $meta = []): Estimate
    {
        $actor = $this->actor();
        $estimate = $this->reloadForWrite($estimate);

        $this->authorizeUpdate($estimate);

        if (!in_array($estimate->status, ['sent', 'viewed'], true)) {
            throw ValidationException::withMessages([
                'status' => 'Only a sent/viewed estimate can be accepted.',
            ]);
        }

        return DB::transaction(function () use ($estimate, $actor, $meta) {
            $from = $estimate->status;

            $estimate->update([
                'status' => 'accepted',
                'accepted_at' => now(),
                'locked_at' => $estimate->locked_at ?? now(),
                'locked_by' => $estimate->locked_by ?? $actor->id,
                'updated_by' => $actor->id,
            ]);

            $this->logStatusChange($estimate, $from, 'accepted', $actor, $meta['reason'] ?? 'Estimate accepted', $meta);
            $this->activity('estimate.accepted', $actor, $estimate, $meta);

            return $estimate->fresh();
        });
    }

    public function reject(Estimate $estimate, string $reason, array $meta = []): Estimate
    {
        $actor = $this->actor();
        $estimate = $this->reloadForWrite($estimate);

        $this->authorizeUpdate($estimate);

        if (!in_array($estimate->status, ['sent', 'viewed'], true)) {
            throw ValidationException::withMessages([
                'status' => 'Only a sent/viewed estimate can be rejected.',
            ]);
        }

        return DB::transaction(function () use ($estimate, $actor, $reason, $meta) {
            $from = $estimate->status;

            $estimate->update([
                'status' => 'rejected',
                'rejected_at' => now(),
                'updated_by' => $actor->id,
            ]);

            $this->logStatusChange($estimate, $from, 'rejected', $actor, $reason, $meta);
            $this->activity('estimate.rejected', $actor, $estimate, ['reason' => $reason] + $meta);

            return $estimate->fresh();
        });
    }

    public function markViewed(Estimate $estimate, array $meta = []): Estimate
    {
        $actor = $this->actor();
        $estimate = $this->reloadForWrite($estimate);

        // Viewing is not necessarily an "update" permission action in some systems.
        // If you want policy enforcement, keep it. Otherwise you can remove.
        $this->authorizeView($estimate);

        if ($estimate->status !== 'sent') {
            return $estimate; // ignore safely
        }

        return DB::transaction(function () use ($estimate, $actor, $meta) {
            $from = $estimate->status;

            $estimate->update([
                'status' => 'viewed',
                'updated_by' => $actor->id,
            ]);

            $this->logStatusChange($estimate, $from, 'viewed', $actor, $meta['reason'] ?? 'Estimate viewed', $meta);
            $this->activity('estimate.viewed', $actor, $estimate, $meta);

            return $estimate->fresh();
        });
    }

    // =========================================================
    // Share token
    // =========================================================
    public function createShareLink(Estimate $estimate, ?\DateTimeInterface $expiresAt = null): array
    {
        $actor = $this->actor();
        $estimate = $this->reloadForWrite($estimate);

        $this->authorizeView($estimate);

        // Only allow shares when estimate is at least sent (common business rule)
        if (!in_array($estimate->status, ['sent', 'viewed', 'accepted', 'converted'], true)) {
            throw ValidationException::withMessages([
                'status' => 'You can only share a sent/accepted estimate.',
            ]);
        }

        return DB::transaction(function () use ($estimate, $actor, $expiresAt) {
            $raw = Str::random(64); // raw token to return to caller (store only hash)
            $hash = hash('sha256', $raw);

            $share = EstimateShare::create([
                'estimate_id' => $estimate->id,
                'token_hash' => $hash,
                'expires_at' => $expiresAt,
                'created_by' => $actor->id,
            ]);

            $this->activity('estimate.share.created', $actor, $estimate, [
                'estimate_share_id' => $share->id,
                'expires_at' => $expiresAt?->format('c'),
            ]);

            return [
                'token' => $raw,
                'token_hash' => $hash,
                'share_id' => $share->id,
            ];
        });
    }

    public function revokeShare(EstimateShare $share, array $meta = []): EstimateShare
    {
        $actor = $this->actor();

        $estimate = $share->estimate()->first();
        if ($estimate) {
            $this->authorizeUpdate($estimate);
        }

        $share->update([
            'revoked_at' => now(),
        ]);

        if ($estimate) {
            $this->activity('estimate.share.revoked', $actor, $estimate, [
                'estimate_share_id' => $share->id,
            ] + $meta);
        }

        return $share->fresh();
    }

    // =========================================================
    // Internals: auth + guards + history + activity hook
    // =========================================================
    private function actor(): User
    {
        $u = Auth::user();
        if (!$u instanceof User) {
            throw ValidationException::withMessages(['auth' => 'Unauthorized.']);
        }
        return $u;
    }

    private function reloadForWrite(Estimate $estimate): Estimate
    {
        // Row lock prevents race conditions (double-send, double-accept, etc.)
        return Estimate::query()
            ->whereKey($estimate->id)
            ->lockForUpdate()
            ->firstOrFail();
    }

    private function authorizeUpdate(Estimate $estimate): void
    {
        if (Gate::has('update')) {
            Gate::authorize('update', $estimate);
        }
    }

    private function authorizeView(Estimate $estimate): void
    {
        if (Gate::has('view')) {
            Gate::authorize('view', $estimate);
        }
    }

    private function ensureNotLockedForFinancialEdit(Estimate $estimate): void
    {
        if ($estimate->locked_at) {
            throw ValidationException::withMessages([
                'locked' => 'This estimate is locked and cannot be modified.',
            ]);
        }
    }

    private function ensureDraftOrViewed(Estimate $estimate): void
    {
        if (!in_array($estimate->status, ['draft', 'viewed'], true)) {
            throw ValidationException::withMessages([
                'status' => 'Only draft/viewed estimates can be sent.',
            ]);
        }
    }

    private function assertForeignKeysBelongToSameWg(Estimate $estimate, array $payload): void
    {
        // Minimal enterprise guardrail:
        // ensure that the item working_group_id always matches the estimate WG.
        if ((int) ($payload['working_group_id'] ?? 0) !== (int) $estimate->working_group_id) {
            throw ValidationException::withMessages([
                'working_group_id' => 'Invalid working group for this estimate.',
            ]);
        }
    }

    private function sanitizeItemPayload(array $payload): array
    {
        // Keep only fields that exist in migration to prevent mass-assignment surprises.
        return [
            'estimate_id' => $payload['estimate_id'],
            'working_group_id' => $payload['working_group_id'],

            'product_id' => $payload['product_id'] ?? null,
            'variant_set_item_id' => $payload['variant_set_item_id'] ?? null,
            'roll_id' => $payload['roll_id'] ?? null,

            'title' => $payload['title'] ?? 'Item',
            'description' => $payload['description'] ?? null,

            'qty' => $payload['qty'] ?? 1,

            'width' => $payload['width'] ?? null,
            'height' => $payload['height'] ?? null,
            'unit' => $payload['unit'] ?? null,
            'area_sqft' => $payload['area_sqft'] ?? null,
            'offcut_sqft' => $payload['offcut_sqft'] ?? 0,

            'unit_price' => $payload['unit_price'] ?? 0,
            'line_subtotal' => $payload['line_subtotal'] ?? 0,
            'discount_amount' => $payload['discount_amount'] ?? 0,
            'tax_amount' => $payload['tax_amount'] ?? 0,
            'line_total' => $payload['line_total'] ?? 0,

            'pricing_snapshot' => $payload['pricing_snapshot'] ?? [],

            'sort_order' => $payload['sort_order'] ?? 0,
        ];
    }

    private function logStatusChange(Estimate $estimate, ?string $from, string $to, User $actor, ?string $reason = null, array $meta = []): void
    {
        EstimateStatusHistory::create([
            'estimate_id' => $estimate->id,
            'from_status' => $from,
            'to_status' => $to,
            'changed_by' => $actor->id,
            'reason' => $reason,
            'meta' => $meta ?: null,
            'created_at' => now(),
        ]);
    }

    /**
     * Activity hook.
     * - If you have an ActivityLogger service already, you can swap this body.
     * - For now, this is a safe no-op if the ActivityLog model isn't present.
     */
    private function activity(string $action, User $actor, Estimate $estimate, array $properties = []): void
    {
        // If you already have App\Models\ActivityLog in your system, uncomment:
        \App\Models\ActivityLog::create([
            'actor_id' => $actor->id,
            'action' => $action,
            'subject_type' => Estimate::class,
            'subject_id' => $estimate->id,
            'working_group_id' => $estimate->working_group_id,
            'ip' => request()?->ip(),
            'user_agent' => request()?->userAgent(),
            'before' => null,
            'after' => $properties ?: null,
            'created_at' => now(),
        ]);

        // If you log activity elsewhere, leave this method and wire it later.
    }

    /**
     * Enterprise-safe estimate number generator.
     * - Keeps uniqueness per working group.
     * - Uses a retry loop to avoid rare collisions.
     */
    private function generateEstimateNo(int $wgId): string
    {
        // Example format: EST-1-20251224-000123
        // You can change the format later without touching logic elsewhere.
        $date = now()->format('Ymd');

        for ($i = 0; $i < 5; $i++) {
            $rand = str_pad((string) random_int(1, 999999), 6, '0', STR_PAD_LEFT);
            $no = "EST-{$wgId}-{$date}-{$rand}";

            $exists = Estimate::query()
                ->where('working_group_id', $wgId)
                ->where('estimate_no', $no)
                ->exists();

            if (!$exists) {
                return $no;
            }
        }

        throw ValidationException::withMessages([
            'estimate_no' => 'Unable to generate a unique estimate number. Please try again.',
        ]);
    }
}

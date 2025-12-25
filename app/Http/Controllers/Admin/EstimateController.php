<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\AcceptEstimateRequest;
use App\Http\Requests\RejectEstimateRequest;
use App\Http\Requests\SendEstimateRequest;
use App\Http\Requests\StoreEstimateRequest;
use App\Http\Requests\UpsertEstimateItemRequest;
use App\Models\Customer;
use App\Models\Estimate;
use App\Models\EstimateItem;
use App\Models\EstimateShare;
use App\Models\Product;
use App\Models\WorkingGroup;
use App\Services\Estimates\EstimateDeliveryService;
use App\Services\Estimates\EstimateFlowService;
use App\Services\Estimates\EstimatePdfService;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Carbon;
use Illuminate\Validation\ValidationException;

class EstimateController extends Controller
{
    use AuthorizesRequests;
    public function __construct(
        private readonly EstimateFlowService $service,
        private readonly EstimateDeliveryService $delivery,
        private readonly EstimatePdfService $pdf,
    ) {}

    public function index(Request $request)
    {
        $filters = [
            'search' => trim((string) $request->get('search', '')),
            'status' => trim((string) $request->get('status', '')),
            'working_group_id' => $request->integer('working_group_id') ?: null,
            'from' => trim((string) $request->get('from', '')),
            'to' => trim((string) $request->get('to', '')),
        ];

        $workingGroups = WorkingGroup::query()
            ->orderBy('name')
            ->get(['id', 'name']);

        $listQuery = Estimate::query()
            ->with(['customer', 'workingGroup'])
            ->withCount('items')
            ->latest('id');

        $this->applyIndexFilters($listQuery, $filters, true);

        $estimates = $listQuery->paginate(20)->withQueryString();

        $kpiQuery = Estimate::query();
        $this->applyIndexFilters($kpiQuery, $filters, false);
        $kpis = $this->buildKpis($kpiQuery);

        return view('admin.estimates.index', [
            'estimates' => $estimates,
            'filters' => $filters,
            'kpis' => $kpis,
            'workingGroups' => $workingGroups,
        ]);
    }

    public function create(Request $request)
    {
        $this->authorize('create', Estimate::class);

        $workingGroups = WorkingGroup::query()
            ->orderBy('name')
            ->get(['id', 'name']);

        $customers = Customer::query()
            ->orderBy('full_name')
            ->limit(500)
            ->get(['id', 'full_name', 'phone', 'email', 'working_group_id', 'type', 'status']);

        // Products list (with WG-aware pricing) is fetched via `admin.estimates.products` in the form UI.
        $products = collect();

        $mode = 'create';
        $estimate = null;

        return view('admin.estimates.form', compact('mode', 'estimate', 'workingGroups', 'customers', 'products'));
    }

    public function show(Estimate $estimate)
    {
        $this->authorize('view', $estimate);

        $estimate->load([
            'customer',
            'workingGroup',
            'priceTier',
            'items.finishings.option',
            'items.product.primaryImage',
            'items.roll',
            'items.variantSetItem',
            'statusHistories.changedBy',
            'shares.createdBy',
            'createdBy',
            'updatedBy',
            'lockedBy',
            'order',
        ]);

        return view('admin.estimates.show', compact('estimate'));
    }

    public function downloadPdf(Estimate $estimate)
    {
        $this->authorize('view', $estimate);

        $publicUrl = null;

        // For admin-generated PDFs, prefer a public share link (token-based) when allowed.
        if (in_array((string) ($estimate->status ?? 'draft'), ['sent', 'viewed', 'accepted', 'converted'], true)) {
            $expiresAt = $estimate->valid_until ? Carbon::parse($estimate->valid_until) : null;
            $share = $this->service->createShareLink($estimate, $expiresAt);
            $publicUrl = route('estimates.public.show', ['token' => $share['token']]);
        }

        // Never embed admin-only URLs in the PDF.
        $publicUrl = is_string($publicUrl) ? $publicUrl : '';
        $pdfBytes = $this->pdf->render($estimate, $publicUrl);

        $no = $estimate->estimate_no ?: ('EST-'.$estimate->id);
        $safe = preg_replace('/[^A-Za-z0-9._-]+/', '-', $no) ?: 'estimate';
        $filename = $safe.'.pdf';

        return response($pdfBytes, 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'attachment; filename="'.$filename.'"',
            'Cache-Control' => 'no-store, private',
        ]);
    }

    public function store(StoreEstimateRequest $request)
    {
        $validated = $request->validated();

        $items = [];
        if ($request->expectsJson()) {
            $items = (array) ($request->validate([
                'items' => ['sometimes', 'array'],
                'items.*.id' => ['nullable', 'integer'],
                'items.*.product_id' => ['required', 'integer', 'exists:products,id'],
                'items.*.title' => ['required', 'string', 'max:255'],
                'items.*.description' => ['nullable', 'string'],
                'items.*.qty' => ['required', 'integer', 'min:1'],
                'items.*.width' => ['nullable', 'numeric', 'min:0.01'],
                'items.*.height' => ['nullable', 'numeric', 'min:0.01'],
                'items.*.unit' => ['nullable', 'string', 'in:in,ft,mm,cm,m'],
                'items.*.area_sqft' => ['nullable', 'numeric', 'min:0'],
                'items.*.offcut_sqft' => ['nullable', 'numeric', 'min:0'],
                'items.*.roll_id' => ['nullable', 'integer', 'exists:rolls,id'],
                'items.*.pricing_snapshot' => ['nullable', 'array'],
                'items.*.unit_price' => ['required', 'numeric', 'min:0'],
                'items.*.line_subtotal' => ['required', 'numeric', 'min:0'],
                'items.*.discount_amount' => ['nullable', 'numeric', 'min:0'],
                'items.*.tax_amount' => ['nullable', 'numeric', 'min:0'],
                'items.*.line_total' => ['required', 'numeric', 'min:0'],
            ])['items'] ?? []);
        }

        $estimate = $this->service->createDraft($validated);

        if (!empty($items)) {
            $this->syncItemsFromForm($estimate, $items);
        }

        $this->service->recalculateTotals($estimate);

        if ($request->expectsJson()) {
            return response()->json([
                'id' => $estimate->id,
                'redirect_url' => route('admin.estimates.show', $estimate),
            ]);
        }

        return redirect()
            ->route('admin.estimates.show', $estimate)
            ->with('success', 'Estimate draft created.');
    }

    public function edit(Estimate $estimate)
    {
        $this->authorize('view', $estimate);

        $estimate->load(['items']);

        $workingGroups = WorkingGroup::query()
            ->orderBy('name')
            ->get(['id', 'name']);

        $customers = Customer::query()
            ->orderBy('full_name')
            ->limit(500)
            ->get(['id', 'full_name', 'phone', 'email', 'working_group_id', 'type', 'status']);

        // Products list (with WG-aware pricing) is fetched via `admin.estimates.products` in the form UI.
        $products = collect();

        $mode = 'edit';

        return view('admin.estimates.form', compact('mode', 'estimate', 'workingGroups', 'customers', 'products'));
    }

    public function update(Request $request, Estimate $estimate)
    {
        $this->authorize('update', $estimate);

        $hasItems = $request->exists('items');

        $data = $request->validate([
            'customer_id' => ['nullable', 'integer', 'exists:customers,id'],
            'customer_snapshot' => ['nullable', 'array'],
            'currency' => ['nullable', 'string', 'max:10'],
            'price_tier_id' => ['nullable', 'integer'],

            'tax_mode' => ['nullable', 'in:none,inclusive,exclusive'],
            'discount_mode' => ['nullable', 'in:none,percent,amount'],
            'discount_value' => ['nullable', 'numeric', 'min:0'],

            'valid_until' => ['nullable', 'date'],
            'notes_internal' => ['nullable', 'string'],
            'notes_customer' => ['nullable', 'string'],
            'terms' => ['nullable', 'string'],
            'meta' => ['nullable', 'array'],

            'items' => ['sometimes', 'array'],
            'items.*.id' => ['nullable', 'integer'],
            'items.*.product_id' => ['required', 'integer', 'exists:products,id'],
            'items.*.title' => ['required', 'string', 'max:255'],
            'items.*.description' => ['nullable', 'string'],
            'items.*.qty' => ['required', 'integer', 'min:1'],
            'items.*.width' => ['nullable', 'numeric', 'min:0.01'],
            'items.*.height' => ['nullable', 'numeric', 'min:0.01'],
            'items.*.unit' => ['nullable', 'string', 'in:in,ft,mm,cm,m'],
            'items.*.area_sqft' => ['nullable', 'numeric', 'min:0'],
            'items.*.offcut_sqft' => ['nullable', 'numeric', 'min:0'],
            'items.*.roll_id' => ['nullable', 'integer', 'exists:rolls,id'],
            'items.*.pricing_snapshot' => ['nullable', 'array'],
            'items.*.unit_price' => ['required', 'numeric', 'min:0'],
            'items.*.line_subtotal' => ['required', 'numeric', 'min:0'],
            'items.*.discount_amount' => ['nullable', 'numeric', 'min:0'],
            'items.*.tax_amount' => ['nullable', 'numeric', 'min:0'],
            'items.*.line_total' => ['required', 'numeric', 'min:0'],
        ]);

        $items = (array) ($data['items'] ?? []);
        unset($data['items']);

        DB::transaction(function () use ($estimate, $data, $items, $hasItems) {
            $estimate = Estimate::query()
                ->whereKey($estimate->id)
                ->lockForUpdate()
                ->firstOrFail();

            if (array_key_exists('customer_id', $data) && !empty($data['customer_id'])) {
                $customerWg = Customer::query()
                    ->whereKey((int) $data['customer_id'])
                    ->value('working_group_id');

                if ((int) $customerWg !== (int) $estimate->working_group_id) {
                    throw ValidationException::withMessages([
                        'customer_id' => 'Customer does not belong to this estimate working group.',
                    ]);
                }
            }

            $estimate->update([
                'customer_id' => $data['customer_id'] ?? $estimate->customer_id,
                'customer_snapshot' => $data['customer_snapshot'] ?? $estimate->customer_snapshot,
                'currency' => $data['currency'] ?? $estimate->currency,
                'price_tier_id' => $data['price_tier_id'] ?? $estimate->price_tier_id,

                'tax_mode' => $data['tax_mode'] ?? $estimate->tax_mode,
                'discount_mode' => $data['discount_mode'] ?? $estimate->discount_mode,
                'discount_value' => $data['discount_value'] ?? $estimate->discount_value,

                'valid_until' => $data['valid_until'] ?? $estimate->valid_until,
                'notes_internal' => $data['notes_internal'] ?? $estimate->notes_internal,
                'notes_customer' => $data['notes_customer'] ?? $estimate->notes_customer,
                'terms' => $data['terms'] ?? $estimate->terms,
                'meta' => $data['meta'] ?? $estimate->meta,

                'updated_by' => Auth::id(),
            ]);

            if ($hasItems) {
                $this->syncItemsFromForm($estimate, $items);
            }
        });

        $estimate->refresh();
        $this->service->recalculateTotals($estimate);

        if ($request->expectsJson()) {
            return response()->json([
                'id' => $estimate->id,
                'redirect_url' => route('admin.estimates.show', $estimate),
            ]);
        }

        return redirect()
            ->route('admin.estimates.show', $estimate)
            ->with('success', 'Estimate updated.');
    }

    // Upsert item: create OR update
    public function upsertItem(UpsertEstimateItemRequest $request, Estimate $estimate, ?EstimateItem $item = null)
    {
        $payload = $request->validated();

        // If $item is provided, ensure it belongs to this estimate
        if ($item && $item->estimate_id !== $estimate->id) {
            throw ValidationException::withMessages([
                'estimate_item' => 'Item does not belong to this estimate.',
            ]);
        }

        $saved = $this->service->upsertItem($estimate, $item, $payload);

        return redirect()
            ->route('admin.estimates.show', $estimate)
            ->with('success', $saved->wasRecentlyCreated ? 'Item added.' : 'Item updated.');
    }

    public function deleteItem(Estimate $estimate, EstimateItem $item)
    {
        $this->authorize('update', $estimate);

        $this->service->removeItem($estimate, $item);

        return redirect()
            ->route('admin.estimates.show', $estimate)
            ->with('success', 'Item removed.');
    }

    public function recalc(Estimate $estimate)
    {
        $this->authorize('update', $estimate);

        $this->service->recalculateTotals($estimate);

        return redirect()
            ->route('admin.estimates.show', $estimate)
            ->with('success', 'Totals recalculated.');
    }

    public function send(SendEstimateRequest $request, Estimate $estimate)
    {
        $sent = $this->service->send($estimate, $request->validated());

        $result = $this->delivery->createShareAndEmail($sent, [
            'reason' => $request->validated('reason'),
            'action' => 'sent',
        ]);

        session()->flash('share_token', $result['share_token']);

        $success = 'Estimate sent and locked.';
        $error = null;

        if ($result['recipient_email'] && !$result['email_sent']) {
            $error = 'Email delivery failed. Please use the share link.';
        } elseif (!$result['recipient_email']) {
            $error = 'No customer email found. Share link created.';
        }

        if ($request->expectsJson()) {
            return response()->json([
                'id' => $sent->id,
                'redirect_url' => route('admin.estimates.show', $sent),
                'email_sent' => (bool) $result['email_sent'],
                'recipient_email' => $result['recipient_email'],
                'share_url' => $result['public_url'],
            ]);
        }

        $redirect = redirect()
            ->route('admin.estimates.show', $sent)
            ->with('success', $success);

        return $error ? $redirect->with('error', $error) : $redirect;
    }

    public function resend(Request $request, Estimate $estimate)
    {
        $this->authorize('resend', $estimate);

        $data = $request->validate([
            'reason' => ['nullable', 'string', 'max:500'],
        ]);

        $result = $this->delivery->createShareAndEmail($estimate, [
            'reason' => $data['reason'] ?? null,
            'action' => 'resent',
        ]);

        session()->flash('share_token', $result['share_token']);

        $success = 'Estimate resent.';
        $error = null;

        if ($result['recipient_email'] && !$result['email_sent']) {
            $error = 'Email delivery failed. Please use the share link.';
        } elseif (!$result['recipient_email']) {
            $error = 'No customer email found. Share link created.';
        }

        if ($request->expectsJson()) {
            return response()->json([
                'id' => $estimate->id,
                'redirect_url' => route('admin.estimates.show', $estimate),
                'email_sent' => (bool) $result['email_sent'],
                'recipient_email' => $result['recipient_email'],
                'share_url' => $result['public_url'],
            ]);
        }

        $redirect = redirect()
            ->route('admin.estimates.show', $estimate)
            ->with('success', $success);

        return $error ? $redirect->with('error', $error) : $redirect;
    }

    public function accept(AcceptEstimateRequest $request, Estimate $estimate)
    {
        $this->service->accept($estimate, $request->validated());

        return redirect()
            ->route('admin.estimates.show', $estimate)
            ->with('success', 'Estimate accepted.');
    }

    public function reject(RejectEstimateRequest $request, Estimate $estimate)
    {
        $data = $request->validated();
        $this->service->reject($estimate, $data['reason']);

        return redirect()
            ->route('admin.estimates.show', $estimate)
            ->with('success', 'Estimate rejected.');
    }

    public function createShare(Request $request, Estimate $estimate)
    {
        $this->authorize('view', $estimate);

        $data = $request->validate([
            'expires_at' => ['nullable', 'date'],
        ]);

        $expiresAt = isset($data['expires_at']) ? now()->parse($data['expires_at']) : null;

        $result = $this->service->createShareLink($estimate, $expiresAt);

        // NOTE: token is raw; store/display it once (don’t log it)
        return redirect()
            ->route('admin.estimates.show', $estimate)
            ->with('success', 'Share link created. Copy token now (shown once).')
            ->with('share_token', $result['token']);
    }

    public function revokeShare(Estimate $estimate, EstimateShare $share)
    {
        $this->authorize('update', $estimate);

        if ($share->estimate_id !== $estimate->id) {
            throw ValidationException::withMessages([
                'share' => 'Share does not belong to this estimate.',
            ]);
        }

        $this->service->revokeShare($share);

        return redirect()
            ->route('admin.estimates.show', $estimate)
            ->with('success', 'Share revoked.');
    }

    private function syncItemsFromForm(Estimate $estimate, array $items): void
    {
        DB::transaction(function () use ($estimate, $items) {
            $estimate = Estimate::query()
                ->whereKey($estimate->id)
                ->lockForUpdate()
                ->firstOrFail();

            $productIds = collect($items)
                ->map(fn ($row) => isset($row['product_id']) ? (int) $row['product_id'] : 0)
                ->filter(fn ($id) => $id > 0)
                ->unique()
                ->values();

            $productsById = Product::query()
                ->whereIn('id', $productIds)
                ->get([
                    'id',
                    'status',
                    'product_type',
                    'requires_dimensions',
                    'min_width_in',
                    'max_width_in',
                    'min_height_in',
                    'max_height_in',
                ])
                ->keyBy('id');

            $keptIds = [];

            foreach (array_values($items) as $idx => $row) {
                $itemId = isset($row['id']) ? (int) $row['id'] : null;

                $productId = (int) ($row['product_id'] ?? 0);
                /** @var Product|null $product */
                $product = $productsById->get($productId);
                if (! $product || $product->status !== 'active' || !in_array($product->product_type, ['standard', 'dimension_based', 'service'], true)) {
                    throw ValidationException::withMessages([
                        'items' => 'One or more selected products are not available.',
                    ]);
                }

                $isDimensionBased = (bool) ($product->product_type === 'dimension_based' || $product->requires_dimensions);

                $width = $row['width'] ?? null;
                $height = $row['height'] ?? null;
                $unit = $row['unit'] ?? null;
                $areaSqft = $row['area_sqft'] ?? null;
                $offcutSqft = $row['offcut_sqft'] ?? 0;
                $rollId = isset($row['roll_id']) ? (int) $row['roll_id'] : null;

                if (! $isDimensionBased) {
                    $width = null;
                    $height = null;
                    $unit = null;
                    $areaSqft = null;
                    $offcutSqft = 0;
                    $rollId = null;
                } else {
                    if ($width === null || $height === null || $unit === null) {
                        throw ValidationException::withMessages([
                            'items' => 'Width, height and unit are required for dimension-based products.',
                        ]);
                    }
                }

                if ($rollId) {
                    $allowed = $product->productRolls()
                        ->where('roll_id', $rollId)
                        ->where('is_active', true)
                        ->whereNull('deleted_at')
                        ->exists();

                    if (! $allowed) {
                        throw ValidationException::withMessages([
                            'items' => 'Selected roll is not available for one or more items.',
                        ]);
                    }
                }

                $payload = [
                    'estimate_id' => $estimate->id,
                    'working_group_id' => $estimate->working_group_id,

                    'product_id' => $productId,
                    'variant_set_item_id' => null,
                    'roll_id' => $rollId ?: null,

                    'title' => (string) $row['title'],
                    'description' => $row['description'] ?? null,

                    'qty' => (int) $row['qty'],

                    'width' => $width,
                    'height' => $height,
                    'unit' => $unit,
                    'area_sqft' => $areaSqft,
                    'offcut_sqft' => $offcutSqft,

                    // Financials (normalize + enforce safe math server-side)
                    'unit_price' => is_numeric($row['unit_price'] ?? null) ? number_format((float) $row['unit_price'], 2, '.', '') : '0.00',
                    'line_subtotal' => is_numeric($row['line_subtotal'] ?? null) ? number_format((float) $row['line_subtotal'], 2, '.', '') : '0.00',
                    'discount_amount' => is_numeric($row['discount_amount'] ?? null) ? number_format((float) $row['discount_amount'], 2, '.', '') : '0.00',
                    'tax_amount' => is_numeric($row['tax_amount'] ?? null) ? number_format((float) $row['tax_amount'], 2, '.', '') : '0.00',
                    'line_total' => '0.00',

                    'sort_order' => $idx,
                ];

                $incomingSnapshot = $row['pricing_snapshot'] ?? null;
                $incomingSnapshot = is_array($incomingSnapshot) ? $incomingSnapshot : [];

                // Clamp discount <= subtotal and compute line_total
                if (bccomp((string) $payload['discount_amount'], (string) $payload['line_subtotal'], 2) === 1) {
                    $payload['discount_amount'] = (string) $payload['line_subtotal'];
                }
                $payload['line_total'] = bcadd(
                    bcsub((string) $payload['line_subtotal'], (string) $payload['discount_amount'], 2),
                    (string) $payload['tax_amount'],
                    2
                );

                $payload['pricing_snapshot'] = array_merge($incomingSnapshot, [
                    'source' => 'admin.estimates.form',
                    'stored_at' => now()->toISOString(),
                    'stored_by' => Auth::id(),
                    'working_group_id' => (int) $estimate->working_group_id,
                    'product_id' => (int) $row['product_id'],
                    'roll_id' => $payload['roll_id'],
                    'area_sqft' => $payload['area_sqft'],
                    'offcut_sqft' => $payload['offcut_sqft'],
                    'unit_price' => (float) $payload['unit_price'],
                    'line_total' => (float) $payload['line_total'],
                ]);

                if ($itemId) {
                    $item = EstimateItem::query()
                        ->whereKey($itemId)
                        ->where('estimate_id', $estimate->id)
                        ->first();

                    if ($item) {
                        $item->update($payload);
                        $keptIds[] = $item->id;
                        continue;
                    }
                }

                $created = EstimateItem::create($payload);
                $keptIds[] = $created->id;
            }

            $itemsQuery = EstimateItem::query()->where('estimate_id', $estimate->id);
            if (!empty($keptIds)) {
                $itemsQuery->whereNotIn('id', $keptIds);
            }
            $itemsQuery->delete();
        });
    }

    private function applyIndexFilters(Builder $query, array $filters, bool $includeStatusFilter): void
    {
        if (!empty($filters['working_group_id'])) {
            $query->where('working_group_id', (int) $filters['working_group_id']);
        }

        if ($includeStatusFilter && !empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        $fromDate = $this->parseDateYmd($filters['from'] ?? null, false);
        if ($fromDate) {
            $query->where('created_at', '>=', $fromDate->startOfDay());
        }

        $toDate = $this->parseDateYmd($filters['to'] ?? null, true);
        if ($toDate) {
            $query->where('created_at', '<=', $toDate->endOfDay());
        }

        if (!empty($filters['search'])) {
            $term = $filters['search'];
            $like = '%'.$term.'%';

            $query->where(function (Builder $q) use ($like) {
                $q->where('estimate_no', 'like', $like)
                    ->orWhere('customer_snapshot->name', 'like', $like)
                    ->orWhere('customer_snapshot->full_name', 'like', $like)
                    ->orWhereHas('customer', function (Builder $cq) use ($like) {
                        $cq->where('full_name', 'like', $like)
                            ->orWhere('customer_code', 'like', $like)
                            ->orWhere('phone', 'like', $like);
                    });
            });
        }
    }

    private function buildKpis(Builder $query): array
    {
        $openStatuses = ['draft', 'sent', 'viewed', 'accepted'];

        $agg = (clone $query)->selectRaw("
            COUNT(*) AS total,
            SUM(CASE WHEN status = 'draft' THEN 1 ELSE 0 END) AS draft,
            SUM(CASE WHEN status = 'sent' THEN 1 ELSE 0 END) AS sent,
            SUM(CASE WHEN status = 'viewed' THEN 1 ELSE 0 END) AS viewed,
            SUM(CASE WHEN status = 'accepted' THEN 1 ELSE 0 END) AS accepted,
            SUM(CASE WHEN status = 'rejected' THEN 1 ELSE 0 END) AS rejected,
            SUM(CASE WHEN status = 'converted' THEN 1 ELSE 0 END) AS converted,
            SUM(CASE WHEN status IN ('draft','sent','viewed','accepted') THEN grand_total ELSE 0 END) AS pipeline_value,
            SUM(CASE WHEN status = 'accepted' THEN grand_total ELSE 0 END) AS accepted_value,
            AVG(grand_total) AS avg_estimate
        ")->first();

        $total = (int) ($agg->total ?? 0);
        $draft = (int) ($agg->draft ?? 0);
        $sent = (int) ($agg->sent ?? 0);
        $viewed = (int) ($agg->viewed ?? 0);
        $accepted = (int) ($agg->accepted ?? 0);
        $converted = (int) ($agg->converted ?? 0);

        $expiringSoon = (clone $query)
            ->whereNotNull('valid_until')
            ->whereIn('status', $openStatuses)
            ->whereBetween('valid_until', [now(), now()->copy()->addDays(3)])
            ->count();

        $conversionDenominator = $accepted > 0 ? $accepted : $sent;
        $conversionRate = $conversionDenominator > 0
            ? number_format(($converted / $conversionDenominator) * 100, 1).'%'
            : '—';

        $currency = (clone $query)->whereNotNull('currency')->orderByDesc('id')->value('currency') ?: 'LKR';

        return [
            'currency' => $currency,

            'total' => $total,
            'draft' => $draft,
            'sent' => $sent,
            'viewed' => $viewed,
            'accepted' => $accepted,
            'converted' => $converted,

            'pipeline_value' => (float) ($agg->pipeline_value ?? 0),
            'accepted_value' => (float) ($agg->accepted_value ?? 0),
            'avg_estimate' => (float) ($agg->avg_estimate ?? 0),
            'expiring_soon' => (int) $expiringSoon,

            'conversion_rate' => $conversionRate,
        ];
    }

    private function parseDateYmd(?string $date, bool $end): ?Carbon
    {
        if (!$date) {
            return null;
        }

        try {
            $parsed = Carbon::createFromFormat('Y-m-d', $date);
        } catch (\Throwable) {
            return null;
        }

        return $end ? $parsed->endOfDay() : $parsed->startOfDay();
    }
}

<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\ConfirmOrderRequest;
use App\Http\Requests\UpdateOrderStatusRequest;
use App\Models\Customer;
use App\Models\Estimate;
use App\Models\Option;
use App\Models\OptionGroup;
use App\Models\Order;
use App\Models\Product;
use App\Models\WorkingGroup;
use App\Services\Orders\OrderFlowService;
use App\Services\Orders\OrderEditService;
use App\Services\Orders\OrderStatusService;
use Illuminate\Http\Request;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Str;

class OrderController extends Controller
{
    use AuthorizesRequests;

    public function __construct(
        private readonly OrderFlowService $service,
        private readonly OrderEditService $edit,
    ) {}

    public function index(Request $request)
    {
        $q = Order::query()
            ->with(['customer', 'workingGroup', 'estimate'])
            ->latest('id');

        if ($wgId = $request->integer('working_group_id')) {
            $q->where('working_group_id', $wgId);
        }

        if ($status = $request->string('status')->toString()) {
            $q->where('status', $status);
        }

        if ($paymentStatus = $request->string('payment_status')->toString()) {
            $q->where('payment_status', $paymentStatus);
        }

        if ($search = trim((string) $request->get('search'))) {
            $q->where('order_no', 'like', "%{$search}%");
        }

        $orders = $q->paginate(20)->withQueryString();

        return view('admin.orders.index', compact('orders'));
    }

    public function create(Request $request)
    {
        $this->authorize('create', Order::class);

        $actor = $request->user();

        $workingGroups = WorkingGroup::query()->orderBy('name')->get(['id', 'name']);
        $customers = Customer::query()
            ->when(! $actor?->isAdminOrSuperAdmin(), fn ($q) => $q->where('working_group_id', $actor?->working_group_id))
            ->orderBy('full_name')
            ->limit(500)
            ->get(['id', 'full_name', 'phone', 'email', 'working_group_id', 'type', 'status']);

        $mode = 'create';
        $order = null;
        $locked = false;

        return view('admin.orders.form', [
            'mode' => $mode,
            'order' => $order,
            'workingGroups' => $workingGroups,
            'customers' => $customers,
            'products' => collect(),
            'locked' => $locked,
        ]);
    }

    public function store(Request $request)
    {
        $this->authorize('create', Order::class);

        $actor = $request->user();
        if (! $actor instanceof \App\Models\User) {
            abort(401);
        }

        $data = $request->validate([
            'working_group_id' => ['required', 'integer', 'exists:working_groups,id'],
            'currency' => ['nullable', 'string', 'max:8'],
            'customer_id' => ['nullable', 'integer', 'exists:customers,id'],
            'customer_snapshot' => ['nullable', 'array'],
            'ordered_at' => ['nullable', 'date'],
            'shipping_fee' => ['nullable', 'numeric'],
            'other_fee' => ['nullable', 'numeric'],

            // Quote-like fields (stored in orders.meta.quote)
            'valid_until' => ['nullable', 'date'],
            'tax_mode' => ['nullable', 'string', 'in:none,inclusive,exclusive'],
            'discount_mode' => ['nullable', 'string', 'in:none,percent,amount'],
            'discount_value' => ['nullable', 'numeric', 'min:0'],
            'notes_internal' => ['nullable', 'string', 'max:5000'],
            'notes_customer' => ['nullable', 'string', 'max:5000'],
            'terms' => ['nullable', 'string', 'max:8000'],

            // Optional (quotation-style) items payload
            'items' => ['nullable', 'array'],
            'items.*.id' => ['nullable', 'integer'],
            'items.*.product_id' => ['required_with:items', 'integer', 'exists:products,id'],
            'items.*.variant_set_item_id' => ['nullable', 'integer'],
            'items.*.roll_id' => ['nullable', 'integer'],
            'items.*.title' => ['nullable', 'string', 'max:255'],
            'items.*.description' => ['nullable', 'string'],
            'items.*.qty' => ['nullable', 'integer', 'min:1'],
            'items.*.width' => ['nullable', 'numeric', 'min:0'],
            'items.*.height' => ['nullable', 'numeric', 'min:0'],
            'items.*.unit' => ['nullable', 'string', 'max:10'],
            'items.*.area_sqft' => ['nullable', 'numeric', 'min:0'],
            'items.*.offcut_sqft' => ['nullable', 'numeric', 'min:0'],
            'items.*.pricing_snapshot' => ['nullable', 'array'],
            'items.*.unit_price' => ['nullable', 'numeric', 'min:0'],
            'items.*.line_subtotal' => ['nullable', 'numeric', 'min:0'],
            'items.*.discount_amount' => ['nullable', 'numeric', 'min:0'],
            'items.*.tax_amount' => ['nullable', 'numeric', 'min:0'],
            'items.*.line_total' => ['nullable', 'numeric', 'min:0'],
            'items.*.sort_order' => ['nullable', 'integer', 'min:0'],

            'items.*.finishings' => ['nullable', 'array'],
            'items.*.finishings.*.id' => ['nullable', 'integer'],
            'items.*.finishings.*.finishing_product_id' => ['required_with:items.*.finishings', 'integer', 'exists:products,id'],
            'items.*.finishings.*.label' => ['nullable', 'string', 'max:255'],
            'items.*.finishings.*.remove' => ['nullable', 'boolean'],
            'items.*.finishings.*.qty' => ['nullable', 'integer', 'min:1'],
            'items.*.finishings.*.unit_price' => ['nullable', 'numeric', 'min:0'],
        ]);

        $wgId = (int) $data['working_group_id'];

        $orderNo = $this->generateOrderNo($wgId);

        $order = Order::create([
            'uuid' => (string) Str::uuid(),
            'order_no' => $orderNo,
            'working_group_id' => $wgId,
            'customer_id' => $data['customer_id'] ?? null,
            'estimate_id' => null,
            'customer_snapshot' => $data['customer_snapshot'] ?? null,
            'currency' => $data['currency'] ?? 'LKR',
            'subtotal' => 0,
            'discount_total' => 0,
            'tax_total' => 0,
            'shipping_fee' => 0,
            'other_fee' => 0,
            'grand_total' => 0,
            'status' => 'draft',
            'payment_status' => 'unpaid',
            'ordered_at' => $data['ordered_at'] ?? now(),
            'meta' => null,
            'created_by' => $actor->id,
            'updated_by' => $actor->id,
        ]);

        // If the UI submitted a full order payload (quotation-style), sync items + totals now.
        try {
            $this->edit->updateDraft($order, $data, $actor);
        } catch (\Throwable $e) {
            if ($request->expectsJson()) {
                return response()->json(['ok' => false, 'message' => $e->getMessage()], 422);
            }
            return back()->with('error', $e->getMessage())->withInput();
        }

        if ($request->expectsJson()) {
            return response()->json([
                'ok' => true,
                'id' => $order->id,
                'redirect_url' => route('admin.orders.show', $order),
                'edit_url' => route('admin.orders.edit', $order),
            ]);
        }

        return redirect()
            ->route('admin.orders.edit', $order)
            ->with('success', 'Order created. Add items and save.');
    }

    public function show(Order $order)
    {
        $this->authorize('view', $order);

        $order->load([
            'customer',
            'workingGroup',
            'estimate',
            'items.finishings.option',
            'items.finishings.finishingProduct',
            'items.variantSetItem.option',
            'items.product',
            'items.roll',
            'statusHistories.changedBy',
            'invoices.items',
            'createdBy',
            'updatedBy',
            'lockedBy',
        ]);

        $groupIds = [];
        $optionIds = [];

        foreach ($order->items as $it) {
            $map = data_get($it->pricing_snapshot, 'input.options', []);
            if (! is_array($map)) {
                continue;
            }

            foreach ($map as $gid => $oid) {
                $gid = is_numeric($gid) ? (int) $gid : 0;
                $oid = is_numeric($oid) ? (int) $oid : 0;

                if ($gid > 0) $groupIds[] = $gid;
                if ($oid > 0) $optionIds[] = $oid;
            }
        }

        $groupIds = array_values(array_unique($groupIds));
        $optionIds = array_values(array_unique($optionIds));

        $optionGroupsById = OptionGroup::query()
            ->whereIn('id', $groupIds)
            ->get(['id', 'name'])
            ->keyBy('id');

        $optionsById = Option::query()
            ->whereIn('id', $optionIds)
            ->get(['id', 'label'])
            ->keyBy('id');

        return view('admin.orders.show', compact('order', 'optionGroupsById', 'optionsById'));
    }

    public function edit(Order $order)
    {
        $this->authorize('update', $order);

        $order->load(['items.finishings']);

        $workingGroups = WorkingGroup::query()->orderBy('name')->get(['id', 'name']);
        $customers = Customer::query()
            ->orderBy('full_name')
            ->limit(500)
            ->get(['id', 'full_name', 'phone', 'email', 'working_group_id', 'type', 'status']);

        $locked = $order->invoices()
            ->whereNull('deleted_at')
            ->whereNotIn('status', ['draft', 'void', 'refunded'])
            ->exists();

        return view('admin.orders.form', [
            'mode' => 'edit',
            'order' => $order,
            'workingGroups' => $workingGroups,
            'customers' => $customers,
            'products' => collect(),
            'locked' => $locked,
        ]);
    }

    public function update(Request $request, Order $order)
    {
        $this->authorize('update', $order);

        $actor = $request->user();
        if (! $actor instanceof \App\Models\User) {
            abort(401);
        }

        $data = $request->validate([
            'customer_id' => ['nullable', 'integer', 'exists:customers,id'],
            'customer_snapshot' => ['nullable', 'array'],
            'currency' => ['nullable', 'string', 'max:8'],
            'ordered_at' => ['nullable', 'date'],
            'shipping_fee' => ['nullable', 'numeric'],
            'other_fee' => ['nullable', 'numeric'],

            // Quote-like fields (stored in orders.meta.quote)
            'valid_until' => ['nullable', 'date'],
            'tax_mode' => ['nullable', 'string', 'in:none,inclusive,exclusive'],
            'discount_mode' => ['nullable', 'string', 'in:none,percent,amount'],
            'discount_value' => ['nullable', 'numeric', 'min:0'],
            'notes_internal' => ['nullable', 'string', 'max:5000'],
            'notes_customer' => ['nullable', 'string', 'max:5000'],
            'terms' => ['nullable', 'string', 'max:8000'],

            // Items payload
            'items' => ['nullable', 'array'],
            'items.*.id' => ['nullable', 'integer'],
            'items.*.product_id' => ['required_with:items', 'integer', 'exists:products,id'],
            'items.*.variant_set_item_id' => ['nullable', 'integer'],
            'items.*.roll_id' => ['nullable', 'integer'],
            'items.*.title' => ['nullable', 'string', 'max:255'],
            'items.*.description' => ['nullable', 'string'],
            'items.*.qty' => ['nullable', 'integer', 'min:1'],
            'items.*.width' => ['nullable', 'numeric', 'min:0'],
            'items.*.height' => ['nullable', 'numeric', 'min:0'],
            'items.*.unit' => ['nullable', 'string', 'max:10'],
            'items.*.area_sqft' => ['nullable', 'numeric', 'min:0'],
            'items.*.offcut_sqft' => ['nullable', 'numeric', 'min:0'],
            'items.*.pricing_snapshot' => ['nullable', 'array'],
            'items.*.unit_price' => ['nullable', 'numeric', 'min:0'],
            'items.*.line_subtotal' => ['nullable', 'numeric', 'min:0'],
            'items.*.discount_amount' => ['nullable', 'numeric', 'min:0'],
            'items.*.tax_amount' => ['nullable', 'numeric', 'min:0'],
            'items.*.line_total' => ['nullable', 'numeric', 'min:0'],
            'items.*.sort_order' => ['nullable', 'integer', 'min:0'],

            'items.*.finishings' => ['nullable', 'array'],
            'items.*.finishings.*.id' => ['nullable', 'integer'],
            'items.*.finishings.*.finishing_product_id' => ['required_with:items.*.finishings', 'integer', 'exists:products,id'],
            'items.*.finishings.*.label' => ['nullable', 'string', 'max:255'],
            'items.*.finishings.*.remove' => ['nullable', 'boolean'],
            'items.*.finishings.*.qty' => ['nullable', 'integer', 'min:1'],
            'items.*.finishings.*.unit_price' => ['nullable', 'numeric', 'min:0'],
        ]);

        try {
            $this->edit->updateDraft($order, $data, $actor);
        } catch (\Throwable $e) {
            if ($request->expectsJson()) {
                return response()->json(['ok' => false, 'message' => $e->getMessage()], 422);
            }
            return back()->with('error', $e->getMessage())->withInput();
        }

        if ($request->expectsJson()) {
            return response()->json([
                'ok' => true,
                'id' => $order->id,
                'redirect_url' => route('admin.orders.show', $order),
                'edit_url' => route('admin.orders.edit', $order),
            ]);
        }

        return redirect()
            ->route('admin.orders.show', $order)
            ->with('success', 'Order updated.');
    }

    public function createFromEstimate(Request $request, Estimate $estimate)
    {
        $this->authorize('convertToOrder', $estimate);

        $meta = $request->validate([
            'reason' => ['nullable', 'string', 'max:500'],
        ]);

        try {
            $order = $this->service->createFromEstimate($estimate, $meta);
        } catch (\Throwable $e) {
            return back()->with('error', $e->getMessage());
        }

        $order->loadMissing('invoices');
        $finalInvoice = $order->invoices?->firstWhere('type', 'final');

        if ($finalInvoice) {
            return redirect()
                ->route('admin.invoices.show', $finalInvoice)
                ->with('success', 'Invoice created and issued from estimate.');
        }

        return redirect()
            ->route('admin.orders.show', $order)
            ->with('success', 'Order created from estimate.');
    }

    public function confirm(ConfirmOrderRequest $request, Order $order, OrderStatusService $status)
    {
        $this->authorize('confirm', $order);

        try {
            $actor = $request->user();
            if (! $actor instanceof \App\Models\User) {
                abort(401);
            }

            $status->changeStatus($order, [
                'status' => 'confirmed',
                'why' => $request->validated()['reason'] ?? null,
            ], $actor);
        } catch (\Throwable $e) {
            return back()->with('error', $e->getMessage());
        }

        return redirect()
            ->route('admin.orders.show', $order)
            ->with('success', 'Order confirmed and locked.');
    }

    public function changeStatus(UpdateOrderStatusRequest $request, Order $order, OrderStatusService $status)
    {
        $this->authorize('changeStatus', $order);

        try {
            $actor = $request->user();
            if (! $actor instanceof \App\Models\User) {
                abort(401);
            }

            $status->changeStatus($order, $request->validated(), $actor);
        } catch (\Throwable $e) {
            return back()->with('error', $e->getMessage());
        }

        return redirect()
            ->route('admin.orders.show', $order)
            ->with('success', 'Order status updated.');
    }

    public function statusOptions(Order $order, OrderStatusService $svc)
    {
        $this->authorize('changeStatus', $order);

        $actor = request()->user();
        if (! $actor instanceof \App\Models\User) {
            abort(401);
        }

        $order->loadMissing('invoices');

        $shippingMethod = (string) data_get($order->meta, 'shipping.method', 'pickup');

        $finalInvoice = $order->invoices?->firstWhere('type', 'final');
        $finalInvoiceIssued = $finalInvoice && ! in_array((string) $finalInvoice->status, ['draft', 'void', 'cancelled'], true);

        $next = $svc->nextStatusesFor($order, $actor);

        return response()->json([
            'ok' => true,
            'from' => (string) ($order->status ?? 'draft'),
            'shipping_method' => $shippingMethod,
            'final_invoice_issued' => $finalInvoiceIssued,
            'next_statuses' => $next,
        ]);
    }

    private function generateOrderNo(int $wgId): string
    {
        $date = now()->format('Ymd');

        for ($i = 0; $i < 5; $i++) {
            $rand = str_pad((string) random_int(1, 999999), 6, '0', STR_PAD_LEFT);
            $no = "ORD-WG{$wgId}-{$date}-{$rand}";

            if (! Order::query()->where('working_group_id', $wgId)->where('order_no', $no)->exists()) {
                return $no;
            }
        }

        abort(422, 'Unable to generate a unique order number. Please try again.');
    }
}

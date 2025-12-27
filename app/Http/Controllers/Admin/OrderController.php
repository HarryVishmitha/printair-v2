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
            ->limit(200)
            ->get(['id', 'full_name', 'phone', 'email', 'working_group_id']);
        return view('admin.orders.create', [
            'workingGroups' => $workingGroups,
            'customers' => $customers,
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
            'currency' => ['nullable', 'string', 'max:3'],
            'customer_id' => ['nullable', 'integer', 'exists:customers,id'],
        ]);

        $wgId = (int) $data['working_group_id'];

        $orderNo = $this->generateOrderNo($wgId);

        $order = Order::create([
            'uuid' => (string) Str::uuid(),
            'order_no' => $orderNo,
            'working_group_id' => $wgId,
            'customer_id' => $data['customer_id'] ?? null,
            'estimate_id' => null,
            'customer_snapshot' => null,
            'currency' => $data['currency'] ?? 'LKR',
            'subtotal' => 0,
            'discount_total' => 0,
            'tax_total' => 0,
            'shipping_fee' => 0,
            'other_fee' => 0,
            'grand_total' => 0,
            'status' => 'draft',
            'payment_status' => 'unpaid',
            'ordered_at' => now(),
            'meta' => null,
            'created_by' => $actor->id,
            'updated_by' => $actor->id,
        ]);

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
            ->limit(200)
            ->get(['id', 'full_name', 'phone', 'email', 'working_group_id']);

        $products = Product::query()
            ->where('status', 'active')
            ->where('product_type', '!=', 'finishing')
            ->orderBy('name')
            ->get(['id', 'name', 'product_code']);

        $finishings = Product::query()
            ->where('status', 'active')
            ->where('product_type', 'finishing')
            ->orderBy('name')
            ->get(['id', 'name', 'product_code']);

        return view('admin.orders.form', [
            'mode' => 'edit',
            'order' => $order,
            'workingGroups' => $workingGroups,
            'customers' => $customers,
            'products' => $products,
            'finishings' => $finishings,
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
            'currency' => ['nullable', 'string', 'max:3'],
            'ordered_at' => ['nullable', 'date'],
            'shipping_fee' => ['nullable', 'numeric'],
            'other_fee' => ['nullable', 'numeric'],
            'items' => ['nullable', 'array'],
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

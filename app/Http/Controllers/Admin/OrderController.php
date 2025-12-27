<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\ConfirmOrderRequest;
use App\Http\Requests\UpdateOrderStatusRequest;
use App\Models\Estimate;
use App\Models\Option;
use App\Models\OptionGroup;
use App\Models\Order;
use App\Services\Orders\OrderFlowService;
use App\Services\Orders\OrderStatusService;
use Illuminate\Http\Request;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class OrderController extends Controller
{
    use AuthorizesRequests;

    public function __construct(
        private readonly OrderFlowService $service
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

    public function createFromEstimate(Request $request, Estimate $estimate)
    {
        $this->authorize('convertToOrder', $estimate);

        $meta = $request->validate([
            'reason' => ['nullable', 'string', 'max:500'],
        ]);

        $order = $this->service->createFromEstimate($estimate, $meta);

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
}

<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\ChangeOrderStatusRequest;
use App\Http\Requests\ConfirmOrderRequest;
use App\Models\Estimate;
use App\Models\Order;
use App\Services\Orders\OrderFlowService;
use Illuminate\Http\Request;

class OrderController extends Controller
{
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
            'items.product',
            'items.roll',
            'items.variantSetItem',
            'statusHistories.changedBy',
            'invoices.items',
            'createdBy',
            'updatedBy',
            'lockedBy',
        ]);

        return view('admin.orders.show', compact('order'));
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

    public function confirm(ConfirmOrderRequest $request, Order $order)
    {
        $this->service->confirm($order, $request->validated());

        return redirect()
            ->route('admin.orders.show', $order)
            ->with('success', 'Order confirmed and locked.');
    }

    public function changeStatus(ChangeOrderStatusRequest $request, Order $order)
    {
        $data = $request->validated();

        $this->service->changeStatus($order, $data['status'], [
            'reason' => $data['reason'] ?? null,
        ]);

        return redirect()
            ->route('admin.orders.show', $order)
            ->with('success', 'Order status updated.');
    }
}

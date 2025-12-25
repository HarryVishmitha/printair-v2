<?php

namespace Tests\Feature\Billing;

use App\Models\Customer;
use App\Models\Estimate;
use App\Models\Order;
use App\Models\Payment;
use App\Models\Product;
use App\Models\User;
use App\Models\WorkingGroup;
use App\Services\Estimates\EstimateFlowService;
use App\Services\Invoices\InvoiceFlowService;
use App\Services\Orders\OrderFlowService;
use App\Services\Payments\PaymentService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Gate;
use Tests\TestCase;

class EstimateToPaidInvoiceFlowTest extends TestCase
{
    use RefreshDatabase;

    public function test_end_to_end_estimate_to_paid_invoice_flow(): void
    {
        if (! function_exists('bcadd') || ! function_exists('bccomp')) {
            $this->markTestSkipped('Requires PHP ext-bcmath for bcadd/bcsub/bccomp used by flow services.');
        }

        // ------------------------------------------------------------
        // 0) Arrange
        // ------------------------------------------------------------
        $wg = WorkingGroup::factory()->create();
        $user = User::factory()->create([
            'working_group_id' => $wg->id,
        ]);
        $customer = Customer::factory()->create([
            'working_group_id' => $wg->id,
        ]);

        $product = Product::query()->create([
            'product_code' => 'TEST-PROD-001',
            'name' => 'Test Product',
            'slug' => 'test-product',
            'product_type' => 'standard',
            'status' => 'active',
            'visibility' => 'public',
            'requires_dimensions' => false,
            'allow_custom_size' => false,
            'allow_predefined_sizes' => true,
            'allow_rotation_to_fit_roll' => true,
            'allow_manual_pricing' => true,
            'meta' => ['source' => 'test'],
            'created_by' => $user->id,
            'updated_by' => $user->id,
        ]);

        $this->actingAs($user);

        // IMPORTANT: Services optionally call Gate::authorize() only if Gate::has(ability).
        // In tests, disable Gate checks to focus on lifecycle correctness.
        Gate::partialMock()
            ->shouldReceive('has')
            ->andReturn(false);

        /** @var EstimateFlowService $estimateFlow */
        $estimateFlow = app(EstimateFlowService::class);
        /** @var OrderFlowService $orderFlow */
        $orderFlow = app(OrderFlowService::class);
        /** @var InvoiceFlowService $invoiceFlow */
        $invoiceFlow = app(InvoiceFlowService::class);
        /** @var PaymentService $paymentService */
        $paymentService = app(PaymentService::class);

        // ------------------------------------------------------------
        // 1) Create Estimate Draft
        // ------------------------------------------------------------
        $estimate = $estimateFlow->createDraft([
            'working_group_id' => $wg->id,
            'customer_id' => $customer->id,
            'customer_snapshot' => [
                'full_name' => $customer->full_name,
                'phone' => $customer->phone,
                'email' => $customer->email,
                'customer_code' => $customer->customer_code,
            ],
            'currency' => 'LKR',
            'tax_mode' => 'none',
            'discount_mode' => 'none',
            'discount_value' => 0,
            'notes_internal' => 'Test internal note',
        ]);

        $this->assertInstanceOf(Estimate::class, $estimate);
        $this->assertSame('draft', $estimate->status);
        $this->assertSame($wg->id, (int) $estimate->working_group_id);

        // ------------------------------------------------------------
        // 2) Add one item
        // ------------------------------------------------------------
        $estimateFlow->upsertItem($estimate, null, [
            'product_id' => $product->id,

            'title' => 'Banner Printing',
            'description' => '24x60 banner',
            'qty' => 1,

            // Dimensions optional (keep null if your schema allows)
            'width' => null,
            'height' => null,
            'unit' => null,
            'area_sqft' => null,
            'offcut_sqft' => 0,

            // Financials
            'unit_price' => 5000,
            'line_subtotal' => 5000,
            'discount_amount' => 0,
            'tax_amount' => 0,
            'line_total' => 5000,

            // Snapshot optional
            'pricing_snapshot' => [
                'source' => 'test',
            ],

            'sort_order' => 1,
        ]);

        $estimate->refresh();
        $this->assertSame(1, $estimate->items()->count());

        // ------------------------------------------------------------
        // 3) Send estimate (locks it)
        // ------------------------------------------------------------
        $estimate = $estimateFlow->send($estimate, ['reason' => 'Sending for approval']);
        $this->assertSame('sent', $estimate->status);
        $this->assertNotNull($estimate->locked_at);

        // ------------------------------------------------------------
        // 4) Accept estimate
        // ------------------------------------------------------------
        $estimate = $estimateFlow->accept($estimate, ['reason' => 'Customer accepted']);
        $this->assertSame('accepted', $estimate->status);

        // ------------------------------------------------------------
        // 5) Convert to Order
        // ------------------------------------------------------------
        $order = $orderFlow->createFromEstimate($estimate, ['reason' => 'Convert for production']);
        $this->assertInstanceOf(Order::class, $order);
        $this->assertSame($wg->id, (int) $order->working_group_id);
        $this->assertSame('draft', $order->status);
        $this->assertSame(1, $order->items()->count());

        // Estimate becomes converted
        $estimate->refresh();
        $this->assertSame('converted', $estimate->status);

        // ------------------------------------------------------------
        // 6) Confirm Order (locks order)
        // ------------------------------------------------------------
        $order = $orderFlow->confirm($order, ['reason' => 'Confirmed for production']);
        $this->assertSame('confirmed', $order->status);
        $this->assertNotNull($order->locked_at);

        // ------------------------------------------------------------
        // 7) Create Invoice from Order
        // ------------------------------------------------------------
        $invoice = $invoiceFlow->createFromOrder($order, 'final', ['reason' => 'Billing']);
        $this->assertSame('draft', $invoice->status);
        $this->assertSame($wg->id, (int) $invoice->working_group_id);
        $this->assertSame(1, $invoice->items()->count());
        $this->assertSame((string) $order->grand_total, (string) $invoice->grand_total);

        // ------------------------------------------------------------
        // 8) Issue Invoice (locks invoice)
        // ------------------------------------------------------------
        $invoice = $invoiceFlow->issue($invoice, ['reason' => 'Issued to customer']);
        $this->assertSame('issued', $invoice->status);
        $this->assertNotNull($invoice->locked_at);

        // ------------------------------------------------------------
        // 9) Record & Confirm Payment
        // ------------------------------------------------------------
        $payment = $paymentService->record([
            'working_group_id' => $wg->id,
            'customer_id' => $customer->id,
            'method' => 'cash',
            'amount' => (string) $invoice->grand_total, // pay full
            'currency' => 'LKR',
            'status' => 'pending',
            'reference_no' => 'TEST-REF-001',
            'meta' => ['note' => 'test payment'],
        ]);

        $this->assertInstanceOf(Payment::class, $payment);
        $payment = $paymentService->confirm($payment, ['reason' => 'Cash received']);
        $this->assertSame('confirmed', $payment->status);

        // ------------------------------------------------------------
        // 10) Allocate full payment to invoice => invoice paid + order paid
        // ------------------------------------------------------------
        $paymentService->allocate($payment, $invoice, (string) $invoice->grand_total, [
            'reason' => 'Full settlement',
        ]);

        $invoice->refresh();
        $order->refresh();

        $this->assertSame('paid', $invoice->status);
        $this->assertSame('0.00', (string) $invoice->amount_due);
        $this->assertSame((string) $invoice->grand_total, (string) $invoice->amount_paid);

        $this->assertSame('paid', $order->payment_status);
    }
}


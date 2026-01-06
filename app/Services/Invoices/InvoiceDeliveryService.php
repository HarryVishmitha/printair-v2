<?php

namespace App\Services\Invoices;

use App\Mail\InvoiceIssuedMail;
use App\Models\Invoice;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

class InvoiceDeliveryService
{
    public function __construct(private readonly InvoicePdfService $pdf) {}

    public function emailIssuedInvoiceToCustomerById(int $invoiceId, ?int $actorId = null): bool
    {
        /** @var \App\Models\Invoice|null $invoice */
        $invoice = Invoice::query()
            ->with([
                'order',
                'items.finishings',
                'payments',
            ])
            ->whereKey($invoiceId)
            ->first();

        if (! $invoice) {
            return false;
        }

        return $this->emailIssuedInvoiceToCustomer($invoice, $actorId);
    }

    public function emailIssuedInvoiceToCustomer(Invoice $invoice, ?int $actorId = null): bool
    {
        if ($invoice->status === 'draft') {
            return false;
        }

        if (in_array((string) $invoice->status, ['void', 'refunded'], true)) {
            return false;
        }

        $invoice->loadMissing([
            'order',
            'items.finishings',
            'payments',
        ]);

        $order = $invoice->order;
        if (! $order) {
            return false;
        }

        $to = $order->customer_email
            ?? data_get($order, 'email')
            ?? data_get($order->customer_snapshot, 'email')
            ?? null;

        if (! $to) {
            return false;
        }

        // Rotate secure token for the public invoice page (short-lived).
        $token = Str::random(48);
        $by = $actorId ?? Auth::id();

        $invoice->update([
            'public_token_hash' => hash('sha256', $token),
            'public_token_expires_at' => now()->addMinutes(7),
            'updated_by' => $by,
        ]);

        $invoiceUrl = route('invoices.public.show', ['invoice' => $invoice->id, 'token' => $token]);
        $pdfUrl = route('invoices.public.download', ['invoice' => $invoice->id, 'token' => $token]);

        $pdfBytes = $this->pdf->render($invoice);

        Mail::to($to)->send(new InvoiceIssuedMail(
            invoice: $invoice,
            invoiceUrl: $invoiceUrl,
            pdfUrl: $pdfUrl,
            pdfBinary: $pdfBytes
        ));

        $invoice->update([
            'updated_by' => $by,
            'meta' => array_merge($invoice->meta ?? [], [
                'last_emailed_to' => $to,
                'last_emailed_at' => now()->toISOString(),
                'emailed_by' => $by,
                'email_source' => 'auto_on_issue',
            ]),
        ]);

        return true;
    }
}


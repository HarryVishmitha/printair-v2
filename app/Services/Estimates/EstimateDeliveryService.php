<?php

namespace App\Services\Estimates;

use App\Mail\EstimateSentMail;
use App\Models\Estimate;
use App\Models\EstimateShare;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class EstimateDeliveryService
{
    public function __construct(
        private readonly EstimateFlowService $flow,
        private readonly EstimatePdfService $pdf,
    ) {}

    /**
     * Creates a single active share link (revokes previous) and (optionally) emails the customer.
     *
     * @return array{share_token:string, public_url:string, recipient_email:?string, email_sent:bool, email_error:?string}
     */
    public function createShareAndEmail(Estimate $estimate, array $meta = []): array
    {
        $expiresAt = null;
        if ($estimate->valid_until) {
            $expiresAt = Carbon::parse($estimate->valid_until);
        }

        // Security: keep only one active share link per estimate (prevents old links staying valid).
        EstimateShare::query()
            ->where('estimate_id', $estimate->id)
            ->whereNull('revoked_at')
            ->update([
                'revoked_at' => now(),
            ]);

        $share = $this->flow->createShareLink($estimate, $expiresAt);
        $token = $share['token'];
        $publicUrl = route('estimates.public.show', ['token' => $token]);

        $pdfBytes = null;
        $filename = $this->pdfFilename($estimate);
        try {
            $pdfBytes = $this->pdf->render($estimate, $publicUrl);
        } catch (\Throwable $e) {
            Log::warning('Estimate PDF generation failed', [
                'estimate_id' => $estimate->id,
                'error' => $e->getMessage(),
            ]);
        }

        $recipientEmail = $this->resolveRecipientEmail($estimate);
        $emailSent = false;
        $emailError = null;

        if ($recipientEmail && is_string($pdfBytes) && $pdfBytes !== '') {
            try {
                Mail::to($recipientEmail)->send(new EstimateSentMail(
                    $estimate,
                    $publicUrl,
                    $pdfBytes,
                    $filename,
                    $meta
                ));
                $emailSent = true;
            } catch (\Throwable $e) {
                $emailError = $e->getMessage();
                Log::warning('Estimate email delivery failed', [
                    'estimate_id' => $estimate->id,
                    'recipient_email' => $recipientEmail,
                    'error' => $emailError,
                ]);
            }
        }

        return [
            'share_token' => $token,
            'public_url' => $publicUrl,
            'recipient_email' => $recipientEmail,
            'email_sent' => $emailSent,
            'email_error' => $emailError,
        ];
    }

    private function resolveRecipientEmail(Estimate $estimate): ?string
    {
        $estimate->loadMissing(['customer']);

        $snap = is_array($estimate->customer_snapshot) ? $estimate->customer_snapshot : [];
        $email = $snap['email'] ?? ($estimate->customer?->email);
        $email = is_string($email) ? trim($email) : '';

        if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return null;
        }

        return $email;
    }

    private function pdfFilename(Estimate $estimate): string
    {
        $no = $estimate->estimate_no ?: ('EST-'.$estimate->id);
        $safe = preg_replace('/[^A-Za-z0-9._-]+/', '-', $no) ?: 'estimate';

        return $safe.'.pdf';
    }
}

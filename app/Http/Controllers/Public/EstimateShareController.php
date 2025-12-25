<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Mail\EstimateShareOtpMail;
use App\Models\Estimate;
use App\Models\EstimateShare;
use App\Models\EstimateShareOtp;
use App\Models\EstimateStatusHistory;
use App\Services\Estimates\EstimatePdfService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\RateLimiter;

class EstimateShareController extends Controller
{
    public function show(Request $request, string $token)
    {
        $share = $this->resolveShare($token);
        if (!$share) {
            return $this->shareUnavailable();
        }

        $estimate = $share->estimate()->with(['items.product.primaryImage', 'workingGroup', 'customer'])->first();
        if (!$estimate) {
            return $this->shareUnavailable();
        }

        $recipientEmail = $this->resolveRecipientEmail($estimate);
        $otpRequired = (bool) $recipientEmail;
        $verified = $otpRequired ? $this->isVerified($share->id) : true;

        $otpAutoSent = false;
        $otpAutoSendThrottled = false;

        // Auto-send OTP once per session (when email exists).
        if ($otpRequired && !$verified && !$request->session()->get($this->sessKeyAutoSent($share->id), false)) {
            $request->session()->put($this->sessKeyAutoSent($share->id), true);
            [$otpAutoSent, $otpAutoSendThrottled] = $this->sendOtpInternal($request, $share, $estimate, $recipientEmail);
        }

        $canMarkViewed = (!$otpRequired) || $verified;

        DB::transaction(function () use ($share, $estimate, $request, $canMarkViewed) {
            $share = EstimateShare::query()->whereKey($share->id)->lockForUpdate()->firstOrFail();
            $share->update([
                'last_accessed_at' => now(),
                'access_count' => (int) $share->access_count + 1,
            ]);

            // Auto-mark viewed only when the viewer is verified (or OTP not required).
            if ($canMarkViewed && $estimate->status === 'sent') {
                $from = $estimate->status;

                Estimate::query()
                    ->whereKey($estimate->id)
                    ->lockForUpdate()
                    ->update([
                        'status' => 'viewed',
                        'updated_by' => null,
                    ]);

                EstimateStatusHistory::create([
                    'estimate_id' => $estimate->id,
                    'from_status' => $from,
                    'to_status' => 'viewed',
                    'changed_by' => null,
                    'reason' => 'Viewed via share link (verified)',
                    'meta' => [
                        'ip' => $request->ip(),
                        'user_agent' => $request->userAgent(),
                    ],
                    'created_at' => now(),
                ]);
            }
        });

        $estimate->refresh();

        return view('public.estimate-share.show', [
            'share' => $share,
            'estimate' => $estimate,
            'otpRequired' => $otpRequired,
            'verified' => $verified,
            'maskedEmail' => $recipientEmail ? $this->maskEmail($recipientEmail) : null,
            'waLink' => $this->whatsappLink($estimate),
            'otpAutoSent' => $otpAutoSent,
            'otpAutoSendThrottled' => $otpAutoSendThrottled,
            'seo' => [
                'title' => ($estimate->estimate_no ?? 'Estimate').' · Printair',
                'description' => 'View estimate and respond securely.',
                'canonical' => url()->current(),
            ],
        ]);
    }

    public function sendOtp(Request $request, string $token)
    {
        $share = $this->resolveShare($token);
        if (!$share) {
            return $this->shareUnavailable();
        }

        $estimate = $share->estimate()->with(['customer'])->first();
        if (!$estimate) {
            return $this->shareUnavailable();
        }

        $recipientEmail = $this->resolveRecipientEmail($estimate);
        if (!$recipientEmail) {
            return redirect()
                ->route('estimates.public.show', $token)
                ->with('error', 'OTP verification is not available because no email is attached to this estimate. Please contact admins via WhatsApp to proceed.');
        }

        if ($this->isVerified($share->id)) {
            return redirect()
                ->route('estimates.public.show', $token)
                ->with('success', 'You are already verified.');
        }

        [$sent, $throttled] = $this->sendOtpInternal($request, $share, $estimate, $recipientEmail);

        if ($throttled) {
            return redirect()
                ->route('estimates.public.show', $token)
                ->with('error', 'Please wait a moment before requesting another OTP.');
        }

        if (!$sent) {
            return redirect()
                ->route('estimates.public.show', $token)
                ->with('error', 'Unable to send OTP right now. Please try again.');
        }

        return redirect()
            ->route('estimates.public.show', $token)
            ->with('success', 'OTP sent. Please check your email.');
    }

    public function verifyOtp(Request $request, string $token)
    {
        $share = $this->resolveShare($token);
        if (!$share) {
            return $this->shareUnavailable();
        }

        $code = (string) $request->validate([
            'code' => ['required', 'string', 'size:6'],
        ])['code'];

        if (!preg_match('/^\d{6}$/', $code)) {
            return redirect()
                ->route('estimates.public.show', $token)
                ->with('error', 'Invalid OTP format.');
        }

        $otpId = (int) $request->session()->get($this->sessKeyOtpId($share->id), 0);
        if ($otpId <= 0) {
            return redirect()
                ->route('estimates.public.show', $token)
                ->with('error', 'No OTP request found. Please request a new OTP.');
        }

        /** @var EstimateShareOtp|null $otp */
        $otp = EstimateShareOtp::query()
            ->whereKey($otpId)
            ->where('estimate_share_id', $share->id)
            ->first();

        if (!$otp || $otp->verified_at) {
            return redirect()
                ->route('estimates.public.show', $token)
                ->with('error', 'OTP is no longer valid. Please request a new OTP.');
        }

        if ($otp->expires_at && $otp->expires_at->isPast()) {
            return redirect()
                ->route('estimates.public.show', $token)
                ->with('error', 'OTP has expired. Please request a new OTP.');
        }

        if ((int) $otp->attempts >= 5) {
            return redirect()
                ->route('estimates.public.show', $token)
                ->with('error', 'Too many attempts. Please request a new OTP.');
        }

        $ok = Hash::check($code, (string) $otp->code_hash);

        $otp->forceFill([
            'attempts' => (int) $otp->attempts + 1,
        ])->save();

        if (!$ok) {
            return redirect()
                ->route('estimates.public.show', $token)
                ->with('error', 'Incorrect OTP. Please try again.');
        }

        $otp->update([
            'verified_at' => now(),
        ]);

        $this->markVerified($request, $share->id);
        $request->session()->forget($this->sessKeyOtpId($share->id));

        return redirect()
            ->route('estimates.public.show', $token)
            ->with('success', 'Verified successfully.');
    }

    public function downloadPdf(Request $request, string $token)
    {
        $share = $this->resolveShare($token);
        if (!$share) {
            return $this->shareUnavailable();
        }

        $estimate = $share->estimate()->with(['items.product.primaryImage', 'workingGroup', 'customer'])->first();
        if (!$estimate) {
            return $this->shareUnavailable();
        }

        $recipientEmail = $this->resolveRecipientEmail($estimate);
        if ($recipientEmail && !$this->isVerified($share->id)) {
            return redirect()
                ->route('estimates.public.show', $token)
                ->with('error', 'Please verify the OTP before downloading the PDF.');
        }

        $publicUrl = route('estimates.public.show', ['token' => $token]);
        $pdfBytes = app(EstimatePdfService::class)->render($estimate, $publicUrl);

        $no = $estimate->estimate_no ?: ('EST-'.$estimate->id);
        $safe = preg_replace('/[^A-Za-z0-9._-]+/', '-', $no) ?: 'estimate';
        $filename = $safe.'.pdf';

        return response($pdfBytes, 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'attachment; filename="'.$filename.'"',
            'Cache-Control' => 'no-store, private',
        ]);
    }

    public function accept(Request $request, string $token)
    {
        $share = $this->resolveShare($token);
        if (!$share) {
            return $this->shareUnavailable();
        }

        $estimate = $share->estimate()->with(['customer'])->first();
        if (!$estimate) {
            return $this->shareUnavailable();
        }

        if ($estimate->status === 'accepted') {
            return redirect()
                ->route('estimates.public.show', $token)
                ->with('success', 'This estimate is already accepted.');
        }

        if ($estimate->status === 'rejected') {
            return redirect()
                ->route('estimates.public.show', $token)
                ->with('error', 'This estimate was already rejected.');
        }

        if (in_array($estimate->status, ['expired', 'cancelled', 'converted'], true)) {
            return redirect()
                ->route('estimates.public.show', $token)
                ->with('error', 'This estimate is not accepting responses at this time.');
        }

        if ($estimate->valid_until && \Illuminate\Support\Carbon::parse($estimate->valid_until)->isPast()) {
            return redirect()
                ->route('estimates.public.show', $token)
                ->with('error', 'This estimate has expired and cannot be accepted.');
        }

        $recipientEmail = $this->resolveRecipientEmail($estimate);
        if (!$recipientEmail) {
            return redirect()
                ->route('estimates.public.show', $token)
                ->with('error', 'This estimate cannot be accepted online because no email is attached. Please contact admins via WhatsApp to accept.');
        }

        if (!$this->isVerified($share->id)) {
            return redirect()
                ->route('estimates.public.show', $token)
                ->with('error', 'Please verify the OTP before accepting the estimate.');
        }

        DB::transaction(function () use ($share, $estimate, $request) {
            $share = EstimateShare::query()->whereKey($share->id)->lockForUpdate()->firstOrFail();
            $estimate = Estimate::query()->whereKey($estimate->id)->lockForUpdate()->firstOrFail();

            if ($estimate->valid_until && \Illuminate\Support\Carbon::parse($estimate->valid_until)->isPast()) {
                return;
            }

            if (!in_array($estimate->status, ['sent', 'viewed'], true)) {
                return;
            }

            $from = $estimate->status;

            $estimate->update([
                'status' => 'accepted',
                'accepted_at' => now(),
                'locked_at' => $estimate->locked_at ?? now(),
                'locked_by' => $estimate->locked_by,
                'updated_by' => null,
            ]);

            EstimateStatusHistory::create([
                'estimate_id' => $estimate->id,
                'from_status' => $from,
                'to_status' => 'accepted',
                'changed_by' => null,
                'reason' => 'Accepted via share link',
                'meta' => [
                    'ip' => $request->ip(),
                    'user_agent' => $request->userAgent(),
                ],
                'created_at' => now(),
            ]);
        });

        return redirect()
            ->route('estimates.public.show', $token)
            ->with('success', 'Thank you. The estimate has been accepted.');
    }

    public function reject(Request $request, string $token)
    {
        $share = $this->resolveShare($token);
        if (!$share) {
            return $this->shareUnavailable();
        }

        $estimate = $share->estimate()->with(['customer'])->first();
        if (!$estimate) {
            return $this->shareUnavailable();
        }

        if ($estimate->status === 'rejected') {
            return redirect()
                ->route('estimates.public.show', $token)
                ->with('success', 'This estimate is already rejected.');
        }

        if ($estimate->status === 'accepted') {
            return redirect()
                ->route('estimates.public.show', $token)
                ->with('error', 'This estimate was already accepted.');
        }

        if (in_array($estimate->status, ['expired', 'cancelled', 'converted'], true)) {
            return redirect()
                ->route('estimates.public.show', $token)
                ->with('error', 'This estimate is not accepting responses at this time.');
        }

        if ($estimate->valid_until && \Illuminate\Support\Carbon::parse($estimate->valid_until)->isPast()) {
            return redirect()
                ->route('estimates.public.show', $token)
                ->with('error', 'This estimate has expired and cannot be rejected.');
        }

        $recipientEmail = $this->resolveRecipientEmail($estimate);
        if (!$recipientEmail) {
            return redirect()
                ->route('estimates.public.show', $token)
                ->with('error', 'This estimate cannot be rejected online because no email is attached. Please contact admins via WhatsApp.');
        }

        if (!$this->isVerified($share->id)) {
            return redirect()
                ->route('estimates.public.show', $token)
                ->with('error', 'Please verify the OTP before rejecting the estimate.');
        }

        $data = $request->validate([
            'reason' => ['required', 'string', 'max:500'],
        ]);

        DB::transaction(function () use ($share, $estimate, $request, $data) {
            $share = EstimateShare::query()->whereKey($share->id)->lockForUpdate()->firstOrFail();
            $estimate = Estimate::query()->whereKey($estimate->id)->lockForUpdate()->firstOrFail();

            if ($estimate->valid_until && \Illuminate\Support\Carbon::parse($estimate->valid_until)->isPast()) {
                return;
            }

            if (!in_array($estimate->status, ['sent', 'viewed'], true)) {
                return;
            }

            $from = $estimate->status;

            $estimate->update([
                'status' => 'rejected',
                'rejected_at' => now(),
                'updated_by' => null,
            ]);

            EstimateStatusHistory::create([
                'estimate_id' => $estimate->id,
                'from_status' => $from,
                'to_status' => 'rejected',
                'changed_by' => null,
                'reason' => $data['reason'],
                'meta' => [
                    'ip' => $request->ip(),
                    'user_agent' => $request->userAgent(),
                ],
                'created_at' => now(),
            ]);
        });

        return redirect()
            ->route('estimates.public.show', $token)
            ->with('success', 'Thank you. The estimate has been rejected.');
    }

    private function resolveShare(string $token): ?EstimateShare
    {
        $hash = hash('sha256', $token);

        return EstimateShare::query()
            ->where('token_hash', $hash)
            ->whereNull('revoked_at')
            ->where(function ($q) {
                $q->whereNull('expires_at')->orWhere('expires_at', '>', now());
            })
            ->first();
    }

    private function resolveRecipientEmail(Estimate $estimate): ?string
    {
        $snap = is_array($estimate->customer_snapshot) ? $estimate->customer_snapshot : [];
        $email = $snap['email'] ?? ($estimate->customer?->email);
        $email = is_string($email) ? trim($email) : '';

        if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return null;
        }

        return $email;
    }

    /**
     * @return array{0:bool,1:bool} [sent, throttled]
     */
    private function sendOtpInternal(Request $request, EstimateShare $share, Estimate $estimate, string $recipientEmail): array
    {
        // Strong throttling for send (per token hash + IP)
        $key = 'estimate_otp_send:'.$share->token_hash.':'.$request->ip();

        if (RateLimiter::tooManyAttempts($key, 3)) {
            return [false, true];
        }
        RateLimiter::hit($key, 5 * 60);

        $code = (string) random_int(100000, 999999);
        $expiresMinutes = 10;

        $otp = EstimateShareOtp::create([
            'estimate_share_id' => $share->id,
            'sent_to_email' => $recipientEmail,
            'code_hash' => Hash::make($code),
            'expires_at' => now()->addMinutes($expiresMinutes),
            'ip' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ]);

        $request->session()->put($this->sessKeyOtpId($share->id), $otp->id);

        try {
            Mail::to($recipientEmail)->send(new EstimateShareOtpMail($estimate, $code, $expiresMinutes));
        } catch (\Throwable) {
            return [false, false];
        }

        return [true, false];
    }

    private function isVerified(int $shareId): bool
    {
        $at = session($this->sessKeyVerifiedAt($shareId));
        if (!$at) {
            return false;
        }

        try {
            $t = \Illuminate\Support\Carbon::parse($at);
        } catch (\Throwable) {
            return false;
        }

        return $t->isAfter(now()->subMinutes(60));
    }

    private function markVerified(Request $request, int $shareId): void
    {
        $request->session()->put($this->sessKeyVerifiedAt($shareId), now()->toISOString());
    }

    private function maskEmail(string $email): string
    {
        $parts = explode('@', $email, 2);
        if (count($parts) !== 2) return $email;

        [$local, $domain] = $parts;
        $local = (string) $local;

        $shown = mb_substr($local, 0, 1);
        return $shown.'***@'.$domain;
    }

    private function whatsappLink(Estimate $estimate): ?string
    {
        $wa = (string) config('printair.contact_whatsapp', '');
        $wa = preg_replace('/\D+/', '', $wa ?: '');
        if (!$wa) return null;

        $no = $estimate->estimate_no ?: ('EST-'.$estimate->id);
        $msg = rawurlencode("Hi Printair, I need to accept estimate {$no}. Please assist.");

        return "https://wa.me/{$wa}?text={$msg}";
    }

    private function sessKeyVerifiedAt(int $shareId): string
    {
        return "est_share_verified_at:{$shareId}";
    }

    private function sessKeyOtpId(int $shareId): string
    {
        return "est_share_otp_id:{$shareId}";
    }

    private function sessKeyAutoSent(int $shareId): string
    {
        return "est_share_otp_autosent:{$shareId}";
    }

    private function shareUnavailable()
    {
        return response()
            ->view('public.estimate-share.unavailable', [
                'seo' => [
                    'title' => 'Estimate link unavailable · Printair',
                    'description' => 'This estimate link has expired or was revoked.',
                    'canonical' => url()->current(),
                ],
            ], 410);
    }
}

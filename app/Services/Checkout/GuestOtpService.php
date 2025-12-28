<?php

namespace App\Services\Checkout;

use App\Models\Customer;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

class GuestOtpService
{
    public const OTP_EXP_MIN = 7;

    public function start(string $email, string $whatsapp, ?string $name = null): Customer
    {
        [$customer, $otp] = DB::transaction(function () use ($email, $whatsapp, $name) {
            $wgId = \App\Models\WorkingGroup::getPublicId();
            if (! $wgId) {
                throw new \RuntimeException('Public working group not configured.');
            }

            $fullName = $name ?: 'Guest Customer';
            $phone = $whatsapp ?: '+000000000';

            $customer = Customer::query()->firstOrCreate(
                [
                    'working_group_id' => $wgId,
                    'email' => $email,
                ],
                [
                    'user_id' => null,
                    'customer_code' => $this->generateCustomerCode(),
                    'full_name' => $fullName,
                    'phone' => $phone,
                    'whatsapp_number' => $whatsapp,
                    'type' => 'walk_in',
                    'status' => 'active',
                    'email_notifications' => true,
                    'sms_notifications' => false,
                ]
            );

            $customer->update([
                'full_name' => $fullName ?: $customer->full_name,
                'phone' => $phone ?: $customer->phone,
                'whatsapp_number' => $whatsapp,
            ]);

            $otp = (string) random_int(100000, 999999);

            DB::table('customer_email_verifications')->insert([
                'customer_id' => $customer->id,
                'email' => $email,
                'otp_hash' => Hash::make($otp),
                'attempts' => 0,
                'expires_at' => Carbon::now()->addMinutes(self::OTP_EXP_MIN),
                'consumed_at' => null,
                'ip' => request()?->ip(),
                'user_agent' => substr((string) request()?->userAgent(), 0, 255),
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            return [$customer, $otp];
        });

        Mail::send('emails.customer-otp', [
            'otp' => $otp,
            'expiresMinutes' => self::OTP_EXP_MIN,
        ], function ($m) use ($email) {
            $m->to($email)->subject('Printair Order Verification Code');
        });

        return $customer;
    }

    public function verify(string $email, string $otp): ?Customer
    {
        $row = DB::table('customer_email_verifications')
            ->where('email', $email)
            ->whereNull('consumed_at')
            ->where('expires_at', '>=', now())
            ->orderByDesc('id')
            ->first();

        if (! $row) {
            return null;
        }

        if ((int) $row->attempts >= 6) {
            return null;
        }

        $hash = (string) ($row->otp_hash ?? '');
        $ok = str_starts_with($hash, '$2y$') || str_starts_with($hash, '$2a$') || str_starts_with($hash, '$2b$')
            ? Hash::check($otp, $hash)
            : hash_equals($hash, hash('sha256', $otp));

        DB::table('customer_email_verifications')
            ->where('id', $row->id)
            ->update([
                'attempts' => (int) $row->attempts + 1,
                'consumed_at' => $ok ? now() : null,
                'updated_at' => now(),
            ]);

        if (! $ok) {
            return null;
        }

        $customer = Customer::query()->where('id', $row->customer_id)->first();
        if (! $customer) {
            return null;
        }

        if (! $customer->email_verified_at) {
            $customer->update(['email_verified_at' => now()]);
        }

        return $customer;
    }

    private function generateCustomerCode(): string
    {
        for ($i = 0; $i < 5; $i++) {
            $rand = str_pad((string) random_int(1, 999999), 6, '0', STR_PAD_LEFT);
            $code = 'CUST-'.$rand;
            $exists = Customer::query()->where('customer_code', $code)->exists();
            if (! $exists) {
                return $code;
            }
        }

        return 'CUST-'.Str::upper(Str::random(8));
    }
}

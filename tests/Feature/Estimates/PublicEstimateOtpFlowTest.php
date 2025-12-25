<?php

namespace Tests\Feature\Estimates;

use App\Mail\EstimateShareOtpMail;
use App\Models\Customer;
use App\Models\Estimate;
use App\Models\EstimateItem;
use App\Models\EstimateShare;
use App\Models\Product;
use App\Models\User;
use App\Models\WorkingGroup;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Tests\TestCase;

class PublicEstimateOtpFlowTest extends TestCase
{
    use RefreshDatabase;

    public function test_public_share_requires_otp_when_email_exists_and_accept_is_idempotent(): void
    {
        Mail::fake();

        $wg = WorkingGroup::factory()->create();
        $user = User::factory()->create(['working_group_id' => $wg->id]);
        $customer = Customer::factory()->create([
            'working_group_id' => $wg->id,
            'email' => 'customer@example.com',
        ]);

        $product = Product::query()->create([
            'category_id' => null,
            'product_code' => 'P-'.Str::upper(Str::random(8)),
            'name' => 'Test Product',
            'slug' => 'test-product-'.Str::lower(Str::random(6)),
            'product_type' => 'standard',
            'status' => 'active',
            'visibility' => 'public',
            'requires_dimensions' => false,
            'allow_custom_size' => false,
            'allow_predefined_sizes' => true,
            'allow_rotation_to_fit_roll' => true,
            'allow_manual_pricing' => false,
            'meta' => null,
            'created_by' => $user->id,
            'updated_by' => $user->id,
        ]);

        $estimate = Estimate::query()->create([
            'uuid' => (string) Str::uuid(),
            'estimate_no' => 'EST-TEST-'.Str::upper(Str::random(6)),
            'working_group_id' => $wg->id,
            'customer_id' => $customer->id,
            'customer_snapshot' => [
                'full_name' => $customer->full_name,
                'email' => $customer->email,
            ],
            'currency' => 'LKR',
            'subtotal' => 1000,
            'discount_total' => 0,
            'tax_total' => 0,
            'shipping_fee' => 0,
            'other_fee' => 0,
            'grand_total' => 1000,
            'tax_mode' => 'none',
            'discount_mode' => 'none',
            'discount_value' => 0,
            'status' => 'sent',
            'valid_until' => now()->addDays(7),
            'sent_at' => now(),
            'locked_at' => now(),
            'locked_by' => $user->id,
            'revision' => 1,
            'created_by' => $user->id,
            'updated_by' => $user->id,
        ]);

        EstimateItem::query()->create([
            'estimate_id' => $estimate->id,
            'working_group_id' => $wg->id,
            'product_id' => $product->id,
            'variant_set_item_id' => null,
            'roll_id' => null,
            'title' => 'Line 1',
            'description' => 'Desc',
            'qty' => 1,
            'width' => null,
            'height' => null,
            'unit' => null,
            'area_sqft' => null,
            'offcut_sqft' => 0,
            'unit_price' => 1000,
            'line_subtotal' => 1000,
            'discount_amount' => 0,
            'tax_amount' => 0,
            'line_total' => 1000,
            'pricing_snapshot' => ['source' => 'test'],
            'sort_order' => 0,
        ]);

        $token = Str::random(64);
        EstimateShare::query()->create([
            'estimate_id' => $estimate->id,
            'token_hash' => hash('sha256', $token),
            'expires_at' => now()->addDays(7),
            'revoked_at' => null,
            'created_by' => $user->id,
            'access_count' => 0,
        ]);

        $response = $this->get(route('estimates.public.show', ['token' => $token]));
        $response->assertOk();
        $response->assertSee('Verify to view & respond');

        $otpCode = null;
        Mail::assertSent(EstimateShareOtpMail::class, function (EstimateShareOtpMail $m) use (&$otpCode, $estimate) {
            $otpCode = $m->code;
            return $m->estimate->is($estimate);
        });
        $this->assertNotNull($otpCode);

        $verify = $this->post(route('estimates.public.otp.verify', ['token' => $token]), [
            'code' => $otpCode,
        ]);
        $verify->assertRedirect(route('estimates.public.show', ['token' => $token]));

        $accept = $this->post(route('estimates.public.accept', ['token' => $token]));
        $accept->assertRedirect(route('estimates.public.show', ['token' => $token]));

        $estimate->refresh();
        $this->assertSame('accepted', $estimate->status);

        // Replay safety: accept again should not change anything or error.
        $accept2 = $this->post(route('estimates.public.accept', ['token' => $token]));
        $accept2->assertRedirect(route('estimates.public.show', ['token' => $token]));
        $estimate->refresh();
        $this->assertSame('accepted', $estimate->status);
    }

    public function test_public_share_reject_is_blocked_without_email(): void
    {
        Mail::fake();

        $wg = WorkingGroup::factory()->create();
        $user = User::factory()->create(['working_group_id' => $wg->id]);
        $customer = Customer::factory()->create([
            'working_group_id' => $wg->id,
            'email' => null,
        ]);

        $product = Product::query()->create([
            'category_id' => null,
            'product_code' => 'P-'.Str::upper(Str::random(8)),
            'name' => 'Test Product',
            'slug' => 'test-product-'.Str::lower(Str::random(6)),
            'product_type' => 'standard',
            'status' => 'active',
            'visibility' => 'public',
            'requires_dimensions' => false,
            'allow_custom_size' => false,
            'allow_predefined_sizes' => true,
            'allow_rotation_to_fit_roll' => true,
            'allow_manual_pricing' => false,
            'meta' => null,
            'created_by' => $user->id,
            'updated_by' => $user->id,
        ]);

        $estimate = Estimate::query()->create([
            'uuid' => (string) Str::uuid(),
            'estimate_no' => 'EST-TEST-'.Str::upper(Str::random(6)),
            'working_group_id' => $wg->id,
            'customer_id' => $customer->id,
            'customer_snapshot' => [
                'full_name' => $customer->full_name,
            ],
            'currency' => 'LKR',
            'subtotal' => 1000,
            'discount_total' => 0,
            'tax_total' => 0,
            'shipping_fee' => 0,
            'other_fee' => 0,
            'grand_total' => 1000,
            'tax_mode' => 'none',
            'discount_mode' => 'none',
            'discount_value' => 0,
            'status' => 'sent',
            'valid_until' => now()->addDays(7),
            'sent_at' => now(),
            'locked_at' => now(),
            'locked_by' => $user->id,
            'revision' => 1,
            'created_by' => $user->id,
            'updated_by' => $user->id,
        ]);

        EstimateItem::query()->create([
            'estimate_id' => $estimate->id,
            'working_group_id' => $wg->id,
            'product_id' => $product->id,
            'variant_set_item_id' => null,
            'roll_id' => null,
            'title' => 'Line 1',
            'description' => null,
            'qty' => 1,
            'pricing_snapshot' => ['source' => 'test'],
            'unit_price' => 1000,
            'line_subtotal' => 1000,
            'discount_amount' => 0,
            'tax_amount' => 0,
            'line_total' => 1000,
            'sort_order' => 0,
        ]);

        $token = Str::random(64);
        EstimateShare::query()->create([
            'estimate_id' => $estimate->id,
            'token_hash' => hash('sha256', $token),
            'expires_at' => now()->addDays(7),
            'revoked_at' => null,
            'created_by' => $user->id,
            'access_count' => 0,
        ]);

        $show = $this->get(route('estimates.public.show', ['token' => $token]));
        $show->assertOk();

        $reject = $this->post(route('estimates.public.reject', ['token' => $token]), [
            'reason' => 'No thanks',
        ]);
        $reject->assertRedirect(route('estimates.public.show', ['token' => $token]));

        $estimate->refresh();
        $this->assertSame('sent', $estimate->status);
    }

    public function test_otp_lockout_after_too_many_wrong_attempts(): void
    {
        Mail::fake();

        $wg = WorkingGroup::factory()->create();
        $user = User::factory()->create(['working_group_id' => $wg->id]);
        $customer = Customer::factory()->create([
            'working_group_id' => $wg->id,
            'email' => 'customer@example.com',
        ]);

        $product = Product::query()->create([
            'category_id' => null,
            'product_code' => 'P-'.Str::upper(Str::random(8)),
            'name' => 'Test Product',
            'slug' => 'test-product-'.Str::lower(Str::random(6)),
            'product_type' => 'standard',
            'status' => 'active',
            'visibility' => 'public',
            'requires_dimensions' => false,
            'allow_custom_size' => false,
            'allow_predefined_sizes' => true,
            'allow_rotation_to_fit_roll' => true,
            'allow_manual_pricing' => false,
            'meta' => null,
            'created_by' => $user->id,
            'updated_by' => $user->id,
        ]);

        $estimate = Estimate::query()->create([
            'uuid' => (string) Str::uuid(),
            'estimate_no' => 'EST-TEST-'.Str::upper(Str::random(6)),
            'working_group_id' => $wg->id,
            'customer_id' => $customer->id,
            'customer_snapshot' => [
                'full_name' => $customer->full_name,
                'email' => $customer->email,
            ],
            'currency' => 'LKR',
            'subtotal' => 1000,
            'discount_total' => 0,
            'tax_total' => 0,
            'shipping_fee' => 0,
            'other_fee' => 0,
            'grand_total' => 1000,
            'tax_mode' => 'none',
            'discount_mode' => 'none',
            'discount_value' => 0,
            'status' => 'sent',
            'valid_until' => now()->addDays(7),
            'sent_at' => now(),
            'locked_at' => now(),
            'locked_by' => $user->id,
            'revision' => 1,
            'created_by' => $user->id,
            'updated_by' => $user->id,
        ]);

        EstimateItem::query()->create([
            'estimate_id' => $estimate->id,
            'working_group_id' => $wg->id,
            'product_id' => $product->id,
            'variant_set_item_id' => null,
            'roll_id' => null,
            'title' => 'Line 1',
            'description' => null,
            'qty' => 1,
            'pricing_snapshot' => ['source' => 'test'],
            'unit_price' => 1000,
            'line_subtotal' => 1000,
            'discount_amount' => 0,
            'tax_amount' => 0,
            'line_total' => 1000,
            'sort_order' => 0,
        ]);

        $token = Str::random(64);
        EstimateShare::query()->create([
            'estimate_id' => $estimate->id,
            'token_hash' => hash('sha256', $token),
            'expires_at' => now()->addDays(7),
            'revoked_at' => null,
            'created_by' => $user->id,
            'access_count' => 0,
        ]);

        // Trigger OTP creation (auto-send)
        $this->get(route('estimates.public.show', ['token' => $token]))->assertOk();
        Mail::assertSent(EstimateShareOtpMail::class);

        for ($i = 0; $i < 5; $i++) {
            $this->post(route('estimates.public.otp.verify', ['token' => $token]), [
                'code' => '000000',
            ])->assertRedirect(route('estimates.public.show', ['token' => $token]));
        }

        $tooMany = $this->post(route('estimates.public.otp.verify', ['token' => $token]), [
            'code' => '000000',
        ]);

        $tooMany->assertRedirect(route('estimates.public.show', ['token' => $token]));
        $follow = $this->get(route('estimates.public.show', ['token' => $token]));
        $follow->assertSee('Too many attempts');
    }

    public function test_expired_estimate_cannot_be_accepted_even_with_verified_otp(): void
    {
        Mail::fake();

        $wg = WorkingGroup::factory()->create();
        $user = User::factory()->create(['working_group_id' => $wg->id]);
        $customer = Customer::factory()->create([
            'working_group_id' => $wg->id,
            'email' => 'customer@example.com',
        ]);

        $product = Product::query()->create([
            'category_id' => null,
            'product_code' => 'P-'.Str::upper(Str::random(8)),
            'name' => 'Test Product',
            'slug' => 'test-product-'.Str::lower(Str::random(6)),
            'product_type' => 'standard',
            'status' => 'active',
            'visibility' => 'public',
            'requires_dimensions' => false,
            'allow_custom_size' => false,
            'allow_predefined_sizes' => true,
            'allow_rotation_to_fit_roll' => true,
            'allow_manual_pricing' => false,
            'meta' => null,
            'created_by' => $user->id,
            'updated_by' => $user->id,
        ]);

        $estimate = Estimate::query()->create([
            'uuid' => (string) Str::uuid(),
            'estimate_no' => 'EST-TEST-'.Str::upper(Str::random(6)),
            'working_group_id' => $wg->id,
            'customer_id' => $customer->id,
            'customer_snapshot' => [
                'full_name' => $customer->full_name,
                'email' => $customer->email,
            ],
            'currency' => 'LKR',
            'subtotal' => 1000,
            'discount_total' => 0,
            'tax_total' => 0,
            'shipping_fee' => 0,
            'other_fee' => 0,
            'grand_total' => 1000,
            'tax_mode' => 'none',
            'discount_mode' => 'none',
            'discount_value' => 0,
            'status' => 'sent',
            'valid_until' => now()->subDay(),
            'sent_at' => now()->subDays(2),
            'locked_at' => now()->subDays(2),
            'locked_by' => $user->id,
            'revision' => 1,
            'created_by' => $user->id,
            'updated_by' => $user->id,
        ]);

        EstimateItem::query()->create([
            'estimate_id' => $estimate->id,
            'working_group_id' => $wg->id,
            'product_id' => $product->id,
            'variant_set_item_id' => null,
            'roll_id' => null,
            'title' => 'Line 1',
            'description' => null,
            'qty' => 1,
            'pricing_snapshot' => ['source' => 'test'],
            'unit_price' => 1000,
            'line_subtotal' => 1000,
            'discount_amount' => 0,
            'tax_amount' => 0,
            'line_total' => 1000,
            'sort_order' => 0,
        ]);

        $token = Str::random(64);
        EstimateShare::query()->create([
            'estimate_id' => $estimate->id,
            'token_hash' => hash('sha256', $token),
            'expires_at' => now()->addDays(7),
            'revoked_at' => null,
            'created_by' => $user->id,
            'access_count' => 0,
        ]);

        // Trigger OTP and verify
        $this->get(route('estimates.public.show', ['token' => $token]))->assertOk();

        $otpCode = null;
        Mail::assertSent(EstimateShareOtpMail::class, function (EstimateShareOtpMail $m) use (&$otpCode, $estimate) {
            $otpCode = $m->code;
            return $m->estimate->is($estimate);
        });

        $this->post(route('estimates.public.otp.verify', ['token' => $token]), [
            'code' => $otpCode,
        ])->assertRedirect(route('estimates.public.show', ['token' => $token]));

        $accept = $this->post(route('estimates.public.accept', ['token' => $token]));
        $accept->assertRedirect(route('estimates.public.show', ['token' => $token]));

        $estimate->refresh();
        $this->assertSame('sent', $estimate->status);
    }
}

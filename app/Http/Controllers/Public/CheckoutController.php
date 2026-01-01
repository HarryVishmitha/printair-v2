<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\WorkingGroup;
use App\Services\Checkout\GuestOtpService;
use App\Services\Cart\CartService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Services\Orders\OrderPlacementService;

class CheckoutController extends Controller
{
    public function __construct(
        private GuestOtpService $otp,
        private OrderPlacementService $place,
        private CartService $cart,
    ) {}

    public function startGuest(Request $request)
    {
        abort_unless(!Auth::check(), 403);

        $data = $request->validate([
            'email' => ['required', 'email', 'max:255'],
            'whatsapp' => ['required', 'string', 'max:40'],
            'name' => ['nullable', 'string', 'max:120'],
        ]);

        try {
            $customer = $this->otp->start($data['email'], $data['whatsapp'], $data['name'] ?? null);
        } catch (\Throwable $e) {
            report($e);

            return response()->json([
                'ok' => false,
                'message' => 'Unable to send OTP right now. Please try again shortly.',
            ], 422);
        }

        session()->put('guest_checkout_email', $customer->email);

        return response()->json([
            'ok' => true,
            'message' => 'OTP sent.',
        ]);
    }

    public function index()
    {
        return view('public.checkout.index');
    }

    public function verifyGuestOtp(Request $request)
    {
        abort_unless(!Auth::check(), 403);

        $data = $request->validate([
            'email' => ['required', 'email', 'max:255'],
            'otp' => ['required', 'string', 'max:10'],
        ]);

        $customer = $this->otp->verify($data['email'], $data['otp']);

        if (!$customer) {
            return response()->json(['ok' => false, 'message' => 'Invalid or expired code.'], 422);
        }

        session()->put('guest_customer_id', $customer->id);
        session()->put('guest_verified_email', $customer->email);

        return response()->json(['ok' => true, 'message' => 'Verified.']);
    }

    public function placeOrder(Request $request)
    {
        $guestCustomerId = session()->get('guest_customer_id');

        $data = $request->validate([
            'working_group_id' => ['nullable', 'integer'],

            'customer' => ['required', 'array'],
            'customer.email' => ['required', 'email', 'max:255'],
            'customer.whatsapp' => ['required', 'string', 'max:40'],
            'customer.name' => ['nullable', 'string', 'max:120'],
            'customer.phone' => ['nullable', 'string', 'max:40'],

            'shipping' => ['nullable', 'array', 'max:30'],
            'notes' => ['nullable', 'string', 'max:4000'],
            'meta' => ['nullable', 'array', 'max:50'],
        ]);

        if (!Auth::check()) {
            abort_unless($guestCustomerId, 403);

            abort_unless(
                session()->get('guest_verified_email') === $data['customer']['email'],
                403
            );
        }

        $data['meta'] = $data['meta'] ?? [];

        $publicWgId = WorkingGroup::getPublicId() ?: 1;
        $effectiveWgId = $publicWgId;

        if (Auth::check()) {
            $effectiveWgId = Auth::user()?->working_group_id ?: $publicWgId;
        }

        $order = $this->place->placeDraft([
            ...$data,
            'working_group_id' => $effectiveWgId,
        ]);

        return response()->json([
            'ok' => true,
            'order_id' => $order->id,
            'message' => 'Order submitted for admin review.',
        ]);
    }
}

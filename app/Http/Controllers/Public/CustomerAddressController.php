<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\CustomerAddress;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CustomerAddressController extends Controller
{
    public function index(Request $request)
    {
        $guestCustomerId = session()->get('guest_customer_id');

        // Logged-in users: future-proof (optional: map user -> customer later)
        if (Auth::check() && ! $guestCustomerId) {
            return response()->json(['ok' => true, 'addresses' => []]);
        }

        abort_unless($guestCustomerId, 403);

        $addresses = CustomerAddress::query()
            ->where('customer_id', $guestCustomerId)
            ->orderByDesc('is_primary')
            ->orderByDesc('id')
            ->limit(20)
            ->get([
                'id',
                'label',
                'line1',
                'line2',
                'city',
                'district',
                'state',
                'postal_code',
                'country',
                'phone_number',
                'is_primary',
            ]);

        return response()->json(['ok' => true, 'addresses' => $addresses]);
    }

    public function store(Request $request)
    {
        $guestCustomerId = session()->get('guest_customer_id');
        abort_unless($guestCustomerId, 403);

        $data = $request->validate([
            'label' => ['nullable', 'string', 'max:80'],
            'line1' => ['required', 'string', 'max:255'],
            'line2' => ['nullable', 'string', 'max:255'],
            'city' => ['nullable', 'string', 'max:120'],
            'district' => ['nullable', 'string', 'max:120'],
            'state' => ['nullable', 'string', 'max:120'],
            'postal_code' => ['nullable', 'string', 'max:24'],
            'country' => ['nullable', 'string', 'size:2'],
            'phone_number' => ['nullable', 'string', 'max:40'],
            'is_primary' => ['nullable', 'boolean'],
        ]);

        if (! empty($data['is_primary'])) {
            CustomerAddress::query()
                ->where('customer_id', $guestCustomerId)
                ->update(['is_primary' => false]);
        }

        $addr = CustomerAddress::query()->create([
            'customer_id' => $guestCustomerId,
            'label' => $data['label'] ?? null,
            'line1' => $data['line1'],
            'line2' => $data['line2'] ?? null,
            'city' => $data['city'] ?? null,
            'district' => $data['district'] ?? null,
            'state' => $data['state'] ?? null,
            'postal_code' => $data['postal_code'] ?? null,
            'country' => $data['country'] ?? 'LK',
            'phone_number' => $data['phone_number'] ?? null,
            'is_primary' => (bool) ($data['is_primary'] ?? false),
        ]);

        return response()->json(['ok' => true, 'address' => $addr]);
    }
}


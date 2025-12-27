<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\Order;
use Illuminate\Http\Request;

class OrderPublicController extends Controller
{
    public function show(Request $request, Order $order, string $token)
    {
        $hash = hash('sha256', $token);

        abort_unless($order->public_token_hash, 404);
        abort_unless(hash_equals((string) $order->public_token_hash, $hash), 403);

        if ($order->public_token_expires_at && now()->greaterThan($order->public_token_expires_at)) {
            abort(403, 'Link expired. Please request a new secure link.');
        }

        if ($request->boolean('json') || $request->wantsJson()) {
            $relations = [
                'items',
                'items.product',
                'items.finishings',
            ];

            if (method_exists($order, 'itemFinishings')) {
                $relations[] = 'itemFinishings';
            }

            return response()->json([
                'ok' => true,
                'order' => $order->load($relations),
            ]);
        }

        return view('public.orders.secure-show', [
            'orderId' => $order->id,
            'token' => $token,
            'jsonUrl' => route('orders.public.show', ['order' => $order->id, 'token' => $token]).'?json=1',
        ]);
    }
}

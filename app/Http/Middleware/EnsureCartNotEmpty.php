<?php

namespace App\Http\Middleware;

use App\Models\Cart;
use App\Services\Cart\CartService;
use Closure;
use Illuminate\Http\Request;

class EnsureCartNotEmpty
{
    public function __construct(private CartService $cart) {}

    public function handle(Request $request, Closure $next)
    {
        $cart = $this->cart->getCart();

        $hasItems = match (true) {
            $cart instanceof Cart => $cart->items()->exists(),
            is_array($cart) => count($cart['items'] ?? []) > 0,
            default => false,
        };

        if ($hasItems) {
            return $next($request);
        }

        $message = 'Your cart is empty. Please add products to cart first.';

        if ($request->expectsJson()) {
            return response()->json(['ok' => false, 'message' => $message], 422);
        }

        return redirect()
            ->route('cart.show')
            ->with('toast', ['type' => 'error', 'message' => $message]);
    }
}


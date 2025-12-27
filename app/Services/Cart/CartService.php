<?php

namespace App\Services\Cart;

use App\Models\Cart;
use App\Models\CartItem;
use App\Models\CartItemFile;
use App\Models\CartItemFinishing;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class CartService
{
    public const SESSION_KEY = 'cart_v1';
    public const MAX_BYTES = 104857600; // 100MB

    public function getCart(): array|Cart
    {
        if (Auth::check()) {
            return $this->getOrCreateDbCart();
        }

        return session()->get(self::SESSION_KEY, [
            'uuid' => (string) Str::uuid(),
            'items' => [],
            'meta' => [],
        ]);
    }

    public function saveGuestCart(array $cart): void
    {
        session()->put(self::SESSION_KEY, $cart);
    }

    public function clear(): void
    {
        if (Auth::check()) {
            $cart = $this->getOrCreateDbCart();
            $cart->items()->delete();
            $cart->delete();

            return;
        }

        session()->forget(self::SESSION_KEY);
    }

    public function addItem(array $payload): array|CartItem
    {
        // payload: product_id, qty, width,height,unit,roll_id,variant_set_item_id, notes, meta, pricing_snapshot
        if (Auth::check()) {
            return $this->addItemDb($payload);
        }

        return $this->addItemSession($payload);
    }

    public function syncDbItemFinishings(CartItem $item, array $finishings): void
    {
        $map = $this->normalizeFinishingsMap($finishings);

        $item->finishings()->delete();

        foreach ($map as $finishingProductId => $qty) {
            if ($qty <= 0) {
                continue;
            }

            $item->finishings()->create([
                'finishing_product_id' => (int) $finishingProductId,
                'qty' => (int) $qty,
                'pricing_snapshot' => null,
                'meta' => null,
            ]);
        }
    }

    public function attachArtworkFileToDbItem(CartItem $item, UploadedFile $file): CartItemFile
    {
        if ($file->getSize() > self::MAX_BYTES) {
            abort(422, 'File too large. Please upload to a storage service and paste the link.');
        }

        $mime = $file->getClientMimeType();
        $allowed = [
            'application/pdf',
            'image/png',
            'image/jpeg',
            'application/octet-stream',
        ];

        $ext = strtolower($file->getClientOriginalExtension());
        $allowedExt = ['pdf', 'png', 'jpg', 'jpeg', 'ai', 'psd'];

        if (! in_array($mime, $allowed, true) && ! in_array($ext, $allowedExt, true)) {
            abort(422, 'Invalid file type. Allowed: PDF, AI, PSD, JPG, PNG.');
        }

        return DB::transaction(function () use ($item, $file, $mime) {
            $path = $file->store('cart/artwork', 'public');

            return $item->files()->create([
                'path' => $path,
                'disk' => 'public',
                'original_name' => $file->getClientOriginalName(),
                'mime' => $mime,
                'size_bytes' => $file->getSize(),
                'is_customer_artwork' => true,
                'meta' => [
                    'uploaded_by' => Auth::id(),
                ],
            ]);
        });
    }

    public function attachExternalArtworkUrlToDbItem(CartItem $item, string $url): void
    {
        $meta = $item->meta ?? [];
        $url = trim($url);

        if ($url === '') {
            unset($meta['artwork_external_url']);
        } else {
            $meta['artwork_external_url'] = $url;
        }

        $item->update(['meta' => $meta ?: []]);
    }

    private function getOrCreateDbCart(): Cart
    {
        $user = Auth::user();

        return Cart::query()->firstOrCreate(
            ['user_id' => $user->id, 'status' => 'active'],
            [
                'uuid' => (string) Str::uuid(),
                'working_group_id' => $user->working_group_id ?? null,
                'currency' => 'LKR',
                'meta' => [],
            ]
        );
    }

    private function addItemDb(array $payload): CartItem
    {
        $cart = $this->getOrCreateDbCart();

        return DB::transaction(function () use ($cart, $payload) {
            $meta = is_array($payload['meta'] ?? null) ? $payload['meta'] : [];
            $finishingsMap = is_array(data_get($meta, 'finishings')) ? (array) data_get($meta, 'finishings') : [];

            if (! empty($finishingsMap)) {
                $meta['finishings'] = $this->normalizeFinishingsMap($finishingsMap);
            }

            $item = $cart->items()->create([
                'product_id' => $payload['product_id'],
                'variant_set_item_id' => $payload['variant_set_item_id'] ?? null,
                'roll_id' => $payload['roll_id'] ?? null,
                'qty' => max(1, (int) ($payload['qty'] ?? 1)),
                'width' => $payload['width'] ?? null,
                'height' => $payload['height'] ?? null,
                'unit' => $payload['unit'] ?? null,
                'area_sqft' => $payload['area_sqft'] ?? null,
                'offcut_sqft' => $payload['offcut_sqft'] ?? null,
                'pricing_snapshot' => $payload['pricing_snapshot'] ?? null,
                'notes' => $payload['notes'] ?? null,
                'meta' => $meta,
            ]);

            if (! empty($finishingsMap)) {
                $this->syncDbItemFinishings($item, (array) $meta['finishings']);
            }

            return $item;
        });
    }

    private function addItemSession(array $payload): array
    {
        $cart = $this->getCart();

        $meta = is_array($payload['meta'] ?? null) ? $payload['meta'] : [];
        $finishingsMap = is_array(data_get($meta, 'finishings')) ? (array) data_get($meta, 'finishings') : [];

        if (! empty($finishingsMap)) {
            $meta['finishings'] = $this->normalizeFinishingsMap($finishingsMap);
        }

        $cart['items'][] = [
            'id' => (string) Str::uuid(),
            'product_id' => (int) $payload['product_id'],
            'variant_set_item_id' => $payload['variant_set_item_id'] ?? null,
            'roll_id' => $payload['roll_id'] ?? null,
            'qty' => max(1, (int) ($payload['qty'] ?? 1)),
            'width' => $payload['width'] ?? null,
            'height' => $payload['height'] ?? null,
            'unit' => $payload['unit'] ?? null,
            'area_sqft' => $payload['area_sqft'] ?? null,
            'offcut_sqft' => $payload['offcut_sqft'] ?? null,
            'pricing_snapshot' => $payload['pricing_snapshot'] ?? null,
            'notes' => $payload['notes'] ?? null,
            'meta' => $meta,
            'files' => [],
        ];

        $this->saveGuestCart($cart);

        return $cart;
    }

    private function normalizeFinishingsMap(array $finishings): array
    {
        $out = [];

        foreach ($finishings as $finishingProductId => $qty) {
            if (! is_numeric($finishingProductId)) {
                continue;
            }
            $finishingProductId = (int) $finishingProductId;
            if ($finishingProductId <= 0) {
                continue;
            }

            $n = is_numeric($qty) ? (int) $qty : 0;
            if ($n < 0) {
                $n = 0;
            }

            $out[$finishingProductId] = $n;
        }

        return $out;
    }
}

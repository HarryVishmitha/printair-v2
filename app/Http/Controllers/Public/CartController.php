<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\CartItem;
use App\Models\Product;
use App\Services\Cart\CartService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class CartController extends Controller
{
    public function __construct(private CartService $cart) {}

    public function show(Request $request)
    {
        $cart = $this->cart->getCart();

        if ($request->boolean('json') || $request->wantsJson()) {
            return response()->json([
                'mode' => Auth::check() ? 'db' : 'session',
                'cart' => $this->buildCartPayload($cart),
            ]);
        }

        return view('cart.show', compact('cart'));
    }

    public function addItem(Request $request)
    {
        $data = $request->validate([
            'product_id' => ['required', 'integer'],
            'variant_set_item_id' => ['nullable', 'integer'],
            'roll_id' => ['nullable', 'integer'],
            'qty' => ['nullable', 'integer', 'min:1'],

            'width' => ['nullable', 'numeric'],
            'height' => ['nullable', 'numeric'],
            'unit' => ['nullable', 'string', 'max:16'],

            'area_sqft' => ['nullable', 'numeric'],
            'offcut_sqft' => ['nullable', 'numeric'],

            'pricing_snapshot' => ['nullable', 'array'],
            'notes' => ['nullable', 'string', 'max:2000'],
            'meta' => ['nullable', 'array'],
        ]);

        $itemOrCart = $this->cart->addItem($data);

        return response()->json([
            'ok' => true,
            'data' => $itemOrCart,
        ]);
    }

    private function buildCartPayload(array|\App\Models\Cart $cart): array
    {
        if ($cart instanceof \App\Models\Cart) {
            $cart->load([
                'items.files',
                'items.finishings',
                'items.product.images',
                'items.product.optionGroups',
                'items.product.options',
                'items.product.activeVariantSets.items.option',
                'items.product.finishings',
                'items.product.allowedRolls',
            ]);

            return [
                'uuid' => $cart->uuid,
                'items' => $cart->items->map(function ($ci) {
                    $meta = is_array($ci->meta) ? $ci->meta : [];
                    $finishingsFromTable = $ci->finishings
                        ->mapWithKeys(fn ($f) => [(int) $f->finishing_product_id => (int) $f->qty])
                        ->all();

                    if (! empty($finishingsFromTable)) {
                        $meta['finishings'] = $finishingsFromTable;
                    }

                    return [
                        'id' => $ci->id,
                        'product_id' => $ci->product_id,
                        'qty' => (int) $ci->qty,
                        'width' => $ci->width,
                        'height' => $ci->height,
                        'unit' => $ci->unit,
                        'roll_id' => $ci->roll_id,
                        'variant_set_item_id' => $ci->variant_set_item_id,
                        'pricing_snapshot' => $ci->pricing_snapshot,
                        'notes' => $ci->notes,
                        'meta' => $meta,
                        'files' => $ci->files->map(fn ($f) => [
                            'id' => $f->id,
                            'disk' => $f->disk,
                            'path' => $f->path,
                            'original_name' => $f->original_name,
                            'mime' => $f->mime,
                            'size_bytes' => $f->size_bytes,
                        ])->values()->all(),
                        'product' => $ci->product ? $this->buildPublicProductJson($ci->product) : null,
                    ];
                })->values()->all(),
                'meta' => $cart->meta ?? [],
            ];
        }

        $items = (array) ($cart['items'] ?? []);
        $productIds = collect($items)
            ->map(fn ($it) => (int) ($it['product_id'] ?? 0))
            ->filter(fn ($id) => $id > 0)
            ->unique()
            ->values()
            ->all();

        $products = Product::query()
            ->whereIn('id', $productIds)
            ->where('status', 'active')
            ->get()
            ->load([
                'images',
                'optionGroups',
                'options',
                'activeVariantSets.items.option',
                'finishings',
                'allowedRolls',
            ])
            ->keyBy('id');

        $itemsOut = [];
        foreach ($items as $it) {
            $pid = (int) ($it['product_id'] ?? 0);
            $p = $products->get($pid);

            $meta = is_array($it['meta'] ?? null) ? $it['meta'] : [];

            $itemsOut[] = [
                'id' => (string) ($it['id'] ?? ''),
                'product_id' => $pid,
                'qty' => (int) ($it['qty'] ?? 1),
                'width' => $it['width'] ?? null,
                'height' => $it['height'] ?? null,
                'unit' => $it['unit'] ?? null,
                'roll_id' => $it['roll_id'] ?? null,
                'variant_set_item_id' => $it['variant_set_item_id'] ?? null,
                'pricing_snapshot' => $it['pricing_snapshot'] ?? null,
                'notes' => $it['notes'] ?? null,
                'meta' => $meta,
                'files' => array_values(array_filter((array) ($it['files'] ?? []), fn ($f) => is_array($f) && !empty($f['path']))),
                'product' => $p ? $this->buildPublicProductJson($p) : null,
            ];
        }

        return [
            'uuid' => (string) ($cart['uuid'] ?? ''),
            'items' => $itemsOut,
            'meta' => (array) ($cart['meta'] ?? []),
        ];
    }

    private function buildPublicProductJson(Product $product): array
    {
        $images = ($product->images ?? collect())->values()->map(function ($img) {
            $url = null;
            if ($img?->path) {
                $path = (string) $img->path;
                if (str_starts_with($path, 'http://') || str_starts_with($path, 'https://')) {
                    $url = $path;
                } elseif (str_starts_with($path, '/')) {
                    $url = url($path);
                } elseif (str_starts_with($path, 'storage/')) {
                    $url = asset($path);
                } else {
                    $url = Storage::disk('public')->url($path);
                }
            }

            return [
                'id' => $img->id,
                'url' => $url,
                'is_featured' => (bool) $img->is_featured,
                'sort_index' => (int) ($img->sort_index ?? 0),
            ];
        })->values()->all();

        $primary = collect($images)->firstWhere('is_featured', true) ?? ($images[0] ?? null);
        $primaryUrl = $primary['url'] ?? null;

        $optionRows = $product->options ?? collect();
        $optionsByGroup = $optionRows->groupBy('option_group_id');

        $optionGroups = ($product->optionGroups ?? collect())->values()->map(function ($g) use ($optionsByGroup) {
            $opts = ($optionsByGroup[$g->id] ?? collect())->map(fn ($o) => [
                'id' => $o->id,
                'name' => $o->label,
            ])->values()->all();

            return [
                'id' => $g->id,
                'name' => $g->name,
                'is_required' => (bool) ($g->pivot?->is_required ?? false),
                'options' => $opts,
            ];
        })->values()->all();

        $variantMatrix = ($product->activeVariantSets ?? collect())
            ->where('is_active', true)
            ->values()
            ->map(function ($set) {
                $map = ($set->items ?? collect())
                    ->mapWithKeys(function ($it) {
                        $gid = $it->option?->option_group_id;
                        if (! $gid) {
                            return [];
                        }
                        return [(int) $gid => (int) $it->option_id];
                    })
                    ->all();

                return [
                    'variant_set_id' => (int) $set->id,
                    'options' => $map,
                ];
            })
            ->filter(fn ($row) => count((array) ($row['options'] ?? [])) > 0)
            ->values();

        $variantGroupIds = $variantMatrix
            ->flatMap(fn ($row) => array_keys((array) ($row['options'] ?? [])))
            ->unique()
            ->values()
            ->all();

        if (count($variantGroupIds) > 0) {
            $optionGroups = collect($optionGroups)
                ->filter(fn ($g) => in_array((int) $g['id'], $variantGroupIds, true))
                ->values()
                ->all();
        }

        $finishings = ($product->finishings ?? collect())->values()->map(function ($f) {
            return [
                'finishing_product_id' => $f->id,
                'name' => $f->name,
                'min_qty' => $f->pivot?->min_qty,
                'max_qty' => $f->pivot?->max_qty,
                'default_qty' => $f->pivot?->default_qty,
                'is_required' => (bool) ($f->pivot?->is_required ?? false),
            ];
        })->values()->all();

        $allowedRolls = ($product->allowedRolls ?? collect())
            ->where('is_active', true)
            ->values()
            ->map(fn ($r) => [
                'roll_id' => $r->id,
                'name' => $r->name,
                'width_in' => (float) $r->width_in,
            ])->values()->all();

        return [
            'id' => $product->id,
            'name' => $product->name,
            'slug' => $product->slug,
            'product_type' => $product->product_type,
            'is_dimension_based' => (bool) ($product->product_type === 'dimension_based' || $product->requires_dimensions),
            'primary_image_url' => $primaryUrl,
            'images' => $images,
            'allowed_rolls' => $allowedRolls,
            'option_groups' => $optionGroups,
            'variant_matrix' => $variantMatrix->values()->all(),
            'finishings' => $finishings,
        ];
    }

    public function updateItem(Request $request, CartItem $item)
    {
        abort_unless(Auth::check(), 401);

        abort_unless((int) ($item->cart?->user_id ?? 0) === (int) Auth::user()->id, 403);
        abort_unless((string) ($item->cart?->status ?? '') === 'active', 403);

        $data = $request->validate([
            'qty' => ['nullable', 'integer', 'min:1'],
            'width' => ['nullable', 'numeric'],
            'height' => ['nullable', 'numeric'],
            'unit' => ['nullable', 'string', 'max:16'],
            'roll_id' => ['nullable', 'integer'],
            'variant_set_item_id' => ['nullable', 'integer'],
            'notes' => ['nullable', 'string', 'max:2000'],

            'meta' => ['nullable', 'array'],
            'pricing_snapshot' => ['nullable', 'array'],
            'finishings' => ['nullable', 'array'],
        ]);

        return DB::transaction(function () use ($item, $data) {
            $meta = $item->meta ?? [];
            if (is_array($data['meta'] ?? null)) {
                $meta = array_replace($meta, $data['meta']);
            }

            if (array_key_exists('finishings', $data) && is_array($data['finishings'])) {
                $meta['finishings'] = $data['finishings'];
            }

            $item->update([
                'qty' => array_key_exists('qty', $data) ? max(1, (int) $data['qty']) : $item->qty,
                'width' => $data['width'] ?? $item->width,
                'height' => $data['height'] ?? $item->height,
                'unit' => $data['unit'] ?? $item->unit,
                'roll_id' => array_key_exists('roll_id', $data) ? ($data['roll_id'] ?: null) : $item->roll_id,
                'variant_set_item_id' => array_key_exists('variant_set_item_id', $data) ? ($data['variant_set_item_id'] ?: null) : $item->variant_set_item_id,
                'notes' => array_key_exists('notes', $data) ? $data['notes'] : $item->notes,
                'meta' => $meta,
                'pricing_snapshot' => $data['pricing_snapshot'] ?? $item->pricing_snapshot,
            ]);

            $finishingsMap = is_array($meta['finishings'] ?? null) ? (array) $meta['finishings'] : [];
            $this->cart->syncDbItemFinishings($item, $finishingsMap);

            return response()->json([
                'ok' => true,
                'item' => $item->fresh()->load(['files', 'finishings']),
            ]);
        });
    }

    public function deleteItem(Request $request, CartItem $item)
    {
        abort_unless(Auth::check(), 401);

        abort_unless((int) ($item->cart?->user_id ?? 0) === (int) Auth::user()->id, 403);
        abort_unless((string) ($item->cart?->status ?? '') === 'active', 403);

        $item->delete();

        return response()->json(['ok' => true]);
    }

    /**
     * Logged-in users: upload file to a DB cart item
     */
    public function uploadArtwork(Request $request, CartItem $item)
    {
        abort_unless(Auth::check(), 401);

        $request->validate([
            'file' => ['required', 'file', 'max:102400'], // 100MB in KB
        ]);

        // Ensure the cart item belongs to the current user's active cart
        abort_unless((int) ($item->cart?->user_id ?? 0) === (int) Auth::user()->id, 403);
        abort_unless((string) ($item->cart?->status ?? '') === 'active', 403);

        $fileRow = $this->cart->attachArtworkFileToDbItem($item, $request->file('file'));

        return response()->json([
            'ok' => true,
            'file' => $fileRow,
        ]);
    }

    /**
     * Guest session cart: upload file to a session cart item by UUID.
     */
    public function guestUploadArtwork(Request $request)
    {
        abort_unless(! Auth::check(), 403);

        $request->validate([
            'item_uuid' => ['required', 'string', 'max:80'],
            'file' => ['required', 'file', 'max:102400'], // 100MB in KB
        ]);

        $fileEntry = $this->cart->attachArtworkFileToSessionItem(
            (string) $request->input('item_uuid'),
            $request->file('file')
        );

        return response()->json([
            'ok' => true,
            'file' => $fileEntry,
        ]);
    }

    /**
     * Logged-in users: store external artwork URL for DB cart item
     * (always visible in UI, even if upload exists)
     */
    public function saveArtworkUrl(Request $request, CartItem $item)
    {
        abort_unless(Auth::check(), 401);
        abort_unless((int) ($item->cart?->user_id ?? 0) === (int) Auth::user()->id, 403);

        $data = $request->validate([
            'url' => ['nullable', 'url', 'max:2000'],
        ]);

        $this->cart->attachExternalArtworkUrlToDbItem($item, (string) ($data['url'] ?? ''));

        return response()->json([
            'ok' => true,
        ]);
    }

    /**
     * Guest session cart: save external URL on a session item by UUID
     * (URL input is always visible)
     */
    public function guestSaveArtworkUrl(Request $request)
    {
        abort_unless(! Auth::check(), 403);

        $data = $request->validate([
            'item_uuid' => ['required', 'string', 'max:80'],
            'url' => ['nullable', 'url', 'max:2000'],
        ]);

        $cart = $this->cart->getCart(); // session cart array

        foreach ($cart['items'] as &$it) {
            if (($it['id'] ?? null) === $data['item_uuid']) {
                $it['meta'] = $it['meta'] ?? [];
                $it['meta']['artwork_external_url'] = $data['url'] ?? null;
                break;
            }
        }

        $this->cart->saveGuestCart($cart);

        return response()->json(['ok' => true]);
    }

    public function guestUpdateItem(Request $request)
    {
        abort_unless(! Auth::check(), 403);

        $data = $request->validate([
            'item_uuid' => ['required', 'string', 'max:80'],
            'qty' => ['nullable', 'integer', 'min:1'],
            'width' => ['nullable', 'numeric'],
            'height' => ['nullable', 'numeric'],
            'unit' => ['nullable', 'string', 'max:16'],
            'roll_id' => ['nullable', 'integer'],
            'variant_set_item_id' => ['nullable', 'integer'],
            'notes' => ['nullable', 'string', 'max:2000'],
            'meta' => ['nullable', 'array'],
            'pricing_snapshot' => ['nullable', 'array'],
            'finishings' => ['nullable', 'array'],
        ]);

        $cart = $this->cart->getCart(); // session cart array

        foreach ($cart['items'] as &$it) {
            if (($it['id'] ?? null) !== $data['item_uuid']) {
                continue;
            }

            if (array_key_exists('qty', $data)) {
                $it['qty'] = max(1, (int) $data['qty']);
            }
            foreach (['width', 'height', 'unit', 'roll_id', 'variant_set_item_id', 'notes', 'pricing_snapshot'] as $k) {
                if (array_key_exists($k, $data)) {
                    $it[$k] = $data[$k];
                }
            }

            $meta = is_array($it['meta'] ?? null) ? $it['meta'] : [];
            if (is_array($data['meta'] ?? null)) {
                $meta = array_replace($meta, $data['meta']);
            }
            if (array_key_exists('finishings', $data) && is_array($data['finishings'])) {
                $meta['finishings'] = $data['finishings'];
            }
            $it['meta'] = $meta;

            break;
        }

        $this->cart->saveGuestCart($cart);

        return response()->json(['ok' => true, 'cart' => $cart]);
    }

    public function guestDeleteItem(Request $request)
    {
        abort_unless(! Auth::check(), 403);

        $data = $request->validate([
            'item_uuid' => ['required', 'string', 'max:80'],
        ]);

        $cart = $this->cart->getCart(); // session cart array
        $cart['items'] = array_values(array_filter(($cart['items'] ?? []), fn ($it) => ($it['id'] ?? null) !== $data['item_uuid']));

        $this->cart->saveGuestCart($cart);

        return response()->json(['ok' => true, 'cart' => $cart]);
    }
}

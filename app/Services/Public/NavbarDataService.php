<?php

namespace App\Services\Public;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;

class NavbarDataService
{
    public const CACHE_KEY = 'navbar.public.v1';

    public function get(): array
    {
        return Cache::remember(self::CACHE_KEY, now()->addMinutes(15), function (): array {
            $placeholder = asset('assets/placeholders/product-placeholder.svg');

            $categories = Category::query()
                ->whereNull('parent_id')
                ->where('is_active', 1)
                ->where('show_in_navbar', 1)
                ->orderBy('sort_order')
                ->orderBy('id')
                ->get(['id', 'name', 'slug', 'sort_order']);

            $categoryIds = $categories->pluck('id')->all();

            $productsByCategory = collect();

            if (! empty($categoryIds)) {
                $rows = DB::query()
                    ->fromSub(
                        DB::table('products')
                            ->where('status', 'active')
                            ->where('visibility', 'public')
                            ->whereNull('deleted_at')
                            ->whereIn('category_id', $categoryIds)
                            ->select(['id', 'category_id'])
                            ->selectRaw('ROW_NUMBER() OVER (PARTITION BY category_id ORDER BY id DESC) as rn'),
                        'p'
                    )
                    ->where('rn', '<=', 6)
                    ->orderByDesc('id')
                    ->get();

                $productIds = $rows->pluck('id')->unique()->values();

                $products = Product::query()
                    ->whereIn('id', $productIds)
                    ->with(['primaryImage', 'publicPricing.tiers'])
                    ->get(['id', 'category_id', 'name', 'slug'])
                    ->keyBy('id');

                $productsByCategory = $rows
                    ->groupBy('category_id')
                    ->map(fn ($group) => $group->map(fn ($r) => $products->get($r->id))->filter()->values());
            }

            return [
                'generated_at' => now()->toIso8601String(),
                'categories' => $categories->map(function (Category $category) use ($productsByCategory, $placeholder): array {
                    $products = $productsByCategory->get($category->id, collect());

                    return [
                        'id' => $category->id,
                        'name' => $category->name,
                        'slug' => $category->slug,
                        'url' => url('/category/'.$category->slug),
                        'products' => $products->map(function (Product $product) use ($placeholder): array {
                            $imageUrl = $placeholder;
                            $img = $product->primaryImage;

                            if ($img) {
                                if ($img->path) {
                                    $imageUrl = Storage::disk($img->disk ?? 'public')->url($img->path);
                                } else {
                                    $imagePath = $img->image_path
                                        ?? $img->file_path
                                        ?? $img->url
                                        ?? null;

                                    if (is_string($imagePath) && $imagePath !== '') {
                                        $imageUrl = str_starts_with($imagePath, 'http://') || str_starts_with($imagePath, 'https://')
                                            ? $imagePath
                                            : asset(ltrim($imagePath, '/'));
                                    }
                                }
                            }

                            return [
                                'id' => $product->id,
                                'slug' => $product->slug,
                                'name' => $product->name,
                                'url' => url('/products/'.$product->slug),
                                'image_url' => $imageUrl,
                                'primaryImage' => $imageUrl,
                                'price' => $this->resolvePublicDisplayPrice($product),
                                'public_price' => $this->resolvePublicDisplayPrice($product),
                            ];
                        })->values(),
                    ];
                })->values(),
            ];
        });
    }

    private function resolvePublicDisplayPrice(Product $product): ?array
    {
        $pricing = $product->publicPricing;

        if (! $pricing) {
            return null;
        }

        if ($pricing->tiers && $pricing->tiers->count() > 0) {
            $min = $pricing->tiers->min('price');
            return [
                'amount' => (float) $min,
                'formatted' => 'Rs. ' . number_format((float) $min, 2),
            ];
        }

        if (! is_null($pricing->base_price)) {
            $v = (float) $pricing->base_price;
            return ['amount' => $v, 'formatted' => 'Rs. ' . number_format($v, 2)];
        }

        if (! is_null($pricing->min_charge)) {
            $v = (float) $pricing->min_charge;
            return ['amount' => $v, 'formatted' => 'Rs. ' . number_format($v, 2)];
        }

        return null;
    }

    public static function bustCache(): void
    {
        Cache::forget(self::CACHE_KEY);
    }
}

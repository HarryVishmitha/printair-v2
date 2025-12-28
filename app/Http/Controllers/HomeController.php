<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Services\ActivityLogger;
use App\Services\Pricing\DimensionCalculatorService;
use App\Services\Pricing\PricingResolverService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;

class HomeController extends Controller
{
    protected function usertype()
    {
        if (! Auth::check()) {
            return 'login';
        } else {
            $role = Auth::user()->role->name;
            $dashboard = match ($role) {
                'Super Admin' => 'superadmin.dashboard',
                'Admin' => 'admin.dashboard',
                'Staff' => 'staff.dashboard',
                'User' => 'user.dashboard',
                default => 'user.dashboard',
            };

            return $dashboard;
        }
    }

    public function index(Request $request)
    {
        $seo = [
            'title' => 'Home',
            'description' => 'Printair Advertising is a leading printing company in Sri Lanka, offering premium digital, offset, and large-format printing solutions. From roll-up banners and X-banners to stickers, labels, invitations, business cards, and custom branding materials, we deliver fast, high-quality, professional printing for businesses and events.',
            'keywords' => 'printing sri lanka, printair, print shop sri lanka, printing services, roll up banner sri lanka, x banner printing, sticker printing sri lanka, digital printing, offset printing, large format printing, business cards sri lanka, invitation printing, label printing, custom banners sri lanka, signage printing, outdoor printing',
        ];

        $dashboard = $this->usertype();

        ActivityLogger::log(
            $request->user(),
            'home.index',
            'Viewed home page'
        );

        return view('home', compact('seo', 'dashboard'));
    }

    public function categories()
    {
        $rows = \App\Models\Category::query()
            ->whereNull('parent_id')
            ->where('is_active', true)
            ->where('show_in_navbar', true)
            ->orderBy('name')
            ->get(['id', 'name', 'slug', 'icon_path', 'cover_image_path']);

        $categories = $rows
            ->map(function (\App\Models\Category $c) {
                $coverPath = is_string($c->cover_image_path) ? trim($c->cover_image_path) : '';

                $imageUrl = null;
                if ($coverPath !== '') {
                    if (str_starts_with($coverPath, 'http://') || str_starts_with($coverPath, 'https://')) {
                        $imageUrl = $coverPath;
                    } elseif (str_starts_with($coverPath, '/')) {
                        $imageUrl = url($coverPath);
                    } elseif (str_starts_with($coverPath, 'storage/')) {
                        $imageUrl = asset($coverPath);
                    } else {
                        $imageUrl = Storage::disk('public')->url($coverPath);
                    }
                }

                return [
                    'id' => $c->id,
                    'name' => $c->name,
                    'slug' => $c->slug,
                    'icon' => $c->icon_path,
                    'image' => $imageUrl,
                    'cover_image_url' => $imageUrl,
                ];
            })
            ->values();

        return response()->json($categories);
    }

    public function popularProducts(Request $request, PricingResolverService $pricing)
    {
        $user = Auth::user();
        $wgId = $user?->working_group_id ?? null;

        $limit = max(1, min(12, (int) $request->integer('limit', 6)));

        // Cache per working group (public users share same cache)
        $cacheKey = 'home:popular-products:'.($wgId ?: 'public').':'.$limit;

        $items = Cache::remember($cacheKey, now()->addMinutes(10), function () use ($wgId, $pricing, $limit) {
            $products = Product::query()
                ->active()
                ->visibleToPublic()
                ->where('product_type', '!=', 'finishing')
                ->with([
                    'images:id,product_id,path,is_featured,sort_index',
                    'seo:product_id,seo_description',
                ])
                ->select('id', 'name', 'slug', 'product_type', 'min_qty', 'short_description')
                ->orderByDesc('id')
                ->limit($limit)
                ->get();

            return $products->map(function (Product $p) use ($wgId, $pricing) {
                $seoDesc = $p->seo?->seo_description ?? $p->short_description ?? null;
                $seoDesc = $seoDesc ? trim((string) $seoDesc) : null;
                if ($seoDesc === '') {
                    $seoDesc = null;
                }

                $primary = $p->images?->firstWhere('is_featured', true) ?? $p->images?->first();
                $imageUrl = asset('assets/placeholders/product.png');
                if ($primary?->path) {
                    $path = (string) $primary->path;
                    if (str_starts_with($path, 'http://') || str_starts_with($path, 'https://')) {
                        $imageUrl = $path;
                    } elseif (str_starts_with($path, '/')) {
                        $imageUrl = url($path);
                    } elseif (str_starts_with($path, 'storage/')) {
                        $imageUrl = asset($path);
                    } else {
                        $imageUrl = Storage::disk('public')->url($path);
                    }
                }

                $priceType = 'none'; // 'unit' | 'sqft' | 'none'
                $from = null; // numeric-ish string
                $label = null; // ready-to-render string

                $rp = $pricing->resolve($p, $wgId);
                if ($rp) {
                    // 1) Unit/tier price (qty=1)
                    $unit = $pricing->baseUnitPrice($rp, 1);
                    if ($unit !== null) {
                        $priceType = 'unit';
                        $from = $unit;
                        $label = 'From Rs. '.number_format((float) $unit, 0);
                    } else {
                        // 2) Dimension rate fallback
                        $rates = $pricing->dimensionRates($rp);
                        if (! empty($rates['rate_per_sqft'])) {
                            $priceType = 'sqft';
                            $from = $rates['rate_per_sqft'];
                            $label = 'From Rs. '.number_format((float) $rates['rate_per_sqft'], 0).' / sqft';
                        }
                    }
                }

                return [
                    'id' => $p->id,
                    'name' => $p->name,
                    'slug' => $p->slug,
                    'href' => url("/products/{$p->slug}"),
                    'primaryImage' => $imageUrl,
                    'seoDescription' => $seoDesc,
                    'price' => [
                        'type' => $priceType,
                        'from' => $from,
                        'label' => $label,
                    ],
                    'quoteHref' => route('quotes.create'),
                ];
            })->values()->all();
        });

        return response()->json([
            'items' => $items,
        ]);
    }

    public function privacy()
    {
        $seo = [
            'title' => 'Privacy Policy',
            'description' => 'Learn how Printair Advertising collects, uses, and protects your information, including design uploads and customer communication.',
            'keywords' => 'Printair privacy policy, printair.lk privacy, printing privacy, design file privacy, sri lanka printing',
            'canonical' => url('/privacy-policy'),
            // Optional OG image (set your real path)
            'image' => asset('assets/printair/printairlogo.png'),
        ];

        $dashboard = $this->usertype();

        return view('privacy', compact('seo', 'dashboard'));
    }

    public function termsAndConditions()
    {
        $dashboard = $this->usertype();
        $seo = [
            'title' => 'Terms & Conditions',
            'description' => 'Read Printair Advertising Terms & Conditions including orders, design approvals, production policy, working group pricing, warranties, payments, delivery, and liability.',
            'keywords' => 'Printair terms and conditions, printair.lk terms, printing terms sri lanka, design upload terms, warranty disclaimer',
            'canonical' => url('/terms-and-conditions'),
            'image' => asset('assets/printair/printairlogo.png'), // optional
        ];

        return view('terms-and-conditions', compact('seo', 'dashboard'));
    }

    public function about()
    {
        $seo = [
            'title' => 'About Us',
            'description' => 'Learn about Printair Advertising—our story, values, production approach, and milestones as a modern design & printing partner in Sri Lanka.',
            'keywords' => 'Printair about, printair advertising, printing sri lanka, design and printing, corporate printing partner',
            'canonical' => url('/about-us'),
            'image' => asset('assets/printair/printairlogo.png'), // optional
        ];
        $dashboard = $this->usertype();

        return view('about-us', compact('seo', 'dashboard'));
    }

    public function contact()
    {
        $seo = [
            'title' => 'Contact Us',
            'description' => 'Contact Printair Advertising for quotations, corporate partnerships, and printing services. Email, WhatsApp, or send a message through our contact form.',
            'keywords' => 'Printair contact, printair.lk contact, printing sri lanka contact, whatsapp printair',
            'canonical' => url('/contact'),
            'image' => asset('assets/printair/printairlogo.png'), // optional
        ];

        $dashboard = $this->usertype();

        return view('contact', compact('seo', 'dashboard'));
    }

    public function partners()
    {
        $seo = [
            'title' => 'Partner Program',
            'description' => 'Join Printair Advertising’s Partner Program to access wholesale pricing, priority production, and long-term print collaboration. Contact us via WhatsApp to become a partner.',
            'keywords' => 'Printair partner program, printair wholesale printing, printair business partners, printing partners sri lanka, printair whatsapp partner',
            'canonical' => url('/partners'),
            'image' => asset('assets/printair/printairlogo.png'), // optional
        ];

        $dashboard = $this->usertype();

        return view('partners', compact('seo', 'dashboard'));
    }

    public function products(Request $request)
    {
        $seo = [
            'title' => 'Products',
            'description' => 'Browse Printair products with starting prices. Filter by category, search fast, and jump into details.',
            'keywords' => 'Printair products, printing products sri lanka, banners, stickers, business cards, offset printing, digital printing, large format printing',
            'canonical' => url('/products'),
            'image' => asset('assets/printair/printairlogo.png'), // optional
        ];

        $dashboard = $this->usertype();

        ActivityLogger::log(
            $request->user(),
            'products.index',
            'Viewed products listing'
        );

        return view('products.index', compact('seo', 'dashboard'));
    }

    public function services(Request $request)
    {
        $seo = [
            'title' => 'Services',
            'description' => 'Browse Printair services with starting prices. Filter by category, search fast, and jump into details.',
            'keywords' => 'Printair services, printing services sri lanka, design services, installation services, branding services',
            'canonical' => url('/services'),
            'image' => asset('assets/printair/printairlogo.png'), // optional
        ];

        $dashboard = $this->usertype();

        ActivityLogger::log(
            $request->user(),
            'services.index',
            'Viewed services listing'
        );

        return view('services.index', compact('seo', 'dashboard'));
    }

    public function productsJson(Request $request, PricingResolverService $pricing)
    {
        $user = Auth::user();
        $wgId = $user?->working_group_id ?? null;

        $productsVersion = Product::query()
            ->active()
            ->visibleToPublic()
            ->whereIn('product_type', ['standard', 'dimension_based'])
            ->max('updated_at');

        $pricingVersion = \App\Models\ProductPricing::query()->whereNull('deleted_at')->max('updated_at');
        $tierVersion = \App\Models\ProductPriceTier::query()->whereNull('deleted_at')->max('updated_at');
        $rollPricingVersion = \App\Models\ProductRollPricing::query()->whereNull('deleted_at')->max('updated_at');
        $variantPricingVersion = \App\Models\ProductVariantPricing::query()->whereNull('deleted_at')->max('updated_at');
        $finishingPricingVersion = \App\Models\ProductFinishingPricing::query()->whereNull('deleted_at')->max('updated_at');

        $versionHash = md5(implode('|', [
            (string) $productsVersion,
            (string) $pricingVersion,
            (string) $tierVersion,
            (string) $rollPricingVersion,
            (string) $variantPricingVersion,
            (string) $finishingPricingVersion,
        ]));

        $cacheKey = 'products:index-json:v3:'.($wgId ?: 'public').':'.$versionHash;

        $items = Cache::remember($cacheKey, now()->addMinutes(10), function () use ($wgId, $pricing) {
            $products = Product::query()
                ->active()
                ->visibleToPublic()
                ->whereIn('product_type', ['standard', 'dimension_based'])
                ->with([
                    'category:id,name,slug',
                    'images:id,product_id,path,is_featured,sort_index',
                    'seo:product_id,seo_description',
                ])
                ->withCount([
                    'productRolls as rolls_count' => fn ($q) => $q->where('is_active', true),
                ])
                ->select(
                    'id',
                    'category_id',
                    'name',
                    'slug',
                    'product_type',
                    'short_description',
                    'min_qty',
                    'requires_dimensions',
                    'allow_custom_size',
                    'allow_predefined_sizes',
                    'meta',
                    'created_at'
                )
                ->orderByDesc('id')
                ->get();

            return $products->map(function (Product $p) use ($wgId, $pricing) {
                $short = $p->short_description ?? $p->seo?->seo_description ?? null;
                $short = $short ? trim((string) $short) : null;
                if ($short === '') {
                    $short = null;
                }

                $primary = $p->images?->firstWhere('is_featured', true) ?? $p->images?->first();
                $imageUrl = null;
                if ($primary?->path) {
                    $path = (string) $primary->path;
                    if (str_starts_with($path, 'http://') || str_starts_with($path, 'https://')) {
                        $imageUrl = $path;
                    } elseif (str_starts_with($path, '/')) {
                        $imageUrl = url($path);
                    } elseif (str_starts_with($path, 'storage/')) {
                        $imageUrl = asset($path);
                    } else {
                        $imageUrl = Storage::disk('public')->url($path);
                    }
                }

                $startingPrice = null;
                $currency = 'LKR';
                $startingLabel = null;
                $startingHint = null;

                $rp = $pricing->resolve($p, $wgId);
                if ($rp) {
                    $unit = $pricing->baseUnitPrice($rp, 1);
                    if ($unit !== null) {
                        $startingPrice = (float) $unit;
                        $startingLabel = $currency.' '.number_format((float) $unit, 0);
                    } else {
                        $rates = $pricing->dimensionRates($rp);
                        if (! empty($rates['min_charge'])) {
                            $startingPrice = (float) $rates['min_charge'];
                            $startingLabel = $currency.' '.number_format((float) $rates['min_charge'], 0);
                            $startingHint = 'Min charge';
                        } elseif (! empty($rates['rate_per_sqft'])) {
                            $startingPrice = (float) $rates['rate_per_sqft'];
                            $startingLabel = $currency.' '.number_format((float) $rates['rate_per_sqft'], 0).' / sqft';
                            $startingHint = 'Per sqft';
                        }
                    }
                }

                $meta = is_array($p->meta) ? $p->meta : (json_decode((string) ($p->meta ?? ''), true) ?: []);
                $isPopular = (bool) ($meta['is_popular'] ?? $meta['popular'] ?? false);

                $isRollBased = $p->product_type === 'dimension_based' || (bool) $p->requires_dimensions;
                $isNew = (bool) ($p->created_at && $p->created_at->greaterThan(now()->subDays(14)));

                return [
                    'id' => $p->id,
                    'name' => $p->name,
                    'href' => route('products.show', $p->slug),
                    'primaryImage' => $imageUrl,
                    'category' => $p->category ? [
                        'name' => $p->category->name,
                        'slug' => $p->category->slug,
                    ] : null,
                    'starting_price' => $startingPrice,
                    'starting_label' => $startingLabel,
                    'starting_hint' => $startingHint,
                    'currency' => $currency,
                    'short' => $short,
                    'product_type' => $p->product_type,
                    'is_roll_based' => $isRollBased,
                    'rolls_count' => (int) ($p->rolls_count ?? 0),
                    'min_qty' => $p->min_qty !== null ? (int) $p->min_qty : null,
                    'allow_custom_size' => (bool) $p->allow_custom_size,
                    'allow_predefined_sizes' => (bool) $p->allow_predefined_sizes,
                    'is_new' => $isNew,
                    'is_popular' => $isPopular,
                ];
            })->values()->all();
        });

        return response()->json([
            'data' => $items,
        ]);
    }

    public function servicesJson(Request $request, PricingResolverService $pricing)
    {
        $user = Auth::user();
        $wgId = $user?->working_group_id ?? null;

        $servicesVersion = Product::query()
            ->active()
            ->visibleToPublic()
            ->where('product_type', 'service')
            ->max('updated_at');

        $pricingVersion = \App\Models\ProductPricing::query()->whereNull('deleted_at')->max('updated_at');
        $tierVersion = \App\Models\ProductPriceTier::query()->whereNull('deleted_at')->max('updated_at');
        $rollPricingVersion = \App\Models\ProductRollPricing::query()->whereNull('deleted_at')->max('updated_at');
        $variantPricingVersion = \App\Models\ProductVariantPricing::query()->whereNull('deleted_at')->max('updated_at');
        $finishingPricingVersion = \App\Models\ProductFinishingPricing::query()->whereNull('deleted_at')->max('updated_at');

        $versionHash = md5(implode('|', [
            (string) $servicesVersion,
            (string) $pricingVersion,
            (string) $tierVersion,
            (string) $rollPricingVersion,
            (string) $variantPricingVersion,
            (string) $finishingPricingVersion,
        ]));

        $cacheKey = 'services:index-json:v2:'.($wgId ?: 'public').':'.$versionHash;

        $items = Cache::remember($cacheKey, now()->addMinutes(10), function () use ($wgId, $pricing) {
            $services = Product::query()
                ->active()
                ->visibleToPublic()
                ->where('product_type', 'service')
                ->with([
                    'category:id,name,slug',
                    'images:id,product_id,path,is_featured,sort_index',
                    'seo:product_id,seo_description',
                ])
                ->withCount([
                    'productRolls as rolls_count' => fn ($q) => $q->where('is_active', true),
                ])
                ->select(
                    'id',
                    'category_id',
                    'name',
                    'slug',
                    'product_type',
                    'short_description',
                    'min_qty',
                    'requires_dimensions',
                    'allow_custom_size',
                    'allow_predefined_sizes',
                    'meta',
                    'created_at'
                )
                ->orderByDesc('id')
                ->get();

            return $services->map(function (Product $p) use ($wgId, $pricing) {
                $short = $p->short_description ?? $p->seo?->seo_description ?? null;
                $short = $short ? trim((string) $short) : null;
                if ($short === '') {
                    $short = null;
                }

                $primary = $p->images?->firstWhere('is_featured', true) ?? $p->images?->first();
                $imageUrl = null;
                if ($primary?->path) {
                    $path = (string) $primary->path;
                    if (str_starts_with($path, 'http://') || str_starts_with($path, 'https://')) {
                        $imageUrl = $path;
                    } elseif (str_starts_with($path, '/')) {
                        $imageUrl = url($path);
                    } elseif (str_starts_with($path, 'storage/')) {
                        $imageUrl = asset($path);
                    } else {
                        $imageUrl = Storage::disk('public')->url($path);
                    }
                }

                $startingPrice = null;
                $currency = 'LKR';
                $startingLabel = null;
                $startingHint = null;

                $rp = $pricing->resolve($p, $wgId);
                if ($rp) {
                    $unit = $pricing->baseUnitPrice($rp, 1);
                    if ($unit !== null) {
                        $startingPrice = (float) $unit;
                        $startingLabel = $currency.' '.number_format((float) $unit, 0);
                    } else {
                        $rates = $pricing->dimensionRates($rp);
                        if (! empty($rates['min_charge'])) {
                            $startingPrice = (float) $rates['min_charge'];
                            $startingLabel = $currency.' '.number_format((float) $rates['min_charge'], 0);
                            $startingHint = 'Min charge';
                        } elseif (! empty($rates['rate_per_sqft'])) {
                            $startingPrice = (float) $rates['rate_per_sqft'];
                            $startingLabel = $currency.' '.number_format((float) $rates['rate_per_sqft'], 0).' / sqft';
                            $startingHint = 'Per sqft';
                        }
                    }
                }

                $meta = is_array($p->meta) ? $p->meta : (json_decode((string) ($p->meta ?? ''), true) ?: []);
                $isPopular = (bool) ($meta['is_popular'] ?? $meta['popular'] ?? false);

                $isRollBased = $p->product_type === 'dimension_based' || (bool) $p->requires_dimensions;
                $isNew = (bool) ($p->created_at && $p->created_at->greaterThan(now()->subDays(14)));

                return [
                    'id' => $p->id,
                    'name' => $p->name,
                    'href' => route('products.show', $p->slug),
                    'primaryImage' => $imageUrl,
                    'category' => $p->category ? [
                        'name' => $p->category->name,
                        'slug' => $p->category->slug,
                    ] : null,
                    'starting_price' => $startingPrice,
                    'starting_label' => $startingLabel,
                    'starting_hint' => $startingHint,
                    'currency' => $currency,
                    'short' => $short,
                    'product_type' => $p->product_type,
                    'is_roll_based' => $isRollBased,
                    'rolls_count' => (int) ($p->rolls_count ?? 0),
                    'min_qty' => $p->min_qty !== null ? (int) $p->min_qty : null,
                    'allow_custom_size' => (bool) $p->allow_custom_size,
                    'allow_predefined_sizes' => (bool) $p->allow_predefined_sizes,
                    'is_new' => $isNew,
                    'is_popular' => $isPopular,
                ];
            })->values()->all();
        });

        return response()->json([
            'data' => $items,
        ]);
    }

    public function productShow(Request $request, Product $product, PricingResolverService $pricing)
    {
        if ($product->status !== 'active' || $product->visibility !== 'public' || $product->product_type === 'finishing') {
            abort(404);
        }

        $product->load([
            'category:id,name,slug',
            'images:id,product_id,path,is_featured,sort_index',
            'seo:product_id,seo_title,seo_description,seo_keywords,og_image_path,canonical_url',
            'files:id,product_id,label,file_path,file_type,visibility',
            'specGroups:id,product_id,name,sort_index,is_internal',
            'specGroups.specs:id,product_id,spec_group_id,spec_key,spec_value,sort_index,is_internal',
            'optionGroups:id,code,name',
            'options:id,option_group_id,label',
            'activeVariantSets:id,product_id,code,is_active',
            'activeVariantSets.items:id,variant_set_id,option_id',
            'activeVariantSets.items.option:id,option_group_id,label',
            'finishings:id,name,slug,finishing_charge_mode',
            'allowedRolls:id,name,slug,width_in,is_active',
        ]);

        $dashboard = $this->usertype();

        $primary = $product->images?->firstWhere('is_featured', true) ?? $product->images?->first();
        $primaryImageUrl = null;
        if ($primary?->path) {
            $path = (string) $primary->path;
            if (str_starts_with($path, 'http://') || str_starts_with($path, 'https://')) {
                $primaryImageUrl = $path;
            } elseif (str_starts_with($path, '/')) {
                $primaryImageUrl = url($path);
            } elseif (str_starts_with($path, 'storage/')) {
                $primaryImageUrl = asset($path);
            } else {
                $primaryImageUrl = Storage::disk('public')->url($path);
            }
        }

        $wgId = Auth::user()?->working_group_id ?? null;
        $currency = 'LKR';
        $startingPrice = null;
        $startingLabel = null;

        $rp = $pricing->resolve($product, $wgId);
        if ($rp) {
            $unit = $pricing->baseUnitPrice($rp, 1);
            if ($unit !== null) {
                $startingPrice = (float) $unit;
                $startingLabel = $currency.' '.number_format((float) $unit, 0);
            } else {
                $rates = $pricing->dimensionRates($rp);
                if (! empty($rates['min_charge'])) {
                    $startingPrice = (float) $rates['min_charge'];
                    $startingLabel = $currency.' '.number_format((float) $rates['min_charge'], 0);
                } elseif (! empty($rates['rate_per_sqft'])) {
                    $startingPrice = (float) $rates['rate_per_sqft'];
                    $startingLabel = $currency.' '.number_format((float) $rates['rate_per_sqft'], 0).' / sqft';
                }
            }
        }

        $seoTitle = $product->seo?->seo_title ?: $product->name;
        $seoDesc = $product->seo?->seo_description ?: ($product->short_description ?? null);
        $seoKeywords = $product->seo?->seo_keywords ?? null;

        $seo = [
            'title' => $seoTitle,
            'description' => $seoDesc,
            'keywords' => $seoKeywords,
            'canonical' => $product->seo?->canonical_url ?: route('products.show', $product->slug),
            'image' => $primaryImageUrl ?: asset('assets/printair/printairlogo.png'),
        ];

        ActivityLogger::log(
            $request->user(),
            'products.show',
            'Viewed product details',
            ['product_id' => $product->id, 'slug' => $product->slug]
        );

        $publicAttachments = $product->files
            ? $product->files->where('visibility', 'public')->values()
            : collect();

        $attachmentJson = $publicAttachments->map(function ($f) {
            $path = (string) ($f->file_path ?? '');
            $url = null;
            if ($path !== '') {
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
                'id' => $f->id,
                'name' => $f->label,
                'url' => $url,
                'type' => $f->file_type,
            ];
        })->values()->all();

        $specGroups = ($product->specGroups ?? collect())
            ->where('is_internal', false)
            ->sortBy('sort_index')
            ->values()
            ->map(function ($g) {
                $specs = ($g->specs ?? collect())
                    ->where('is_internal', false)
                    ->sortBy('sort_index')
                    ->values()
                    ->map(fn ($s) => [
                        'id' => $s->id,
                        'label' => $s->spec_key,
                        'value' => $s->spec_value,
                    ])->values()->all();

                return [
                    'id' => $g->id,
                    'name' => $g->name,
                    'specs' => $specs,
                ];
            })->values()->all();

        $optionRows = ($product->options ?? collect())->values();
        $optionIds = $optionRows->pluck('id')->all();
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
            $optionGroups = collect($optionGroups)->filter(fn ($g) => in_array((int) $g['id'], $variantGroupIds, true))->values()->all();
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

        $allowedRolls = ($product->allowedRolls ?? collect())
            ->where('is_active', true)
            ->values()
            ->map(fn ($r) => [
                'roll_id' => $r->id,
                'name' => $r->name,
                'width_in' => (float) $r->width_in,
            ])->values()->all();

        $productJson = [
            'id' => $product->id,
            'name' => $product->name,
            'slug' => $product->slug,
            'product_type' => $product->product_type,
            'short_description' => $product->short_description,
            'description' => $product->description,
            'seo_keywords' => $product->seo?->seo_keywords,
            'category' => $product->category ? [
                'name' => $product->category->name,
                'slug' => $product->category->slug,
            ] : null,
            'images' => $images,
            'is_dimension_based' => (bool) ($product->product_type === 'dimension_based' || $product->requires_dimensions),
            'allowed_rolls' => $allowedRolls,
            'option_groups' => $optionGroups,
            'variant_matrix' => $variantMatrix->values()->all(),
            'finishings' => $finishings,
            'attachments' => $attachmentJson,
            'spec_groups' => $specGroups,
            'mock_base_price' => $startingPrice,
        ];

        return view('products.show', [
            'seo' => $seo,
            'dashboard' => $dashboard,
            'product' => $product,
            'primaryImageUrl' => $primaryImageUrl,
            'startingPrice' => $startingPrice,
            'startingLabel' => $startingLabel,
            'currency' => $currency,
            'productJson' => $productJson,
            'initialWg' => 'public',
        ]);
    }

    public function productPriceQuote(
        Request $request,
        Product $product,
        PricingResolverService $pricing,
        DimensionCalculatorService $dimensions
    ) {
        if ($product->status !== 'active' || $product->visibility !== 'public' || $product->product_type === 'finishing') {
            abort(404);
        }

        $validated = $request->validate([
            'qty' => ['nullable', 'integer', 'min:1', 'max:100000'],
            'width' => ['nullable', 'numeric', 'min:0.01'],
            'height' => ['nullable', 'numeric', 'min:0.01'],
            'unit' => ['nullable', 'string', 'in:in,ft,mm,cm,m'],
            'roll_id' => ['nullable', 'integer'],
            'variants' => ['nullable', 'array'],
            'variants.*' => ['nullable', 'integer'],
            'options' => ['nullable', 'array', 'max:60'],
            'options.*' => ['nullable', 'integer'],
            'finishings' => ['nullable', 'array'],
            'finishings.*' => ['nullable'],
            'artwork' => ['nullable', 'array'],
            'artwork.mode' => ['nullable', 'string', 'in:upload,hire'],
            'artwork.brief' => ['nullable', 'string', 'max:4000'],
        ]);

        $qty = (int) ($validated['qty'] ?? 1);
        $qty = max(1, min(100000, $qty));

        $wgId = Auth::user()?->working_group_id ?? null;
        $rp = $pricing->resolve($product, $wgId);

        if (! $rp) {
            return response()->json([
                'total' => 0,
                'breakdown' => [],
            ]);
        }

        $breakdown = [];
        $total = 0.0;

        $isDimensionBased = (bool) ($product->product_type === 'dimension_based' || $product->requires_dimensions);

        $widthIn = null;
        $heightIn = null;
        $unit = (string) ($validated['unit'] ?? 'in');
        $requestedWidthIn = null;
        $requestedHeightIn = null;
        $selectedRollId = null;
        $selectedRollWidthIn = null;
        $selectedRollRotated = false;
        $selectedRollAuto = false;

        if ($isDimensionBased) {
            $w = $validated['width'] ?? null;
            $h = $validated['height'] ?? null;

            if ($w === null || $h === null) {
                return response()->json([
                    'message' => 'Width and height are required for this product.',
                ], 422);
            }

            $widthIn = $dimensions->toInches((float) $w, $unit);
            $heightIn = $dimensions->toInches((float) $h, $unit);
            $requestedWidthIn = (float) $widthIn;
            $requestedHeightIn = (float) $heightIn;
        }

        $rollId = isset($validated['roll_id']) ? (int) $validated['roll_id'] : null;
        $rollWidthIn = null;
        $allowRotate = (bool) $product->allow_rotation_to_fit_roll;

        // "One side fits roll width" rule:
        // Only ONE of the user-entered dimensions must fit within the roll's fixed width.
        // Preference order: try the larger side first; if no roll fits, try the other side (rotation).
        $firstSide = null;  // 'w' or 'h' (meaning which user-entered dimension we try to place across roll width)
        $secondSide = null;
        if ($requestedWidthIn !== null && $requestedHeightIn !== null) {
            $firstSide = $requestedWidthIn >= $requestedHeightIn ? 'w' : 'h';
            $secondSide = $firstSide === 'w' ? 'h' : 'w';
        }

        if ($isDimensionBased && $rollId) {
            $allowed = $product->productRolls()
                ->where('roll_id', $rollId)
                ->where('is_active', true)
                ->exists();

            if (! $allowed) {
                return response()->json(['message' => 'Selected roll is not available for this product.'], 422);
            }

            $roll = \App\Models\Roll::query()->where('id', $rollId)->active()->first();
            if (! $roll) {
                return response()->json(['message' => 'Selected roll is invalid or inactive.'], 422);
            }

            $rollWidthIn = (float) $roll->width_in;

            if ($firstSide && $secondSide && $requestedWidthIn !== null && $requestedHeightIn !== null) {
                $fit = false;
                foreach ([$firstSide, $secondSide] as $side) {
                    if ($side === 'w') {
                        if ($rollWidthIn < (float) $requestedWidthIn) {
                            continue;
                        }
                        $widthIn = (float) $requestedWidthIn;   // across roll
                        $heightIn = (float) $requestedHeightIn; // along roll length
                        $selectedRollRotated = false;
                        $fit = true;
                        break;
                    }

                    // side === 'h' => requires rotation unless it's a square
                    if (! $allowRotate && (float) $requestedWidthIn !== (float) $requestedHeightIn) {
                        continue;
                    }
                    if ($rollWidthIn < (float) $requestedHeightIn) {
                        continue;
                    }

                    $widthIn = (float) $requestedHeightIn;
                    $heightIn = (float) $requestedWidthIn;
                    $selectedRollRotated = (float) $requestedWidthIn !== (float) $requestedHeightIn;
                    $fit = true;
                    break;
                }

                if (! $fit) {
                    return response()->json(['message' => 'Selected size does not fit the chosen roll width.'], 422);
                }

                $selectedRollId = (int) $rollId;
                $selectedRollWidthIn = $rollWidthIn;
                $selectedRollAuto = false;
            }
        } elseif ($isDimensionBased && $widthIn !== null && $heightIn !== null) {
            // Auto roll selection:
            // Try the larger side first; if no roll fits, try the other side.
            $rolls = $product->allowedRolls()
                ->active()
                ->get(['rolls.id', 'rolls.width_in'])
                ->sortBy(fn ($r) => (float) $r->width_in)
                ->values();

            if ($rolls->count() > 0) {
                if ($firstSide && $secondSide && $requestedWidthIn !== null && $requestedHeightIn !== null) {
                    $chosen = null;
                    $chosenSide = null;

                    foreach ([$firstSide, $secondSide] as $side) {
                        if ($side === 'w') {
                            $chosen = $rolls->first(fn ($row) => (float) $row->width_in >= (float) $requestedWidthIn);
                            $chosenSide = $chosen ? 'w' : null;
                            if ($chosen) {
                                break;
                            }
                            continue;
                        }

                        // side === 'h'
                        if (! $allowRotate && (float) $requestedWidthIn !== (float) $requestedHeightIn) {
                            continue;
                        }
                        $chosen = $rolls->first(fn ($row) => (float) $row->width_in >= (float) $requestedHeightIn);
                        $chosenSide = $chosen ? 'h' : null;
                        if ($chosen) {
                            break;
                        }
                    }

                    if (! $chosen || ! $chosenSide) {
                        return response()->json(['message' => 'Selected size does not fit any available roll width.'], 422);
                    }

                    $rollId = (int) $chosen->id;
                    $rollWidthIn = (float) $chosen->width_in;

                    if ($chosenSide === 'w') {
                        $widthIn = (float) $requestedWidthIn;
                        $heightIn = (float) $requestedHeightIn;
                        $selectedRollRotated = false;
                    } else {
                        $widthIn = (float) $requestedHeightIn;
                        $heightIn = (float) $requestedWidthIn;
                        $selectedRollRotated = (float) $requestedWidthIn !== (float) $requestedHeightIn;
                    }

                    $selectedRollId = (int) $rollId;
                    $selectedRollWidthIn = (float) $rollWidthIn;
                    $selectedRollAuto = true;
                }
            }
        }

        // Enforce product constraints (after roll fitting/rotation where applicable)
        if ($isDimensionBased && $requestedWidthIn !== null && $requestedHeightIn !== null) {
            if ($product->min_width_in !== null && $requestedWidthIn < (float) $product->min_width_in) {
                return response()->json(['message' => 'Width is below the minimum allowed.'], 422);
            }
            if ($product->max_width_in !== null && $requestedWidthIn > (float) $product->max_width_in) {
                return response()->json(['message' => 'Width exceeds the maximum allowed.'], 422);
            }
            if ($product->min_height_in !== null && $requestedHeightIn < (float) $product->min_height_in) {
                return response()->json(['message' => 'Height is below the minimum allowed.'], 422);
            }
            if ($product->max_height_in !== null && $requestedHeightIn > (float) $product->max_height_in) {
                return response()->json(['message' => 'Height exceeds the maximum allowed.'], 422);
            }
        }

        // Base price
        if ($isDimensionBased && $widthIn !== null && $heightIn !== null) {
            $rates = $rollId ? $pricing->rollRates($rp, $rollId) : $pricing->dimensionRates($rp);
            $rate = ! empty($rates['rate_per_sqft']) ? (float) $rates['rate_per_sqft'] : null;
            $offcutRate = ! empty($rates['offcut_rate_per_sqft']) ? (float) $rates['offcut_rate_per_sqft'] : null;
            $minCharge = ! empty($rates['min_charge']) ? (float) $rates['min_charge'] : null;

            if ($rate === null) {
                $baseTotal = 0.0;
            } else {
                $calc = $dimensions->calculateDimensionPrice(
                    (float) $widthIn,
                    (float) $heightIn,
                    (float) $qty,
                    (float) $rate,
                    $offcutRate,
                    $minCharge,
                    $rollWidthIn
                );
                $baseTotal = (float) ($calc['total'] ?? 0);

                $areaCharge = (float) ($calc['area_charge'] ?? 0);
                $offcutCharge = (float) ($calc['offcut_charge'] ?? 0);
                $preMin = $areaCharge + $offcutCharge;

                $breakdown[] = ['label' => 'Printing area', 'amount' => $areaCharge];
                if ($offcutCharge > 0) {
                    $breakdown[] = ['label' => 'Offcut', 'amount' => $offcutCharge];
                }
                if ($baseTotal > $preMin) {
                    $breakdown[] = ['label' => 'Min charge', 'amount' => ($baseTotal - $preMin)];
                }
            }

            $total += $baseTotal;
            if ($rate === null) {
                $breakdown[] = ['label' => 'Base', 'amount' => $baseTotal];
            }
        } else {
            $unit = $pricing->baseUnitPrice($rp, $qty);
            $baseTotal = $unit !== null ? ((float) $unit * $qty) : 0.0;
            $total += $baseTotal;
            $breakdown[] = ['label' => 'Base', 'amount' => $baseTotal];
        }

        // Variants (dependent option groups -> match a variant set combination)
        $selectedByGroup = [];
        foreach ((array) ($validated['options'] ?? []) as $gid => $oid) {
            if (! is_numeric($gid) || ! is_numeric($oid)) {
                continue;
            }
            $gid = (int) $gid;
            $oid = (int) $oid;
            if ($gid <= 0 || $oid <= 0) {
                continue;
            }
            $selectedByGroup[$gid] = $oid;
        }

        if (count($selectedByGroup) > 0) {
            // Validate option IDs belong to the claimed group IDs
            $optionIds = array_values($selectedByGroup);
            $groupByOptionId = \App\Models\Option::query()
                ->whereIn('id', $optionIds)
                ->pluck('option_group_id', 'id')
                ->all();

            foreach ($selectedByGroup as $gid => $oid) {
                if (! isset($groupByOptionId[$oid]) || (int) $groupByOptionId[$oid] !== (int) $gid) {
                    return response()->json(['message' => 'Invalid variant selection.'], 422);
                }
            }

            // Validate options are active for this product
            $activeOptionIds = \App\Models\ProductOption::query()
                ->where('product_id', $product->id)
                ->whereIn('option_id', $optionIds)
                ->where('is_active', true)
                ->whereNull('deleted_at')
                ->pluck('option_id')
                ->all();

            if (count($activeOptionIds) !== count(array_unique($optionIds))) {
                return response()->json(['message' => 'One or more selected variants are not available for this product.'], 422);
            }

            $sets = \App\Models\ProductVariantSet::query()
                ->where('product_id', $product->id)
                ->where('is_active', true)
                ->with(['items.option:id,option_group_id'])
                ->get();

            if ($sets->count() > 0) {
                $requiredGroupIds = $sets
                    ->flatMap(fn ($s) => ($s->items ?? collect())->map(fn ($it) => (int) ($it->option?->option_group_id ?: 0)))
                    ->filter(fn ($v) => $v > 0)
                    ->unique()
                    ->values()
                    ->all();

                $isComplete = count($requiredGroupIds) > 0
                    && collect($requiredGroupIds)->every(fn ($gid) => isset($selectedByGroup[(int) $gid]));

                if ($isComplete) {
                    $matchedVariantSetId = null;

                    foreach ($sets as $set) {
                        $setMap = [];
                        foreach (($set->items ?? collect()) as $it) {
                            $gid = (int) ($it->option?->option_group_id ?: 0);
                            if ($gid <= 0) {
                                continue;
                            }
                            $setMap[$gid] = (int) $it->option_id;
                        }

                        $matches = true;
                        foreach ($requiredGroupIds as $gid) {
                            $gid = (int) $gid;
                            if (! isset($setMap[$gid]) || (int) $setMap[$gid] !== (int) ($selectedByGroup[$gid] ?? 0)) {
                                $matches = false;
                                break;
                            }
                        }

                        if ($matches) {
                            $matchedVariantSetId = (int) $set->id;
                            break;
                        }
                    }

                    if ($matchedVariantSetId) {
                        $vp = $pricing->variantPricing($rp, $matchedVariantSetId);
                        if ($vp) {
                            $variantTotal = 0.0;
                            if ($vp->fixed_price !== null) {
                                $variantTotal = (float) $vp->fixed_price * $qty;
                            } elseif ($vp->rate_per_sqft !== null && $widthIn !== null && $heightIn !== null) {
                                $calc = $dimensions->calculateDimensionPrice(
                                    (float) $widthIn,
                                    (float) $heightIn,
                                    (float) $qty,
                                    (float) $vp->rate_per_sqft,
                                    $vp->offcut_rate_per_sqft !== null ? (float) $vp->offcut_rate_per_sqft : null,
                                    $vp->min_charge !== null ? (float) $vp->min_charge : null,
                                    $rollWidthIn
                                );
                                $variantTotal = (float) ($calc['total'] ?? 0);
                            }

                            if ($variantTotal > 0) {
                                $total += $variantTotal;
                                $breakdown[] = ['label' => 'Variants', 'amount' => $variantTotal];
                            }
                        }
                    }
                }
            }
        }

        // Finishings
        $finishingsInput = (array) ($validated['finishings'] ?? []);
        $finishingIds = array_values(array_unique(array_filter(array_map(
            fn ($k) => is_numeric($k) ? (int) $k : null,
            array_keys($finishingsInput)
        ))));

        if (count($finishingIds) > 0) {
            $validFinishingIds = \App\Models\ProductFinishingLink::query()
                ->where('product_id', $product->id)
                ->where('is_active', true)
                ->whereIn('finishing_product_id', $finishingIds)
                ->pluck('finishing_product_id')
                ->all();

            $finishingProductsById = \App\Models\Product::query()
                ->whereIn('id', $validFinishingIds)
                ->get(['id', 'status', 'visibility', 'product_type'])
                ->keyBy('id');

            $finishingTotal = 0.0;
            foreach ($validFinishingIds as $fid) {
                $requestedQty = $finishingsInput[(string) $fid] ?? 0;
                $requestedQty = is_numeric($requestedQty) ? (int) $requestedQty : 0;
                if ($requestedQty <= 0) {
                    continue;
                }

                $fp = $pricing->finishingPricing($rp, (int) $fid);
                $usedFallback = false;

                if ($fp) {
                    if ($fp->price_per_piece !== null) {
                        $finishingTotal += ((float) $fp->price_per_piece * $requestedQty);
                    } elseif ($fp->flat_price !== null) {
                        $finishingTotal += (float) $fp->flat_price;
                    } elseif ($fp->price_per_side !== null) {
                        $finishingTotal += ((float) $fp->price_per_side * $requestedQty);
                    } else {
                        $usedFallback = true;
                    }
                } else {
                    $usedFallback = true;
                }

                if ($usedFallback) {
                    $finishingProduct = $finishingProductsById->get((int) $fid);
                    if (! $finishingProduct || $finishingProduct->status !== 'active') {
                        continue;
                    }

                    $frp = $pricing->resolve($finishingProduct, $wgId);
                    if (! $frp) {
                        continue;
                    }

                    $unit = $pricing->baseUnitPrice($frp, $requestedQty);
                    if ($unit === null) {
                        continue;
                    }

                    $finishingTotal += ((float) $unit * $requestedQty);
                }
            }

            if ($finishingTotal > 0) {
                $total += $finishingTotal;
                $breakdown[] = ['label' => 'Finishings', 'amount' => $finishingTotal];
            }
        }

        $response = [
            'total' => round($total, 2),
            'breakdown' => $breakdown,
        ];

        if ($isDimensionBased && $selectedRollId && $selectedRollWidthIn !== null) {
            $response['roll'] = [
                'id' => (int) $selectedRollId,
                'width_in' => (float) $selectedRollWidthIn,
                'rotated' => (bool) $selectedRollRotated,
                'auto' => (bool) $selectedRollAuto,
            ];
        }

        return response()->json($response);
    }
}

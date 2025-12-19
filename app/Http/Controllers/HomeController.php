<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Services\ActivityLogger;
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
        $categories = \App\Models\Category::query()
            ->whereNull('parent_id')
            ->where('is_active', true)
            ->where('show_in_navbar', true)
            ->select('id', 'name', 'slug', 'cover_image_path as image')
            ->orderBy('name')
            ->get();

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
            'title' => 'Privacy Policy | Printair Advertising',
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
            'title' => 'Terms & Conditions | Printair Advertising',
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
            'title' => 'About Us | Printair Advertising',
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
        
    }
}

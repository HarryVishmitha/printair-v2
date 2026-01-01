<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\ProductFinishingPricing;
use App\Models\ProductPriceTier;
use App\Models\ProductPricing;
use App\Models\ProductWorkingGroupOverride;
use App\Models\ProductRollPricing;
use App\Models\ProductVariantAvailabilityOverride;
use App\Models\ProductVariantPricing;
use App\Models\ProductVariantSet;
use App\Models\WorkingGroup;
use App\Services\Pricing\PricingResolverService;
use App\Services\Pricing\VariantAvailabilityResolverService;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class AdminPricingController extends Controller
{
    use AuthorizesRequests;
    public function __construct(
        private readonly PricingResolverService $pricingResolver,
        private readonly VariantAvailabilityResolverService $variantAvailability
    ) {
    }

    private function wantsJson(Request $request): bool
    {
        return $request->expectsJson() || $request->wantsJson();
    }

    private function jsonOk(string $message, array $data = []): \Illuminate\Http\JsonResponse
    {
        return response()->json([
            'ok' => true,
            'message' => $message,
            'data' => $data ?: null,
        ]);
    }

    private function jsonFail(string $message, int $status = 422, array $data = []): \Illuminate\Http\JsonResponse
    {
        return response()->json([
            'ok' => false,
            'message' => $message,
            'data' => $data ?: null,
        ], $status);
    }

    private function pricePillData(Product $product, ?int $wgId): array
    {
        $fresh = $product->fresh();
        $isDimensionBased = (bool) ($fresh->is_dimension_based ?? false);

        $rpPublic = $this->pricingResolver->resolve($fresh, null);
        $rpWg = $wgId ? $this->pricingResolver->resolve($fresh, $wgId) : null;

        if ($isDimensionBased) {
            $publicRate = $rpPublic ? ($this->pricingResolver->dimensionRates($rpPublic)['rate_per_sqft'] ?? null) : null;
            $wgRate = $rpWg ? ($this->pricingResolver->dimensionRates($rpWg)['rate_per_sqft'] ?? null) : null;

            return [
                'public_price' => $publicRate,
                'wg_price' => $wgRate,
                'public_price_label' => $publicRate === null ? null : ('LKR '.number_format((float) $publicRate, 4).'/sqft'),
                'wg_price_label' => $wgRate === null ? null : ('LKR '.number_format((float) $wgRate, 4).'/sqft'),
                'mode' => 'dimension_based',
            ];
        }

        $publicUnit = $rpPublic ? $this->pricingResolver->baseUnitPrice($rpPublic, 1) : null;
        $wgUnit = $rpWg ? $this->pricingResolver->baseUnitPrice($rpWg, 1) : null;

        return [
            'public_price' => $publicUnit,
            'wg_price' => $wgUnit,
            'public_price_label' => $publicUnit === null ? null : ('LKR '.number_format((float) $publicUnit, 2)),
            'wg_price_label' => $wgUnit === null ? null : ('LKR '.number_format((float) $wgUnit, 2)),
            'mode' => 'unit',
        ];
    }

    public function index(Request $request)
    {
        $this->authorize('viewAny', ProductPricing::class);

        try {
            $wgId = $request->integer('working_group_id');
            $search = trim((string) $request->get('search', ''));
            $status = $request->get('status');
            $sort = (string) $request->get('sort', 'updated_desc');

            $workingGroups = WorkingGroup::query()
                ->orderBy('name')
                ->get(['id', 'name', 'slug']);

            $selectedWorkingGroup = $wgId ? $workingGroups->firstWhere('id', $wgId) : null;

            $productsQ = Product::query()
                ->with([
                    'primaryImage:id,product_id,path,is_featured',
                    'pricings' => function ($q) use ($selectedWorkingGroup) {
                        $q->whereNull('deleted_at')
                            ->where(function ($qq) use ($selectedWorkingGroup) {
                                $qq->where(function ($a) {
                                    $a->where('context', 'public')
                                        ->whereNull('working_group_id');
                                });

                                if ($selectedWorkingGroup) {
                                    $qq->orWhere(function ($b) use ($selectedWorkingGroup) {
                                        $b->where('context', 'working_group')
                                            ->where('working_group_id', $selectedWorkingGroup->id);
                                    });
                                }
                            });
                    },
                ]);

            if ($search !== '') {
                $productsQ->where(function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                        ->orWhere('slug', 'like', "%{$search}%");
                });
            }

            if ($status) {
                $productsQ->where('status', $status);
            }

            match ($sort) {
                'name_asc' => $productsQ->orderBy('name', 'asc'),
                'name_desc' => $productsQ->orderBy('name', 'desc'),
                default => $productsQ->orderBy('updated_at', 'desc'),
            };

            $products = $productsQ->paginate(18)->withQueryString();

            $visibilityByProductId = [];
            if ($selectedWorkingGroup) {
                $visibilityByProductId = DB::table('product_working_group_overrides')
                    ->whereNull('deleted_at')
                    ->where('working_group_id', $selectedWorkingGroup->id)
                    ->pluck('is_visible', 'product_id')
                    ->all();
            }

            $cards = $products->getCollection()->map(function (Product $product) use ($selectedWorkingGroup, $visibilityByProductId) {
                $publicPricing = $product->pricings->first(fn ($row) => $row->context === 'public' && $row->working_group_id === null);

                $wgRow = null;
                if ($selectedWorkingGroup) {
                    $wgRow = $product->pricings->first(fn ($row) => $row->context === 'working_group'
                        && (int) $row->working_group_id === (int) $selectedWorkingGroup->id);
                }

                $isDimensionBased = (bool) ($product->is_dimension_based ?? false);

                $rpPublic = $publicPricing ? $this->pricingResolver->resolve($product, null) : null;
                $rpWg = $selectedWorkingGroup ? $this->pricingResolver->resolve($product, $selectedWorkingGroup) : null;

                $publicPrice = null;
                $wgPrice = null;
                $publicLabel = null;
                $wgLabel = null;

                if ($isDimensionBased) {
                    $publicPrice = $rpPublic ? ($this->pricingResolver->dimensionRates($rpPublic)['rate_per_sqft'] ?? null) : null;
                    $wgPrice = $rpWg ? ($this->pricingResolver->dimensionRates($rpWg)['rate_per_sqft'] ?? null) : null;

                    $publicLabel = $publicPrice === null ? null : ('LKR '.number_format((float) $publicPrice, 4).'/sqft');
                    $wgLabel = $wgPrice === null ? null : ('LKR '.number_format((float) $wgPrice, 4).'/sqft');
                } else {
                    $publicPrice = $rpPublic ? $this->pricingResolver->baseUnitPrice($rpPublic, 1) : null;
                    $wgPrice = $rpWg ? $this->pricingResolver->baseUnitPrice($rpWg, 1) : null;

                    $publicLabel = $publicPrice === null ? null : ('LKR '.number_format((float) $publicPrice, 2));
                    $wgLabel = $wgPrice === null ? null : ('LKR '.number_format((float) $wgPrice, 2));
                }

                $isVisible = true;
                if ($selectedWorkingGroup) {
                    $isVisible = array_key_exists($product->id, $visibilityByProductId)
                        ? (bool) $visibilityByProductId[$product->id]
                        : true;
                }

                return [
                    'id' => $product->id,
                    'name' => $product->name,
                    'slug' => $product->slug ?? null,
                    'product_type' => $product->product_type ?? null,
                    'image' => $product->primaryImage?->path ?? null,
                    'public_price' => $publicPrice,
                    'public_price_label' => $publicLabel,
                    'selected_wg_price' => $wgPrice,
                    'selected_wg_price_label' => $wgLabel,
                    'selected_wg_row_exists' => (bool) $wgRow,
                    'selected_wg_override_active' => (bool) ($wgRow?->is_active),
                    'selected_wg_is_visible' => $isVisible,
                    'manage_url' => route('admin.pricing.products.show', $product).($selectedWorkingGroup ? ('?working_group_id='.$selectedWorkingGroup->id) : ''),
                ];
            });

            return view('admin.pricing.index', [
                'workingGroups' => $workingGroups,
                'selectedWorkingGroup' => $selectedWorkingGroup,
                'products' => $products,
                'cards' => $cards,
                'filters' => [
                    'working_group_id' => $wgId,
                    'search' => $search,
                    'status' => $status,
                    'sort' => $sort,
                ],
            ]);
        } catch (\Throwable $e) {
            Log::error('AdminPricingController@index error', [
                'user_id' => Auth::id(),
                'error' => $e->getMessage(),
            ]);

            if ($this->wantsJson(request())) {
                return $this->jsonFail($e->getMessage() ?: 'Failed to load pricing dashboard.');
            }

            return back()->with('error', 'Failed to load pricing dashboard. Please try again.');
        }
    }

    public function show(Request $request, Product $product)
    {
        $this->authorize('managePricing', $product);

        try {
            $wgId = $request->integer('working_group_id');
            $tab = (string) $request->get('tab', 'base');

            $workingGroups = WorkingGroup::query()
                ->orderBy('name')
                ->get(['id', 'name', 'slug']);

            $selectedWorkingGroup = $wgId ? $workingGroups->firstWhere('id', $wgId) : null;

            $product->load([
                'primaryImage',
                'activeVariantSets',
                'finishingLinks.finishingProduct',
                'productRolls.roll',
                'publicPricing.tiers',
                'publicPricing.variantPricings',
                'publicPricing.finishingPricings',
                'publicPricing.rollPricings.roll',
            ]);

            $workingGroupPricing = null;
            if ($selectedWorkingGroup) {
                $workingGroupPricing = ProductPricing::query()
                    ->where('product_id', $product->id)
                    ->where('context', 'working_group')
                    ->where('working_group_id', $selectedWorkingGroup->id)
                    ->whereNull('deleted_at')
                    ->with(['tiers', 'variantPricings', 'finishingPricings', 'rollPricings.roll'])
                    ->first();
            }

            $visibilityOverride = null;
            if ($selectedWorkingGroup) {
                $visibilityOverride = ProductWorkingGroupOverride::query()
                    ->where('product_id', $product->id)
                    ->where('working_group_id', $selectedWorkingGroup->id)
                    ->first();
            }

            $resolved = $this->pricingResolver->resolve($product, $selectedWorkingGroup);

            $availabilityMap = [];
            if ($selectedWorkingGroup && $product->relationLoaded('activeVariantSets')) {
                $variantSetIds = $product->activeVariantSets->pluck('id')->map(fn ($id) => (int) $id)->all();
                $availabilityMap = $this->variantAvailability->enabledMap($product, $variantSetIds, $selectedWorkingGroup);
            }

            $pricePills = $this->pricePillData($product, $selectedWorkingGroup?->id);

            return view('admin.pricing.show', [
                'product' => $product,
                'tab' => in_array($tab, ['base', 'tiers', 'variants', 'finishings', 'rolls'], true) ? $tab : 'base',
                'workingGroups' => $workingGroups,
                'selectedWorkingGroup' => $selectedWorkingGroup,
                'publicPricing' => $product->publicPricing,
                'workingGroupPricing' => $workingGroupPricing,
                'wgPricing' => $workingGroupPricing,
                'resolvedPricing' => $resolved,
                'visibilityOverride' => $visibilityOverride,
                'availabilityMap' => $availabilityMap,
                'pricePills' => $pricePills,
            ]);
        } catch (\Throwable $e) {
            Log::error('AdminPricingController@show error', [
                'user_id' => Auth::id(),
                'product_id' => $product->id,
                'error' => $e->getMessage(),
            ]);

            if ($this->wantsJson(request())) {
                return $this->jsonFail($e->getMessage() ?: 'Failed to load product pricing page.');
            }

            return redirect()
                ->route('admin.pricing.index')
                ->with('error', 'Failed to load product pricing page.');
        }
    }

    public function toggleWorkingGroupVisibility(Request $request, Product $product, WorkingGroup $workingGroup)
    {
        $this->authorize('managePricing', $product);
        $this->authorize('manageWorkingGroupOverrides', ProductPricing::class);

        $data = $request->validate([
            'is_visible' => ['required', 'boolean'],
        ]);

        try {
            DB::transaction(function () use ($data, $product, $workingGroup) {
                $now = now();

                $existing = DB::table('product_working_group_overrides')
                    ->whereNull('deleted_at')
                    ->where('product_id', $product->id)
                    ->where('working_group_id', $workingGroup->id)
                    ->lockForUpdate()
                    ->first();

                if ($existing) {
                    DB::table('product_working_group_overrides')
                        ->where('id', $existing->id)
                        ->update([
                            'is_visible' => (bool) $data['is_visible'],
                            'updated_by' => Auth::user()?->id,
                            'updated_at' => $now,
                        ]);
                } else {
                    DB::table('product_working_group_overrides')->insert([
                        'product_id' => $product->id,
                        'working_group_id' => $workingGroup->id,
                        'is_visible' => (bool) $data['is_visible'],
                        'created_by' => Auth::user()?->id,
                        'updated_by' => Auth::user()?->id,
                        'created_at' => $now,
                        'updated_at' => $now,
                        'deleted_at' => null,
                    ]);
                }
            });

            if ($this->wantsJson(request())) {
                return $this->jsonOk('Working group visibility updated.', [
                    'product_id' => $product->id,
                    'working_group_id' => $workingGroup->id,
                    'is_visible' => (bool) $data['is_visible'],
                ]);
            }

            return back()->with('success', 'Working group visibility updated.');
        } catch (\Throwable $e) {
            Log::error('toggleWorkingGroupVisibility failed', [
                'product_id' => $product->id,
                'working_group_id' => $workingGroup->id,
                'error' => $e->getMessage(),
                'user_id' => Auth::user()?->id,
            ]);

            if ($this->wantsJson(request())) {
                return $this->jsonFail($e->getMessage() ?: 'Failed to update visibility.');
            }

            return back()->with('error', 'Failed to update visibility.');
        }
    }

    public function toggleWorkingGroupOverride(Request $request, Product $product, WorkingGroup $workingGroup)
    {
        $this->authorize('managePricing', $product);
        $this->authorize('manageWorkingGroupOverrides', ProductPricing::class);

        $data = $request->validate([
            'is_enabled' => ['required', 'boolean'],
        ]);

        try {
            DB::transaction(function () use ($data, $product, $workingGroup) {
                $enabled = (bool) $data['is_enabled'];

                $product->loadMissing('publicPricing');

                if ($enabled && ! $product->publicPricing) {
                    throw new \RuntimeException('Create Public Pricing first before enabling WG override.');
                }

                $row = ProductPricing::query()
                    ->where('product_id', $product->id)
                    ->where('context', 'working_group')
                    ->where('working_group_id', $workingGroup->id)
                    ->whereNull('deleted_at')
                    ->lockForUpdate()
                    ->first();

                if (! $row) {
                    $public = $product->publicPricing;

                    ProductPricing::create([
                        'product_id' => $product->id,
                        'context' => 'working_group',
                        'working_group_id' => $workingGroup->id,
                        'is_active' => $enabled,

                        'base_price' => $public?->base_price,
                        'rate_per_sqft' => $public?->rate_per_sqft,
                        'offcut_rate_per_sqft' => $public?->offcut_rate_per_sqft,
                        'min_charge' => $public?->min_charge,

                        // Enable base override by default when turning on WG override,
                        // so the resolver actually uses this WG row for base pricing.
                        'override_base' => true,
                        'override_variants' => false,
                        'override_finishings' => false,

                        'created_by' => Auth::user()?->id,
                        'updated_by' => Auth::user()?->id,
                    ]);
                } else {
                    $update = [
                        'is_active' => $enabled,
                        'updated_by' => Auth::user()?->id,
                    ];

                    // If enabling and no override flags were ever set, default base override ON.
                    if ($enabled && ! $row->override_base && ! $row->override_variants && ! $row->override_finishings) {
                        $update['override_base'] = true;
                    }

                    $row->update($update);
                }
            });

            if ($this->wantsJson(request())) {
                $row = ProductPricing::query()
                    ->where('product_id', $product->id)
                    ->where('context', 'working_group')
                    ->where('working_group_id', $workingGroup->id)
                    ->whereNull('deleted_at')
                    ->first();

                return $this->jsonOk('Working group override updated.', [
                    'product_id' => $product->id,
                    'working_group_id' => $workingGroup->id,
                    'row_exists' => (bool) $row,
                    'override_active' => (bool) ($row?->is_active),
                ]);
            }

            return back()->with('success', 'Working group override updated.');
        } catch (\Throwable $e) {
            Log::warning('toggleWorkingGroupOverride failed', [
                'product_id' => $product->id,
                'wg_id' => $workingGroup->id,
                'error' => $e->getMessage(),
                'user_id' => Auth::user()?->id,
            ]);

            if ($this->wantsJson(request())) {
                return $this->jsonFail($e->getMessage() ?: 'Failed to update WG override.');
            }

            return back()->with('error', $e->getMessage() ?: 'Failed to update WG override.');
        }
    }

    public function ensurePublicPricing(Request $request, Product $product)
    {
        $this->authorize('create', ProductPricing::class);

        try {
            $row = ProductPricing::query()
                ->where('product_id', $product->id)
                ->where('context', 'public')
                ->whereNull('working_group_id')
                ->whereNull('deleted_at')
                ->first();

            if (! $row) {
                ProductPricing::create([
                    'product_id' => $product->id,
                    'context' => 'public',
                    'working_group_id' => null,
                    'override_base' => false,
                    'override_variants' => false,
                    'override_finishings' => false,
                    'is_active' => true,
                    'created_by' => Auth::id(),
                    'updated_by' => Auth::id(),
                ]);
            }

            if ($this->wantsJson(request())) {
                return $this->jsonOk('Public pricing row is ready.');
            }

            return back()->with('success', 'Public pricing row is ready.');
        } catch (\Throwable $e) {
            Log::error('AdminPricingController@ensurePublicPricing error', [
                'user_id' => Auth::id(),
                'product_id' => $product->id,
                'error' => $e->getMessage(),
            ]);

            if ($this->wantsJson(request())) {
                return $this->jsonFail($e->getMessage() ?: 'Failed to ensure public pricing row.');
            }

            return back()->with('error', 'Failed to ensure public pricing row.');
        }
    }

    public function ensureWorkingGroupPricing(Request $request, Product $product, WorkingGroup $workingGroup)
    {
        $this->authorize('create', ProductPricing::class);

        try {
            $row = ProductPricing::query()
                ->where('product_id', $product->id)
                ->where('context', 'working_group')
                ->where('working_group_id', $workingGroup->id)
                ->whereNull('deleted_at')
                ->first();

            if (! $row) {
                ProductPricing::create([
                    'product_id' => $product->id,
                    'context' => 'working_group',
                    'working_group_id' => $workingGroup->id,
                    'override_base' => true,
                    'override_variants' => false,
                    'override_finishings' => false,
                    'is_active' => true,
                    'created_by' => Auth::id(),
                    'updated_by' => Auth::id(),
                ]);
            } else {
                $update = [
                    'is_active' => true,
                    'updated_by' => Auth::id(),
                ];

                if (! $row->override_base && ! $row->override_variants && ! $row->override_finishings) {
                    $update['override_base'] = true;
                }

                $row->update($update);
            }

            if ($this->wantsJson(request())) {
                return $this->jsonOk('Working group pricing row is ready.');
            }

            return back()->with('success', 'Working group pricing row is ready.');
        } catch (\Throwable $e) {
            Log::error('AdminPricingController@ensureWorkingGroupPricing error', [
                'user_id' => Auth::id(),
                'product_id' => $product->id,
                'working_group_id' => $workingGroup->id,
                'error' => $e->getMessage(),
            ]);

            if ($this->wantsJson(request())) {
                return $this->jsonFail($e->getMessage() ?: 'Failed to ensure working group pricing row.');
            }

            return back()->with('error', 'Failed to ensure working group pricing row.');
        }
    }

    public function upsertBasePricing(Request $request, Product $product)
    {
        $this->authorize('managePricing', $product);

        $data = $request->validate([
            'context' => ['required', 'in:public,working_group'],
            'working_group_id' => ['nullable', 'integer', 'exists:working_groups,id'],
            'is_active' => ['nullable', 'boolean'],

            'override_base' => ['nullable', 'boolean'],
            'override_variants' => ['nullable', 'boolean'],
            'override_finishings' => ['nullable', 'boolean'],

            'base_price' => ['nullable', 'numeric', 'min:0'],
            'rate_per_sqft' => ['nullable', 'numeric', 'min:0'],
            'offcut_rate_per_sqft' => ['nullable', 'numeric', 'min:0'],
            'min_charge' => ['nullable', 'numeric', 'min:0'],
        ]);

        try {
            $saved = DB::transaction(function () use ($data, $product) {
                $context = (string) $data['context'];
                $wgId = null;

                if ($context === 'working_group') {
                    $wgId = (int) ($data['working_group_id'] ?? 0);
                    if (! $wgId) {
                        throw new \RuntimeException('Missing working_group_id for working_group pricing.');
                    }
                }

                $row = ProductPricing::query()
                    ->where('product_id', $product->id)
                    ->where('context', $context)
                    ->when($context === 'public', fn ($q) => $q->whereNull('working_group_id'))
                    ->when($context === 'working_group', fn ($q) => $q->where('working_group_id', $wgId))
                    ->whereNull('deleted_at')
                    ->lockForUpdate()
                    ->first();

                $payload = [
                    'updated_by' => Auth::user()?->id,
                ];

                // Only update pricing fields that were actually sent by the client.
                // (Prevents accidentally nulling fields when UI submits partial payloads.)
                foreach (['base_price', 'rate_per_sqft', 'offcut_rate_per_sqft', 'min_charge'] as $k) {
                    if (array_key_exists($k, $data)) {
                        $payload[$k] = $data[$k];
                    }
                }

                if ($context === 'working_group') {
                    if (array_key_exists('override_base', $data)) {
                        $payload['override_base'] = (bool) $data['override_base'];
                    }
                    if (array_key_exists('override_variants', $data)) {
                        $payload['override_variants'] = (bool) $data['override_variants'];
                    }
                    if (array_key_exists('override_finishings', $data)) {
                        $payload['override_finishings'] = (bool) $data['override_finishings'];
                    }

                    // Common admin expectation: if you're editing WG base pricing fields,
                    // you intend to use WG base pricing for quoting.
                    if (!array_key_exists('override_base', $data)) {
                        $touchedBase = false;
                        foreach (['base_price', 'rate_per_sqft', 'offcut_rate_per_sqft', 'min_charge'] as $k) {
                            if (array_key_exists($k, $data)) {
                                $touchedBase = true;
                                break;
                            }
                        }
                        if ($touchedBase) {
                            $payload['override_base'] = true;
                        }
                    }
                }

                if (array_key_exists('is_active', $data)) {
                    $payload['is_active'] = (bool) $data['is_active'];
                }

                if (! $row) {
                    $this->authorize('create', ProductPricing::class);

                    $payload['product_id'] = $product->id;
                    $payload['context'] = $context;
                    $payload['working_group_id'] = $wgId;
                    $payload['created_by'] = Auth::user()?->id;
                    $payload['is_active'] = $payload['is_active'] ?? true;

                    if ($context === 'working_group') {
                        $payload['override_base'] = $payload['override_base'] ?? false;
                        $payload['override_variants'] = $payload['override_variants'] ?? false;
                        $payload['override_finishings'] = $payload['override_finishings'] ?? false;
                    }

                    ProductPricing::create($payload);
                } else {
                    $this->authorize('update', $row);
                    $row->update($payload);
                }

                $id = $row?->id;
                if (! $id) {
                    $id = ProductPricing::query()
                        ->where('product_id', $product->id)
                        ->where('context', $context)
                        ->when($context === 'public', fn ($q) => $q->whereNull('working_group_id'))
                        ->when($context === 'working_group', fn ($q) => $q->where('working_group_id', $wgId))
                        ->whereNull('deleted_at')
                        ->value('id');
                }

                return [
                    'pricing_id' => $id,
                    'context' => $context,
                    'working_group_id' => $wgId,
                ];
            });

            if ($this->wantsJson(request())) {
                $wgId = $request->integer('working_group_id') ?: ($saved['working_group_id'] ?? null);

                $publicPricingId = ProductPricing::query()
                    ->where('product_id', $product->id)
                    ->where('context', 'public')
                    ->whereNull('working_group_id')
                    ->whereNull('deleted_at')
                    ->value('id');

                $wgPricingId = null;
                if ($wgId) {
                    $wgPricingId = ProductPricing::query()
                        ->where('product_id', $product->id)
                        ->where('context', 'working_group')
                        ->where('working_group_id', $wgId)
                        ->whereNull('deleted_at')
                        ->value('id');
                }

                return $this->jsonOk('Base pricing saved.', [
                    ...$this->pricePillData($product, $wgId),
                    'public_pricing_id' => $publicPricingId,
                    'wg_pricing_id' => $wgPricingId,
                    'saved_pricing_id' => $saved['pricing_id'] ?? null,
                ]);
            }

            return back()->with('success', 'Base pricing saved.');
        } catch (\Throwable $e) {
            Log::error('AdminPricingController@upsertBasePricing failed', [
                'product_id' => $product->id,
                'error' => $e->getMessage(),
                'user_id' => Auth::user()?->id,
            ]);

            if ($this->wantsJson($request)) {
                return $this->jsonFail($e->getMessage() ?: 'Failed to save base pricing.');
            }

            return back()->with('error', $e->getMessage() ?: 'Failed to save base pricing.');
        }
    }

    public function syncTiers(Request $request, Product $product)
    {
        $this->authorize('managePricing', $product);

        $data = $request->validate([
            'product_pricing_id' => ['required', 'integer', 'exists:product_pricings,id'],
            'tiers' => ['array'],
            'tiers.*.id' => ['nullable', 'integer', 'exists:product_price_tiers,id'],
            'tiers.*.min_qty' => ['required', 'integer', 'min:1'],
            'tiers.*.max_qty' => ['nullable', 'integer', 'min:1'],
            'tiers.*.price' => ['required', 'numeric', 'min:0'],
        ]);

        try {
            DB::transaction(function () use ($data, $product) {
                $pricing = ProductPricing::query()
                    ->whereKey((int) $data['product_pricing_id'])
                    ->where('product_id', $product->id)
                    ->whereNull('deleted_at')
                    ->lockForUpdate()
                    ->firstOrFail();

                $this->authorize('update', $pricing);

                $keptTierIds = [];

                foreach (($data['tiers'] ?? []) as $t) {
                    $tierId = (int) ($t['id'] ?? 0);
                    $minQty = (int) $t['min_qty'];
                    $maxQty = array_key_exists('max_qty', $t) && $t['max_qty'] !== null ? (int) $t['max_qty'] : null;

                    if ($maxQty !== null && $maxQty < $minQty) {
                        throw new \RuntimeException('Tier max_qty must be >= min_qty.');
                    }

                    $payload = [
                        'min_qty' => $minQty,
                        'max_qty' => $maxQty,
                        'price' => $t['price'],
                        'updated_by' => Auth::user()?->id,
                    ];

                    if ($tierId) {
                        $updated = ProductPriceTier::query()
                            ->whereKey($tierId)
                            ->where('product_pricing_id', $pricing->id)
                            ->whereNull('deleted_at')
                            ->lockForUpdate()
                            ->update($payload);

                        if (! $updated) {
                            throw new \RuntimeException('Tier not found for this pricing row.');
                        }

                        $keptTierIds[] = $tierId;
                    } else {
                        $new = ProductPriceTier::create([
                            'product_pricing_id' => $pricing->id,
                            'min_qty' => $payload['min_qty'],
                            'max_qty' => $payload['max_qty'],
                            'price' => $payload['price'],
                            'created_by' => Auth::user()?->id,
                            'updated_by' => Auth::user()?->id,
                        ]);

                        $keptTierIds[] = (int) $new->id;
                    }
                }

                $keptTierIds = array_values(array_unique(array_filter($keptTierIds, fn ($id) => (int) $id > 0)));

                $tiersToDelete = ProductPriceTier::query()
                    ->where('product_pricing_id', $pricing->id)
                    ->whereNull('deleted_at')
                    ->when(! empty($keptTierIds), fn ($q) => $q->whereNotIn('id', $keptTierIds))
                    ->lockForUpdate()
                    ->get();

                foreach ($tiersToDelete as $tier) {
                    $tier->update(['updated_by' => Auth::user()?->id]);
                    $tier->delete();
                }
            });

            if ($this->wantsJson(request())) {
                $pricing = ProductPricing::query()
                    ->whereKey((int) $data['product_pricing_id'])
                    ->where('product_id', $product->id)
                    ->whereNull('deleted_at')
                    ->first();

                $wgId = $request->integer('working_group_id')
                    ?: (($pricing && $pricing->context === 'working_group') ? (int) $pricing->working_group_id : null);

                return $this->jsonOk('Tiers saved.', $this->pricePillData($product, $wgId));
            }

            return back()->with('success', 'Tiers saved.');
        } catch (\Throwable $e) {
            Log::error('AdminPricingController@syncTiers failed', [
                'product_id' => $product->id,
                'error' => $e->getMessage(),
                'user_id' => Auth::user()?->id,
            ]);

            if ($this->wantsJson($request)) {
                return $this->jsonFail($e->getMessage() ?: 'Failed to save tiers.');
            }

            return back()->with('error', $e->getMessage() ?: 'Failed to save tiers.');
        }
    }

    public function deleteTier(Request $request, Product $product, ProductPriceTier $tier)
    {
        $this->authorize('managePricing', $product);

        try {
            DB::transaction(function () use ($product, $tier) {
                $pricing = ProductPricing::query()
                    ->whereKey($tier->product_pricing_id)
                    ->where('product_id', $product->id)
                    ->whereNull('deleted_at')
                    ->lockForUpdate()
                    ->firstOrFail();

                $this->authorize('update', $pricing);

                $tier->update([
                    'updated_by' => Auth::user()?->id,
                ]);

                $tier->delete();
            });

            if ($this->wantsJson(request())) {
                return $this->jsonOk('Tier deleted.');
            }

            return back()->with('success', 'Tier deleted.');
        } catch (\Throwable $e) {
            Log::error('AdminPricingController@deleteTier failed', [
                'product_id' => $product->id,
                'tier_id' => $tier->id,
                'error' => $e->getMessage(),
                'user_id' => Auth::user()?->id,
            ]);

            if ($this->wantsJson(request())) {
                return $this->jsonFail($e->getMessage() ?: 'Failed to delete tier.');
            }

            return back()->with('error', $e->getMessage() ?: 'Failed to delete tier.');
        }
    }

    public function toggleVariantSetAvailability(Request $request, Product $product, ProductVariantSet $variantSet)
    {
        $this->authorize('managePricing', $product);

        abort_unless((int) $variantSet->product_id === (int) $product->id, 404);

        $data = $request->validate([
            'working_group_id' => ['required', 'integer', 'exists:working_groups,id'],
            'is_enabled' => ['required', 'boolean'],
        ]);

        try {
            DB::transaction(function () use ($data, $variantSet) {
                $wgId = (int) $data['working_group_id'];

                ProductVariantAvailabilityOverride::updateOrCreate(
                    [
                        'variant_set_id' => $variantSet->id,
                        'working_group_id' => $wgId,
                    ],
                    [
                        'is_enabled' => (bool) $data['is_enabled'],
                        'updated_by' => Auth::user()?->id,
                        'created_by' => Auth::user()?->id,
                    ]
                );
            });

            if ($this->wantsJson(request())) {
                return $this->jsonOk('Variant availability updated.', [
                    'variant_set_id' => $variantSet->id,
                    'working_group_id' => (int) $data['working_group_id'],
                    'is_enabled' => (bool) $data['is_enabled'],
                ]);
            }

            return back()->with('success', 'Variant availability updated.');
        } catch (\Throwable $e) {
            Log::error('AdminPricingController@toggleVariantSetAvailability failed', [
                'product_id' => $variantSet->product_id,
                'variant_set_id' => $variantSet->id,
                'error' => $e->getMessage(),
                'user_id' => Auth::user()?->id,
            ]);

            if ($this->wantsJson(request())) {
                return $this->jsonFail($e->getMessage() ?: 'Failed to update variant availability.');
            }

            return back()->with('error', $e->getMessage() ?: 'Failed to update variant availability.');
        }
    }

    public function syncVariantPricing(Request $request, Product $product)
    {
        $this->authorize('managePricing', $product);

        $data = $request->validate([
            'product_pricing_id' => ['required', 'integer', 'exists:product_pricings,id'],
            'rows' => ['array'],
            'rows.*.id' => ['nullable', 'integer'],
            'rows.*.variant_set_id' => ['required', 'integer', 'exists:product_variant_sets,id'],
            'rows.*.fixed_price' => ['nullable', 'numeric', 'min:0'],
            'rows.*.rate_per_sqft' => ['nullable', 'numeric', 'min:0'],
            'rows.*.offcut_rate_per_sqft' => ['nullable', 'numeric', 'min:0'],
            'rows.*.min_charge' => ['nullable', 'numeric', 'min:0'],
            'rows.*.is_active' => ['nullable', 'boolean'],
        ]);

        try {
            DB::transaction(function () use ($data, $product) {
                $pricing = ProductPricing::query()
                    ->whereKey((int) $data['product_pricing_id'])
                    ->where('product_id', $product->id)
                    ->whereNull('deleted_at')
                    ->lockForUpdate()
                    ->firstOrFail();

                $this->authorize('update', $pricing);

                foreach (($data['rows'] ?? []) as $r) {
                    $rowId = (int) ($r['id'] ?? 0);
                    $variantSetId = (int) $r['variant_set_id'];

                    $belongs = ProductVariantSet::query()
                        ->whereKey($variantSetId)
                        ->where('product_id', $product->id)
                        ->whereNull('deleted_at')
                        ->exists();

                    if (! $belongs) {
                        throw new \RuntimeException('Variant set does not belong to this product.');
                    }

                    $payload = [
                        'fixed_price' => $r['fixed_price'] ?? null,
                        'rate_per_sqft' => $r['rate_per_sqft'] ?? null,
                        'offcut_rate_per_sqft' => $r['offcut_rate_per_sqft'] ?? null,
                        'min_charge' => $r['min_charge'] ?? null,
                        'is_active' => array_key_exists('is_active', $r) ? (bool) $r['is_active'] : true,
                        'updated_by' => Auth::user()?->id,
                    ];

                    if ($rowId) {
                        ProductVariantPricing::query()
                            ->whereKey($rowId)
                            ->where('product_pricing_id', $pricing->id)
                            ->whereNull('deleted_at')
                            ->lockForUpdate()
                            ->update($payload);
                    } else {
                        ProductVariantPricing::create([
                            'product_pricing_id' => $pricing->id,
                            'variant_set_id' => $variantSetId,
                            'fixed_price' => $payload['fixed_price'],
                            'rate_per_sqft' => $payload['rate_per_sqft'],
                            'offcut_rate_per_sqft' => $payload['offcut_rate_per_sqft'],
                            'min_charge' => $payload['min_charge'],
                            'is_active' => $payload['is_active'],
                            'created_by' => Auth::user()?->id,
                            'updated_by' => Auth::user()?->id,
                        ]);
                    }
                }
            });

            if ($this->wantsJson(request())) {
                $pricing = ProductPricing::query()
                    ->whereKey((int) $data['product_pricing_id'])
                    ->where('product_id', $product->id)
                    ->whereNull('deleted_at')
                    ->first();

                $wgId = $request->integer('working_group_id')
                    ?: (($pricing && $pricing->context === 'working_group') ? (int) $pricing->working_group_id : null);

                return $this->jsonOk('Variant pricing saved.', $this->pricePillData($product, $wgId));
            }

            return back()->with('success', 'Variant pricing saved.');
        } catch (\Throwable $e) {
            Log::error('AdminPricingController@syncVariantPricing failed', [
                'product_id' => $product->id,
                'error' => $e->getMessage(),
                'user_id' => Auth::user()?->id,
            ]);

            if ($this->wantsJson(request())) {
                return $this->jsonFail($e->getMessage() ?: 'Failed to save variant pricing.');
            }

            return back()->with('error', $e->getMessage() ?: 'Failed to save variant pricing.');
        }
    }

    public function syncFinishingPricing(Request $request, Product $product)
    {
        $this->authorize('managePricing', $product);

        $data = $request->validate([
            'product_pricing_id' => ['required', 'integer', 'exists:product_pricings,id'],
            'rows' => ['array'],
            'rows.*.id' => ['nullable', 'integer'],
            'rows.*.finishing_product_id' => ['required', 'integer', 'exists:products,id'],
            'rows.*.price_per_piece' => ['nullable', 'numeric', 'min:0'],
            'rows.*.price_per_side' => ['nullable', 'numeric', 'min:0'],
            'rows.*.flat_price' => ['nullable', 'numeric', 'min:0'],
            'rows.*.min_qty' => ['nullable', 'integer', 'min:1'],
            'rows.*.max_qty' => ['nullable', 'integer', 'min:1'],
            'rows.*.is_active' => ['nullable', 'boolean'],
        ]);

        try {
            DB::transaction(function () use ($data, $product) {
                $pricing = ProductPricing::query()
                    ->whereKey((int) $data['product_pricing_id'])
                    ->where('product_id', $product->id)
                    ->whereNull('deleted_at')
                    ->lockForUpdate()
                    ->firstOrFail();

                $this->authorize('update', $pricing);

                foreach (($data['rows'] ?? []) as $r) {
                    $id = (int) ($r['id'] ?? 0);
                    $payload = [
                        'price_per_piece' => $r['price_per_piece'] ?? null,
                        'price_per_side' => $r['price_per_side'] ?? null,
                        'flat_price' => $r['flat_price'] ?? null,
                        'min_qty' => $r['min_qty'] ?? null,
                        'max_qty' => $r['max_qty'] ?? null,
                        'is_active' => array_key_exists('is_active', $r) ? (bool) $r['is_active'] : true,
                        'updated_by' => Auth::user()?->id,
                    ];

                    if ($id) {
                        ProductFinishingPricing::query()
                            ->whereKey($id)
                            ->where('product_pricing_id', $pricing->id)
                            ->whereNull('deleted_at')
                            ->lockForUpdate()
                            ->update($payload);
                    } else {
                        ProductFinishingPricing::create([
                            'product_pricing_id' => $pricing->id,
                            'finishing_product_id' => (int) $r['finishing_product_id'],
                            'price_per_piece' => $payload['price_per_piece'],
                            'price_per_side' => $payload['price_per_side'],
                            'flat_price' => $payload['flat_price'],
                            'min_qty' => $payload['min_qty'],
                            'max_qty' => $payload['max_qty'],
                            'is_active' => $payload['is_active'],
                            'created_by' => Auth::user()?->id,
                            'updated_by' => Auth::user()?->id,
                        ]);
                    }
                }
            });

            if ($this->wantsJson(request())) {
                $pricing = ProductPricing::query()
                    ->whereKey((int) $data['product_pricing_id'])
                    ->where('product_id', $product->id)
                    ->whereNull('deleted_at')
                    ->first();

                $wgId = $request->integer('working_group_id')
                    ?: (($pricing && $pricing->context === 'working_group') ? (int) $pricing->working_group_id : null);

                return $this->jsonOk('Finishing pricing saved.', $this->pricePillData($product, $wgId));
            }

            return back()->with('success', 'Finishing pricing saved.');
        } catch (\Throwable $e) {
            Log::error('AdminPricingController@syncFinishingPricing failed', [
                'product_id' => $product->id,
                'error' => $e->getMessage(),
                'user_id' => Auth::user()?->id,
            ]);

            if ($this->wantsJson(request())) {
                return $this->jsonFail($e->getMessage() ?: 'Failed to save finishing pricing.');
            }

            return back()->with('error', $e->getMessage() ?: 'Failed to save finishing pricing.');
        }
    }

    public function deleteFinishingPricing(Request $request, Product $product, ProductFinishingPricing $finishingPricing)
    {
        $this->authorize('managePricing', $product);

        try {
            DB::transaction(function () use ($product, $finishingPricing) {
                $pricing = ProductPricing::query()
                    ->whereKey($finishingPricing->product_pricing_id)
                    ->where('product_id', $product->id)
                    ->whereNull('deleted_at')
                    ->lockForUpdate()
                    ->firstOrFail();

                $this->authorize('update', $pricing);

                $finishingPricing->update([
                    'updated_by' => Auth::user()?->id,
                ]);

                $finishingPricing->delete();
            });

            if ($this->wantsJson(request())) {
                return $this->jsonOk('Finishing pricing row deleted.');
            }

            return back()->with('success', 'Finishing pricing row deleted.');
        } catch (\Throwable $e) {
            Log::error('AdminPricingController@deleteFinishingPricing failed', [
                'product_id' => $product->id,
                'finishing_pricing_id' => $finishingPricing->id,
                'error' => $e->getMessage(),
                'user_id' => Auth::user()?->id,
            ]);

            if ($this->wantsJson(request())) {
                return $this->jsonFail($e->getMessage() ?: 'Failed to delete finishing pricing row.');
            }

            return back()->with('error', $e->getMessage() ?: 'Failed to delete finishing pricing row.');
        }
    }

    public function syncRollPricing(Request $request, Product $product)
    {
        $this->authorize('managePricing', $product);

        $data = $request->validate([
            'product_pricing_id' => ['required', 'integer', 'exists:product_pricings,id'],
            'rows' => ['array'],
            'rows.*.id' => ['nullable', 'integer'],
            'rows.*.roll_id' => ['required', 'integer', 'exists:rolls,id'],
            'rows.*.rate_per_sqft' => ['nullable', 'numeric', 'min:0'],
            'rows.*.offcut_rate_per_sqft' => ['nullable', 'numeric', 'min:0'],
            'rows.*.min_charge' => ['nullable', 'numeric', 'min:0'],
            'rows.*.is_active' => ['nullable', 'boolean'],
        ]);

        try {
            DB::transaction(function () use ($data, $product) {
                $pricing = ProductPricing::query()
                    ->whereKey((int) $data['product_pricing_id'])
                    ->where('product_id', $product->id)
                    ->whereNull('deleted_at')
                    ->lockForUpdate()
                    ->firstOrFail();

                $this->authorize('update', $pricing);

                foreach (($data['rows'] ?? []) as $r) {
                    $id = (int) ($r['id'] ?? 0);
                    $payload = [
                        'roll_id' => (int) $r['roll_id'],
                        'rate_per_sqft' => $r['rate_per_sqft'] ?? null,
                        'offcut_rate_per_sqft' => $r['offcut_rate_per_sqft'] ?? null,
                        'min_charge' => $r['min_charge'] ?? null,
                        'is_active' => array_key_exists('is_active', $r) ? (bool) $r['is_active'] : true,
                        'updated_by' => Auth::user()?->id,
                    ];

                    if ($id) {
                        ProductRollPricing::query()
                            ->whereKey($id)
                            ->where('product_pricing_id', $pricing->id)
                            ->whereNull('deleted_at')
                            ->lockForUpdate()
                            ->update($payload);
                    } else {
                        ProductRollPricing::create([
                            'product_pricing_id' => $pricing->id,
                            'product_id' => $product->id,
                            'roll_id' => $payload['roll_id'],
                            'rate_per_sqft' => $payload['rate_per_sqft'],
                            'offcut_rate_per_sqft' => $payload['offcut_rate_per_sqft'],
                            'min_charge' => $payload['min_charge'],
                            'is_active' => $payload['is_active'],
                            'created_by' => Auth::user()?->id,
                            'updated_by' => Auth::user()?->id,
                        ]);
                    }
                }
            });

            if ($this->wantsJson(request())) {
                $pricing = ProductPricing::query()
                    ->whereKey((int) $data['product_pricing_id'])
                    ->where('product_id', $product->id)
                    ->whereNull('deleted_at')
                    ->first();

                $wgId = $request->integer('working_group_id')
                    ?: (($pricing && $pricing->context === 'working_group') ? (int) $pricing->working_group_id : null);

                return $this->jsonOk('Roll overrides saved.', $this->pricePillData($product, $wgId));
            }

            return back()->with('success', 'Roll overrides saved.');
        } catch (\Throwable $e) {
            Log::error('AdminPricingController@syncRollPricing failed', [
                'product_id' => $product->id,
                'error' => $e->getMessage(),
                'user_id' => Auth::user()?->id,
            ]);

            if ($this->wantsJson(request())) {
                return $this->jsonFail($e->getMessage() ?: 'Failed to save roll overrides.');
            }

            return back()->with('error', $e->getMessage() ?: 'Failed to save roll overrides.');
        }
    }
}

<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreProductRequest;
use App\Http\Requests\UpdateProductRequest;
use App\Models\Category;
use App\Models\Option;
use App\Models\OptionGroup;
use App\Models\Product;
use App\Models\ProductFile;
use App\Models\ProductFinishingLink;
use App\Models\ProductImage;
use App\Models\ProductOption;
use App\Models\ProductOptionGroup;
use App\Models\ProductRoll;
use App\Models\ProductPricing;
use App\Models\ProductVariantPricing;
use App\Models\ProductVariantSet;
use App\Models\ProductVariantSetItem;
use App\Models\Roll;
use App\Services\ProductMediaService;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use App\Support\ProductWizardCache;
use Illuminate\Filesystem\FilesystemAdapter;

class ProductController extends Controller
{
    use AuthorizesRequests;

    public function updateStatus(Request $request, Product $product)
    {
        $this->authorize('update', $product);

        $validated = $request->validate([
            'status' => ['required', 'in:active,inactive,draft'],
        ]);

        $product->update([
            'status' => $validated['status'],
            'updated_by' => Auth::user()?->id,
        ]);

        return response()->json([
            'ok' => true,
            'status' => $product->status,
        ]);
    }

    public function updateVisibility(Request $request, Product $product)
    {
        $this->authorize('update', $product);

        $validated = $request->validate([
            'visibility' => ['required', 'in:public,internal'],
        ]);

        $product->update([
            'visibility' => $validated['visibility'],
            'updated_by' => Auth::user()?->id,
        ]);

        return response()->json([
            'ok' => true,
            'visibility' => $product->visibility,
        ]);
    }

    public function destroy(Product $product)
    {
        $this->authorize('delete', $product);

        try {
            $isReferencedAsFinishing = ProductFinishingLink::query()
                ->where('finishing_product_id', $product->id)
                ->whereNull('deleted_at')
                ->exists();

            if ($isReferencedAsFinishing) {
                return redirect()
                    ->route('admin.products.index')
                    ->with('error', 'This product is linked as a finishing on other products, so it cannot be deleted.');
            }

            DB::transaction(function () use ($product) {
                $product->update([
                    'updated_by' => Auth::user()?->id,
                ]);

                $product->delete();
            });

            return redirect()
                ->route('admin.products.index')
                ->with('success', 'Product deleted successfully.');
        } catch (\Throwable $e) {
            Log::error('Product delete failed', [
                'user_id' => Auth::user()?->id,
                'product_id' => $product->id,
                'error' => $e->getMessage(),
            ]);

            return redirect()
                ->route('admin.products.index')
                ->with('error', 'Unable to delete product. Please try again.');
        }
    }

    private function syncVariantsInPlace(Product $product, array $payload, ?ProductPricing $pricingRow): void
    {
        $optionsInput = $payload['options'] ?? [];
        $variantsInput = $payload['variants'] ?? [];

        if (! is_array($optionsInput) || ! is_array($variantsInput)) {
            throw ValidationException::withMessages([
                'variants_payload' => 'Invalid variants payload structure.',
            ]);
        }

        if (count($optionsInput) > 8) {
            throw ValidationException::withMessages([
                'variants_payload' => 'Too many option groups. Keep it within 8 groups.',
            ]);
        }

        if (count($variantsInput) > 800) {
            throw ValidationException::withMessages([
                'variants_payload' => 'Too many variants generated. Reduce options or disable extra combinations.',
            ]);
        }

        $userId = Auth::user()?->id;

        $seenOptionGroupIds = [];
        $seenOptionIds = [];
        $seenVariantSetIds = [];

        // Map: ["groupSlug:valueSlug" => option_id] based on DB codes
        $optionIdByKey = [];

        foreach ($optionsInput as $groupIndex => $g) {
            if (! is_array($g)) {
                continue;
            }

            $incomingGroupId = (isset($g['id']) && is_numeric($g['id'])) ? (int) $g['id'] : null;
            $groupName = trim((string) ($g['name'] ?? ''));

            if ($groupName === '') {
                throw ValidationException::withMessages([
                    'variants_payload' => 'Option group name cannot be empty.',
                ]);
            }

            $optionGroup = null;
            if ($incomingGroupId) {
                $optionGroup = OptionGroup::query()->find($incomingGroupId);
                if (! $optionGroup) {
                    throw ValidationException::withMessages([
                        'variants_payload' => 'One or more option groups are invalid. Please reload and try again.',
                    ]);
                }

                if ($optionGroup->name !== $groupName) {
                    $optionGroup->name = $groupName;
                    $optionGroup->save();
                }
            } else {
                $groupSlug = trim((string) ($g['slug'] ?? $groupName));
                $groupCode = Str::slug($groupSlug);

                if ($groupCode === '') {
                    throw ValidationException::withMessages([
                        'variants_payload' => 'Option group slug cannot be empty.',
                    ]);
                }

                $optionGroup = OptionGroup::query()->firstOrCreate(
                    ['code' => $groupCode],
                    ['name' => $groupName, 'description' => null]
                );

                if ($optionGroup->name !== $groupName) {
                    $optionGroup->name = $groupName;
                    $optionGroup->save();
                }
            }

            $seenOptionGroupIds[] = $optionGroup->id;

            $pog = ProductOptionGroup::withTrashed()->firstOrNew([
                'product_id' => $product->id,
                'option_group_id' => $optionGroup->id,
            ]);

            if ($pog->trashed()) {
                $pog->restore();
            }

            $pog->is_required = true;
            $pog->sort_index = (int) ($g['sort_order'] ?? ($groupIndex + 1));
            $pog->updated_by = $userId;
            if (! $pog->exists) {
                $pog->created_by = $userId;
            }
            $pog->save();

            $values = $g['values'] ?? [];
            if (! is_array($values)) {
                $values = [];
            }

            $valueCount = 0;

            foreach ($values as $valueIndex => $v) {
                if (! is_array($v)) {
                    continue;
                }

                $valueCount++;
                if ($valueCount > 80) {
                    throw ValidationException::withMessages([
                        'variants_payload' => 'Too many values in a group. Keep it within 80 values per group.',
                    ]);
                }

                $incomingOptionId = (isset($v['id']) && is_numeric($v['id'])) ? (int) $v['id'] : null;
                $label = trim((string) ($v['label'] ?? ''));
                if ($label === '') {
                    throw ValidationException::withMessages([
                        'variants_payload' => 'Variant option label cannot be empty.',
                    ]);
                }

                $option = null;
                if ($incomingOptionId) {
                    $option = Option::query()->find($incomingOptionId);
                    if (! $option || (int) $option->option_group_id !== (int) $optionGroup->id) {
                        throw ValidationException::withMessages([
                            'variants_payload' => 'One or more options are invalid. Please reload and try again.',
                        ]);
                    }

                    if ($option->label !== $label) {
                        $option->label = $label;
                        $option->save();
                    }
                } else {
                    $valueSlug = trim((string) ($v['slug'] ?? $label));
                    $valueCode = Str::slug($valueSlug);
                    if ($valueCode === '') {
                        throw ValidationException::withMessages([
                            'variants_payload' => 'Variant option slug cannot be empty.',
                        ]);
                    }

                    $option = Option::query()->firstOrCreate(
                        ['option_group_id' => $optionGroup->id, 'code' => $valueCode],
                        ['label' => $label, 'meta' => null]
                    );

                    if ($option->label !== $label) {
                        $option->label = $label;
                        $option->save();
                    }
                }

                $seenOptionIds[] = $option->id;

                $po = ProductOption::withTrashed()->firstOrNew([
                    'product_id' => $product->id,
                    'option_id' => $option->id,
                ]);

                if ($po->trashed()) {
                    $po->restore();
                }

                $po->is_active = array_key_exists('is_active', $v) ? (bool) $v['is_active'] : true;
                $po->sort_index = (int) ($v['sort_order'] ?? ($valueIndex + 1));
                $po->updated_by = $userId;
                if (! $po->exists) {
                    $po->created_by = $userId;
                }
                $po->save();

                $groupSlug = $optionGroup->code;
                $valueSlug = $option->code;
                $optionIdByKey["{$groupSlug}:{$valueSlug}"] = $option->id;
            }
        }

        // Soft-delete pivots that are no longer present
        ProductOptionGroup::query()
            ->where('product_id', $product->id)
            ->whereNull('deleted_at')
            ->whereNotIn('option_group_id', $seenOptionGroupIds ?: [0])
            ->delete();

        ProductOption::query()
            ->where('product_id', $product->id)
            ->whereNull('deleted_at')
            ->whereNotIn('option_id', $seenOptionIds ?: [0])
            ->delete();

        foreach ($variantsInput as $variantIndex => $vr) {
            if (! is_array($vr)) {
                continue;
            }

            $variantId = (isset($vr['id']) && is_numeric($vr['id'])) ? (int) $vr['id'] : null;
            $variantKey = trim((string) ($vr['key'] ?? ''));
            $selections = $vr['selections'] ?? [];

            if ($variantKey === '' || ! is_array($selections) || count($selections) === 0) {
                continue;
            }

            $optionIds = [];
            foreach ($selections as $s) {
                if (! is_array($s)) {
                    continue;
                }
                $gSlug = trim((string) ($s['group_slug'] ?? ''));
                $vSlug = trim((string) ($s['value_slug'] ?? ''));
                if ($gSlug === '' || $vSlug === '') {
                    continue;
                }

                $optId = $optionIdByKey["{$gSlug}:{$vSlug}"] ?? null;
                if (! $optId) {
                    throw ValidationException::withMessages([
                        'variants_payload' => "Variant selection not found: {$gSlug}:{$vSlug}. Rebuild variants and try again.",
                    ]);
                }
                $optionIds[] = (int) $optId;
            }

            $optionIds = array_values(array_unique($optionIds));
            if (count($optionIds) === 0) {
                continue;
            }

            $set = null;

            if ($variantId) {
                $set = ProductVariantSet::query()
                    ->where('product_id', $product->id)
                    ->where('id', $variantId)
                    ->first();
            }

            if (! $set) {
                $set = ProductVariantSet::withTrashed()->firstOrNew([
                    'product_id' => $product->id,
                    'code' => $variantKey,
                ]);

                if ($set->trashed()) {
                    $set->restore();
                }
            }

            $set->code = $variantKey;
            $set->is_active = array_key_exists('enabled', $vr) ? (bool) $vr['enabled'] : true;
            $set->updated_by = $userId;
            if (! $set->exists) {
                $set->created_by = $userId;
            }
            $set->save();

            $seenVariantSetIds[] = $set->id;

            ProductVariantSetItem::query()
                ->where('variant_set_id', $set->id)
                ->delete();

            foreach ($optionIds as $optId) {
                ProductVariantSetItem::query()->create([
                    'variant_set_id' => $set->id,
                    'option_id' => $optId,
                ]);
            }

            if ($pricingRow instanceof ProductPricing) {
                $price = $vr['price'] ?? null;
                $hasPrice = $price !== null && $price !== '' && is_numeric($price);

                if ($hasPrice) {
                    ProductVariantPricing::query()->updateOrCreate(
                        ['product_pricing_id' => $pricingRow->id, 'variant_set_id' => $set->id],
                        [
                            'fixed_price' => (float) $price,
                            'rate_per_sqft' => null,
                            'offcut_rate_per_sqft' => null,
                            'min_charge' => null,
                            'is_active' => true,
                            'updated_by' => $userId,
                            'created_by' => $userId,
                        ]
                    );
                } else {
                    ProductVariantPricing::query()
                        ->where('product_pricing_id', $pricingRow->id)
                        ->where('variant_set_id', $set->id)
                        ->delete();
                }
            }
        }

        // Soft-disable variant sets not present anymore
        ProductVariantSet::query()
            ->where('product_id', $product->id)
            ->whereNotIn('id', $seenVariantSetIds ?: [0])
            ->update(['is_active' => false, 'updated_by' => $userId]);

        if ($pricingRow instanceof ProductPricing) {
            ProductVariantPricing::query()
                ->where('product_pricing_id', $pricingRow->id)
                ->whereNotIn('variant_set_id', $seenVariantSetIds ?: [0])
                ->delete();
        }
    }

    public function index(Request $request)
    {
        try {
            $this->authorize('viewAny', Product::class);

            $q = trim((string) $request->get('q', ''));
            $status = $request->get('status');      // active|inactive|draft|null
            $type = $request->get('type');        // standard|dimension_based|finishing|service|null
            $visibility = $request->get('visibility');  // public|internal|null
            $categoryId = $request->get('category_id'); // int|null

            $products = Product::query()
                ->with(['category:id,name'])
                ->when($q !== '', function ($query) use ($q) {
                    $query->where(function ($qq) use ($q) {
                        $qq->where('name', 'like', "%{$q}%")
                            ->orWhere('product_code', 'like', "%{$q}%")
                            ->orWhere('slug', 'like', "%{$q}%");
                    });
                })
                ->when($status, fn ($query) => $query->where('status', $status))
                ->when($type, fn ($query) => $query->where('product_type', $type))
                ->when($visibility, fn ($query) => $query->where('visibility', $visibility))
                ->when($categoryId, fn ($query) => $query->where('category_id', $categoryId))
                ->orderByDesc('updated_at')
                ->paginate(20)
                ->withQueryString();

            $categories = Category::query()
                ->select('id', 'name')
                ->where('is_active', 1)
                ->orderBy('name')
                ->get();

            return view('admin.products.index', [
                'products' => $products,
                'categories' => $categories,
                'filters' => [
                    'q' => $q,
                    'status' => $status,
                    'type' => $type,
                    'visibility' => $visibility,
                    'category_id' => $categoryId,
                ],
            ]);
        } catch (\Throwable $e) {
            Log::error('Admin ProductController@index error', [
                'user_id' => Auth::user()?->id,
                'error' => $e->getMessage(),
            ]);

            return back()->with('error', 'Something went wrong while loading products. Please try again.');
        }
    }

    public function create()
    {
        try {
            $this->authorize('create', Product::class);

            $categories = Category::query()
                ->select('id', 'name')
                ->where('is_active', 1)
                ->orderBy('name')
                ->get();

            $rolls = Roll::query()
                ->where('is_active', true)
                ->orderBy('material_type')
                ->orderBy('width_in')
                ->orderBy('name')
                ->get();

            $finishings = Product::query()
                ->where('product_type', 'finishing')
                ->where('visibility', 'internal')
                ->where('status', 'active')
                ->orderBy('name')
                ->get(['id', 'name', 'slug', 'product_code']);

            return view('admin.products.create', [
                'categories' => $categories,
                'rolls' => $rolls,
                'finishings' => $finishings,
                'defaults' => [
                    'status' => 'active',
                    'visibility' => 'public',
                    'product_type' => 'standard',
                    'requires_dimensions' => false,
                    'allow_custom_size' => false,
                    'allow_predefined_sizes' => false,
                    'allow_rotation_to_fit_roll' => true,
                    'allow_manual_pricing' => false,
                ],
            ]);
        } catch (\Throwable $e) {
            Log::error('Admin ProductController@create error', [
                'user_id' => Auth::user()?->id,
                'error' => $e->getMessage(),
            ]);

            return redirect()->route('admin.products.index')
                ->with('error', 'Unable to open product creation page. Please try again.');
        }
    }

    public function edit(Product $product)
    {
        try {
            $this->authorize('update', $product);

            $categories = Category::query()
                ->select('id', 'name')
                ->where('is_active', 1)
                ->orderBy('name')
                ->get();

            $rolls = Roll::query()
                ->where('is_active', true)
                ->orderBy('material_type')
                ->orderBy('width_in')
                ->orderBy('name')
                ->get();

            $finishings = Product::query()
                ->where('product_type', 'finishing')
                ->where('visibility', 'internal')
                ->where('status', 'active')
                ->orderBy('name')
                ->get(['id', 'name', 'slug', 'product_code']);

            $product->load(['category:id,name', 'seo', 'images', 'files']);

            $publicPricing = $product->pricings()
                ->where('context', 'public')
                ->whereNull('working_group_id')
                ->where('is_active', 1)
                ->latest('id')
                ->first();

            // Build variants payload for the wizard (with existing DB IDs)
            $productOptionGroups = ProductOptionGroup::query()
                ->with(['optionGroup:id,code,name'])
                ->where('product_id', $product->id)
                ->whereNull('deleted_at')
                ->orderBy('sort_index')
                ->get();

            $productOptions = ProductOption::query()
                ->with(['option:id,option_group_id,code,label'])
                ->where('product_id', $product->id)
                ->whereNull('deleted_at')
                ->orderBy('sort_index')
                ->get();

            $productOptionsByGroupId = $productOptions
                ->filter(fn ($po) => $po->option !== null)
                ->groupBy(fn ($po) => (int) $po->option->option_group_id);

            $optionGroupsPayload = $productOptionGroups
                ->filter(fn ($pog) => $pog->optionGroup !== null)
                ->values()
                ->map(function ($pog, $i) use ($productOptionsByGroupId) {
                    $og = $pog->optionGroup;

                    $values = ($productOptionsByGroupId[(int) $og->id] ?? collect())
                        ->values()
                        ->map(function ($po, $j) {
                            $opt = $po->option;

                            return [
                                'id' => $opt->id,
                                'label' => $opt->label,
                                'slug' => $opt->code,
                                'sort_order' => (int) ($po->sort_index ?? ($j + 1)),
                                'is_active' => (bool) ($po->is_active ?? true),
                            ];
                        })
                        ->all();

                    return [
                        'id' => $og->id,
                        'name' => $og->name,
                        'slug' => $og->code,
                        'sort_order' => (int) ($pog->sort_index ?? ($i + 1)),
                        'is_active' => true,
                        'values' => $values,
                    ];
                })
                ->all();

            $variantPriceBySetId = collect();
            if ($publicPricing) {
                $variantPriceBySetId = $publicPricing->variantPricings()
                    ->whereNull('deleted_at')
                    ->get()
                    ->keyBy('variant_set_id');
            }

            $variantSets = ProductVariantSet::query()
                ->with(['items.option.group'])
                ->where('product_id', $product->id)
                ->whereNull('deleted_at')
                ->get();

            $variantsPayload = $variantSets
                ->map(function ($set) use ($variantPriceBySetId) {
                    $selections = collect($set->items ?? [])
                        ->map(function ($item) {
                            $opt = $item->option;
                            $grp = $opt?->group;

                            if (! $opt || ! $grp) {
                                return null;
                            }

                            return [
                                'group_name' => $grp->name,
                                'group_slug' => $grp->code,
                                'value_label' => $opt->label,
                                'value_slug' => $opt->code,
                            ];
                        })
                        ->filter()
                        ->sortBy('group_slug')
                        ->values()
                        ->all();

                    $key = collect($selections)
                        ->map(fn ($s) => "{$s['group_slug']}:{$s['value_slug']}")
                        ->implode('|');

                    $priceRow = $variantPriceBySetId->get($set->id);

                    return [
                        'id' => $set->id,
                        'key' => $set->code ?: $key,
                        'enabled' => (bool) ($set->is_active ?? true),
                        'price' => $priceRow?->fixed_price ?? '',
                        'selections' => $selections,
                    ];
                })
                ->values()
                ->all();

            $selectedRollIds = ProductRoll::query()
                ->where('product_id', $product->id)
                ->whereNull('deleted_at')
                ->pluck('roll_id')
                ->map(fn ($v) => (string) $v)
                ->values()
                ->all();

            $finishingLinks = ProductFinishingLink::query()
                ->where('product_id', $product->id)
                ->whereNull('deleted_at')
                ->get();

            $selectedFinishingIds = $finishingLinks
                ->pluck('finishing_product_id')
                ->map(fn ($v) => (string) $v)
                ->values()
                ->all();

            $finishingConfig = $finishingLinks
                ->keyBy(fn ($l) => (string) $l->finishing_product_id)
                ->map(fn ($l) => [
                    'pricing_mode' => $l->pricing_mode ?? 'per_piece',
                    'min_qty' => $l->min_qty,
                    'max_qty' => $l->max_qty,
                    'is_required' => (bool) ($l->is_required ?? false),
                    'sort_index' => $l->sort_index ?? 0,
                ])
                ->all();

            $payload = [
                'id' => $product->id,
                'category_id' => $product->category_id,
                'name' => $product->name,
                'slug' => $product->slug,
                'product_code' => $product->product_code,
                'short_description' => $product->short_description,
                'long_description' => Schema::hasColumn('products', 'long_description')
                    ? ($product->long_description ?? null)
                    : ($product->description ?? null),
                'description' => Schema::hasColumn('products', 'long_description')
                    ? ($product->long_description ?? null)
                    : ($product->description ?? null),
                'product_type' => $product->product_type,
                'visibility' => $product->visibility,
                'status' => $product->status,

                'min_qty' => $product->min_qty,
                'max_qty' => $product->max_qty ?? null,

                'requires_dimensions' => (bool) $product->requires_dimensions,
                'allow_custom_size' => (bool) $product->allow_custom_size,
                'allow_predefined_sizes' => (bool) $product->allow_predefined_sizes,
                'allow_rotation_to_fit_roll' => (bool) $product->allow_rotation_to_fit_roll,

                'min_width_in' => $product->min_width_in,
                'max_width_in' => $product->max_width_in,
                'min_height_in' => $product->min_height_in,
                'max_height_in' => $product->max_height_in,
                'roll_max_width_in' => $product->roll_max_width_in,

                'allow_manual_pricing' => (bool) ($product->allow_manual_pricing ?? false),

                'pricing' => $publicPricing ? [
                    'min_qty' => $product->min_qty,
                    'rate_per_sqft' => $publicPricing->rate_per_sqft,
                    'offcut_per_sqft' => $publicPricing->offcut_rate_per_sqft,
                    'base_price' => $publicPricing->base_price,
                ] : null,

                'seo' => $product->seo ? [
                    'seo_title' => $product->seo->seo_title,
                    'seo_description' => $product->seo->seo_description,
                    'seo_keywords' => $product->seo->seo_keywords,
                    'og_title' => $product->seo->og_title,
                    'og_description' => $product->seo->og_description,
                    'canonical_url' => $product->seo->canonical_url,
                    'is_indexable' => (int) ($product->seo->is_indexable ? 1 : 0),
                    'og_image_path' => $product->seo->og_image_path,
                ] : null,

                'roll_ids' => $selectedRollIds,
                'finishing_ids' => $selectedFinishingIds,
                'finishing_config' => $finishingConfig,

                'variants_payload' => [
                    'options' => $optionGroupsPayload,
                    'variants' => $variantsPayload,
                ],

                'existing_images' => $product->images
                    ? $product->images->map(fn ($img) => [
                        'id' => $img->id,
                        'url' => Storage::url($img->path),
                        'path' => $img->path,
                        'is_featured' => (bool) ($img->is_featured ?? false),
                        'sort_index' => (int) ($img->sort_index ?? 0),
                        'alt_text' => $img->alt_text ?? null,
                    ])->values()->all()
                    : [],

                'existing_files' => $product->files
                    ? $product->files->map(fn ($f) => [
                        'id' => $f->id,
                        'label' => $f->label,
                        'visibility' => $f->visibility,
                        'file_type' => $f->file_type,
                        'url' => Storage::url($f->file_path),
                        'path' => $f->file_path,
                    ])->values()->all()
                    : [],
            ];

            return view('admin.products.edit', [
                'product' => $product,
                'categories' => $categories,
                'rolls' => $rolls,
                'finishings' => $finishings,
                'productPayload' => $payload,
            ]);
        } catch (\Throwable $e) {
            Log::error('Admin ProductController@edit error', [
                'user_id' => Auth::user()?->id,
                'product_id' => $product->id ?? null,
                'error' => $e->getMessage(),
            ]);

            return redirect()->route('admin.products.index')
                ->with('error', 'Unable to open product edit page. Please try again.');
        }
    }

    public function update(UpdateProductRequest $request, Product $product)
    {
        $this->authorize('update', $product);

        try {
            $data = $request->validated();

            $slugSource = trim((string) ($data['slug'] ?? ''));
            if ($slugSource === '') {
                $slugSource = trim((string) ($data['name'] ?? ''));
            }
            $data['slug'] = Str::slug($slugSource);

            $descriptionValue = $data['long_description'] ?? ($data['description'] ?? null);
            $descriptionColumn = Schema::hasColumn('products', 'long_description') ? 'long_description' : 'description';

            DB::beginTransaction();

            $product->update([
                'category_id' => $data['category_id'],
                'product_code' => $data['product_code'] ?? $product->product_code,
                'name' => $data['name'],
                'slug' => $data['slug'],
                'short_description' => $data['short_description'] ?? null,
                $descriptionColumn => $descriptionValue,
                'product_type' => $data['product_type'],
                'visibility' => $data['visibility'],
                'status' => $data['status'],

                'min_qty' => $data['min_qty'] ?? null,
                'max_qty' => $data['max_qty'] ?? null,

                'requires_dimensions' => (bool) ($data['requires_dimensions'] ?? false),
                'allow_custom_size' => (bool) ($data['allow_custom_size'] ?? false),
                'allow_predefined_sizes' => (bool) ($data['allow_predefined_sizes'] ?? false),
                'allow_rotation_to_fit_roll' => (bool) ($data['allow_rotation_to_fit_roll'] ?? false),

                'min_width_in' => $data['min_width_in'] ?? null,
                'max_width_in' => $data['max_width_in'] ?? null,
                'min_height_in' => $data['min_height_in'] ?? null,
                'max_height_in' => $data['max_height_in'] ?? null,
                'roll_max_width_in' => $data['roll_max_width_in'] ?? null,

                'allow_manual_pricing' => (bool) ($data['allow_manual_pricing'] ?? false),

                'meta' => $data['meta'] ?? $product->meta,

                'updated_by' => Auth::user()?->id,
            ]);

            // ==========================
            // STEP 2: PUBLIC BASE PRICING (UPDATE)
            // ==========================
            $pricingRow = $product->pricings()
                ->where('context', 'public')
                ->whereNull('working_group_id')
                ->where('is_active', 1)
                ->latest('id')
                ->first();

            try {
                $pricingValidated = validator($request->all(), [
                    'pricing' => ['nullable', 'array'],
                    'pricing.min_qty' => ['nullable', 'integer', 'min:1'],
                    'pricing.allow_tiers' => ['nullable', 'in:0,1'],
                    'pricing.rate_per_sqft' => ['nullable', 'numeric', 'min:0'],
                    'pricing.offcut_per_sqft' => ['nullable', 'numeric', 'min:0'],
                    'pricing.base_price' => ['nullable', 'numeric', 'min:0'],
                ])->validate();

                $pricingInput = $pricingValidated['pricing'] ?? [];

                $basePrice = $pricingInput['base_price'] ?? null;
                $ratePerSqft = $pricingInput['rate_per_sqft'] ?? null;
                $offcutRatePerSqft = $pricingInput['offcut_per_sqft'] ?? null;

                $hasBase = $basePrice !== null && $basePrice !== '';
                $hasRate = $ratePerSqft !== null && $ratePerSqft !== '';
                $hasOffcut = $offcutRatePerSqft !== null && $offcutRatePerSqft !== '';

                if ($hasBase || $hasRate || $hasOffcut) {
                    $payload = [
                        'context' => 'public',
                        'working_group_id' => null,
                        'override_base' => (bool) ($pricingRow?->override_base ?? false),
                        'override_variants' => (bool) ($pricingRow?->override_variants ?? false),
                        'override_finishings' => (bool) ($pricingRow?->override_finishings ?? false),
                        'base_price' => $hasBase ? (float) $basePrice : null,
                        'rate_per_sqft' => $hasRate ? (float) $ratePerSqft : null,
                        'offcut_rate_per_sqft' => $hasOffcut ? (float) $offcutRatePerSqft : null,
                        'min_charge' => $pricingRow?->min_charge,
                        'is_active' => true,
                        'updated_by' => Auth::user()?->id,
                    ];

                    if ($pricingRow) {
                        $pricingRow->update($payload);
                    } else {
                        $payload['created_by'] = Auth::user()?->id;
                        $pricingRow = $product->pricings()->create($payload);
                    }
                }

                if (! empty($pricingInput['min_qty'])) {
                    $product->min_qty = (int) $pricingInput['min_qty'];
                    $product->updated_by = Auth::user()?->id;
                    $product->save();
                }
            } catch (ValidationException $e) {
                throw $e;
            } catch (\Throwable $e) {
                Log::error('Product update Step 2 (public pricing) failed', [
                    'product_id' => $product->id ?? null,
                    'user_id' => Auth::user()?->id,
                    'error' => $e->getMessage(),
                ]);

                throw ValidationException::withMessages([
                    'pricing.base_price' => 'Could not save base pricing. Please check values and try again.',
                ]);
            }

            // ==========================
            // STEP 3: OPTIONS + VARIANTS (ONLY IF PROVIDED)
            // ==========================
            try {
                $raw = $request->input('variants_payload');

                // If no payload => keep existing variants as-is.
                if ($raw !== null && $raw !== '') {
                    $payload = json_decode($raw, true);

                    if (! is_array($payload) || json_last_error() !== JSON_ERROR_NONE) {
                        throw ValidationException::withMessages([
                            'variants_payload' => 'Invalid variants payload. Please rebuild variants and try again.',
                        ]);
                    }

                    $this->syncVariantsInPlace($product, $payload, $pricingRow);
                }
            } catch (ValidationException $e) {
                throw $e;
            } catch (\Throwable $e) {
                Log::error('Product update Step 3 failed', [
                    'product_id' => $product->id ?? null,
                    'user_id' => Auth::user()?->id,
                    'error' => $e->getMessage(),
                ]);

                throw ValidationException::withMessages([
                    'variants_payload' => 'Could not save options & variants. Please try again. If it keeps happening, contact admin.',
                ]);
            }

            // ==========================
            // STEP 4: ROLLS (BINDING)
            // ==========================
            try {
                if ($product->product_type === 'dimension_based') {
                    $rollIds = $request->input('roll_ids', []);
                    if (! is_array($rollIds)) {
                        $rollIds = [];
                    }

                    $rollIds = array_values(array_unique(array_filter($rollIds, fn ($v) => is_numeric($v))));

                    if (count($rollIds) === 0) {
                        throw ValidationException::withMessages([
                            'roll_ids' => 'Select at least one roll for dimension-based products.',
                        ]);
                    }

                    $activeCount = Roll::query()
                        ->whereIn('id', $rollIds)
                        ->where('is_active', true)
                        ->count();

                    if ($activeCount !== count($rollIds)) {
                        throw ValidationException::withMessages([
                            'roll_ids' => 'One or more selected rolls are invalid or inactive. Please re-check and try again.',
                        ]);
                    }

                    ProductRoll::query()->where('product_id', $product->id)->delete();

                    $userId = Auth::user()?->id;
                    $rows = array_map(fn ($id) => [
                        'product_id' => $product->id,
                        'roll_id' => (int) $id,
                        'is_active' => true,
                        'created_by' => $userId,
                        'updated_by' => $userId,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ], $rollIds);

                    foreach (array_chunk($rows, 500) as $chunk) {
                        ProductRoll::query()->insert($chunk);
                    }
                } else {
                    ProductRoll::query()->where('product_id', $product->id)->delete();
                }
            } catch (ValidationException $e) {
                throw $e;
            } catch (\Throwable $e) {
                Log::error('Product update Step 4 (roll binding) failed', [
                    'product_id' => $product->id ?? null,
                    'user_id' => Auth::user()?->id,
                    'error' => $e->getMessage(),
                ]);

                throw ValidationException::withMessages([
                    'roll_ids' => 'Could not save roll bindings. Please try again.',
                ]);
            }

            // ==========================
            // STEP 5: FINISHINGS (RULES)
            // ==========================
            try {
                $finishingIds = $request->input('finishings', []);
                if (! is_array($finishingIds)) {
                    $finishingIds = [];
                }

                $finishingIds = array_values(array_unique(array_filter($finishingIds, fn ($v) => is_numeric($v))));

                $config = $request->input('finishing_config', []);
                if (! is_array($config)) {
                    $config = [];
                }

                $userId = Auth::user()?->id;

                $existingByFinishingId = ProductFinishingLink::withTrashed()
                    ->where('product_id', $product->id)
                    ->get()
                    ->keyBy(fn ($l) => (int) $l->finishing_product_id);

                if (count($finishingIds) > 0) {
                    $validIds = Product::query()
                        ->whereIn('id', $finishingIds)
                        ->where('product_type', 'finishing')
                        ->where('visibility', 'internal')
                        ->pluck('id')
                        ->all();

                    if (count($validIds) !== count($finishingIds)) {
                        throw ValidationException::withMessages([
                            'finishings' => 'One or more selected finishings are invalid. Please select only internal finishing products.',
                        ]);
                    }

                    foreach ($finishingIds as $idx => $finishingId) {
                        $finishingId = (int) $finishingId;
                        $c = $config[$finishingId] ?? [];

                        $allowedModes = ['per_piece', 'per_side', 'flat'];
                        $mode = (string) ($c['pricing_mode'] ?? 'per_piece');
                        if (! in_array($mode, $allowedModes, true)) {
                            throw ValidationException::withMessages([
                                "finishing_config.{$finishingId}.pricing_mode" => 'Invalid pricing mode.',
                            ]);
                        }

                        $min = $c['min_qty'] ?? null;
                        $max = $c['max_qty'] ?? null;

                        $min = is_numeric($min) ? (int) $min : null;
                        $max = is_numeric($max) ? (int) $max : null;

                        if ($min !== null && $min < 0) {
                            throw ValidationException::withMessages([
                                "finishing_config.{$finishingId}.min_qty" => 'Min qty must be 0 or more.',
                            ]);
                        }

                        if ($max !== null && $max < 0) {
                            throw ValidationException::withMessages([
                                "finishing_config.{$finishingId}.max_qty" => 'Max qty must be 0 or more.',
                            ]);
                        }

                        if ($min !== null && $max !== null && $max < $min) {
                            throw ValidationException::withMessages([
                                "finishing_config.{$finishingId}.max_qty" => 'Max qty must be greater than or equal to Min qty.',
                            ]);
                        }

                        $isRequired = (bool) ($c['is_required'] ?? false);
                        $sortIndex = is_numeric($c['sort_index'] ?? null) ? (int) $c['sort_index'] : (int) $idx;

                        $link = $existingByFinishingId->get($finishingId);
                        if ($link) {
                            if (method_exists($link, 'trashed') && $link->trashed()) {
                                $link->restore();
                            }

                            $link->fill([
                                'pricing_mode' => $mode,
                                'min_qty' => $min,
                                'max_qty' => $max,
                                'is_required' => $isRequired,
                                'sort_index' => $sortIndex,
                                'is_active' => true,
                                'updated_by' => $userId,
                            ]);
                            if (! $link->created_by) {
                                $link->created_by = $userId;
                            }
                            $link->save();
                        } else {
                            ProductFinishingLink::query()->create([
                                'product_id' => $product->id,
                                'finishing_product_id' => $finishingId,
                                'pricing_mode' => $mode,
                                'min_qty' => $min,
                                'max_qty' => $max,
                                'is_required' => $isRequired,
                                'sort_index' => $sortIndex,
                                'is_active' => true,
                                'created_by' => $userId,
                                'updated_by' => $userId,
                            ]);
                        }
                    }
                }

                // Soft-disable anything not present anymore (ID-stable; avoids unique constraint collisions).
                $selectedMap = array_flip($finishingIds);
                foreach ($existingByFinishingId as $fid => $link) {
                    if (isset($selectedMap[$fid])) {
                        continue;
                    }

                    $link->is_active = false;
                    $link->updated_by = $userId;
                    $link->save();

                    if (method_exists($link, 'trashed') && ! $link->trashed()) {
                        $link->delete();
                    }
                }
            } catch (ValidationException $e) {
                throw $e;
            } catch (\Throwable $e) {
                Log::error('Product update Step 5 (finishings) failed', [
                    'product_id' => $product->id ?? null,
                    'user_id' => Auth::user()?->id,
                    'error' => $e->getMessage(),
                ]);

                throw ValidationException::withMessages([
                    'finishings' => 'Could not save finishings. Please try again.',
                ]);
            }

            // ==========================
            // STEP 6: MEDIA (IMAGES + ATTACHMENTS)
            // ==========================
            try {
                $validated = validator($request->all(), [
                    'images' => ['nullable', 'array', 'max:12'],
                    'images.*' => ['file', 'mimes:jpg,jpeg,png,webp', 'max:10240'],
                    'primary_image_index' => ['nullable', 'integer', 'min:0', 'max:11'],
                    'image_sort' => ['nullable', 'array'],
                    'image_sort.*' => ['nullable', 'integer', 'min:0', 'max:9999'],

                    'attachments' => ['nullable', 'array', 'max:12'],
                    'attachments.*' => ['file', 'mimes:pdf,ai,psd,eps,svg,jpg,jpeg,png,zip,rar', 'max:25600'],
                    'attachment_sort' => ['nullable', 'array'],
                    'attachment_sort.*' => ['nullable', 'integer', 'min:0', 'max:9999'],
                ])->validate();

                $images = $request->file('images', []);
                $attachments = $request->file('attachments', []);

                $primaryIndex = $validated['primary_image_index'] ?? null;
                $imageSort = $validated['image_sort'] ?? [];
                $attachmentSort = $validated['attachment_sort'] ?? [];

                app(ProductMediaService::class)->storeMediaForProduct(
                    $product,
                    $images,
                    $primaryIndex,
                    $imageSort,
                    $attachments,
                    $attachmentSort
                );
            } catch (ValidationException $e) {
                throw $e;
            } catch (\Throwable $e) {
                Log::error('Product update Step 6 (media) failed', [
                    'product_id' => $product->id ?? null,
                    'user_id' => Auth::user()?->id,
                    'error' => $e->getMessage(),
                ]);

                throw ValidationException::withMessages([
                    'images' => 'Could not upload media. Please try again.',
                ]);
            }

            // ==========================
            // STEP 7: SEO
            // ==========================
            try {
                $seoValidated = validator($request->all(), [
                    'seo' => ['nullable', 'array'],
                    'seo.seo_title' => ['nullable', 'string', 'max:160'],
                    'seo.seo_description' => ['nullable', 'string', 'max:255'],
                    'seo.seo_keywords' => ['nullable', 'string', 'max:255'],
                    'seo.og_title' => ['nullable', 'string', 'max:160'],
                    'seo.og_description' => ['nullable', 'string', 'max:255'],
                    'seo.canonical_url' => ['nullable', 'url', 'max:500'],
                    'seo.is_indexable' => ['nullable', 'in:0,1'],
                    'seo.og_image' => ['nullable', 'file', 'mimes:jpg,jpeg,png,webp', 'max:10240'],
                ])->validate();

                $seoInput = $seoValidated['seo'] ?? [];
                $userId = Auth::user()?->id;

                $ogImagePath = null;
                if ($request->hasFile('seo.og_image')) {
                    $ogFile = $request->file('seo.og_image');
                    $ogImagePath = $ogFile->storePublicly("products/{$product->id}/seo", 'public');
                }

                $seoTitle = $seoInput['seo_title'] ?? null;
                if ($seoTitle === null || $seoTitle === '') {
                    $seoTitle = $product->name;
                }

                $seoDescription = $seoInput['seo_description'] ?? null;
                if (($seoDescription === null || $seoDescription === '') && $product->short_description) {
                    $seoDescription = $product->short_description;
                }

                $ogTitle = $seoInput['og_title'] ?? null;
                if ($ogTitle === null || $ogTitle === '') {
                    $ogTitle = $seoTitle;
                }

                $ogDescription = $seoInput['og_description'] ?? null;
                if ($ogDescription === null || $ogDescription === '') {
                    $ogDescription = $seoDescription;
                }

                $isIndexable = array_key_exists('is_indexable', $seoInput)
                    ? (bool) ((int) $seoInput['is_indexable'])
                    : true;

                $payload = [
                    'seo_title' => $seoTitle,
                    'seo_description' => $seoDescription,
                    'seo_keywords' => $seoInput['seo_keywords'] ?? null,
                    'og_title' => $ogTitle,
                    'og_description' => $ogDescription,
                    'canonical_url' => $seoInput['canonical_url'] ?? null,
                    'is_indexable' => $isIndexable,
                    'updated_by' => $userId,
                ];

                if ($ogImagePath) {
                    $payload['og_image_path'] = $ogImagePath;
                }

                $existingSeo = $product->seo;
                if ($existingSeo) {
                    $existingSeo->fill($payload);
                    if (! $existingSeo->created_by) {
                        $existingSeo->created_by = $userId;
                    }
                    $existingSeo->save();
                } else {
                    $payload['created_by'] = $userId;
                    $product->seo()->create($payload);
                }
            } catch (ValidationException $e) {
                throw $e;
            } catch (\Throwable $e) {
                Log::error('Product update Step 7 (SEO) failed', [
                    'product_id' => $product->id ?? null,
                    'user_id' => Auth::user()?->id,
                    'error' => $e->getMessage(),
                ]);

                throw ValidationException::withMessages([
                    'seo.seo_title' => 'Could not save SEO. Please try again.',
                ]);
            }

            DB::commit();

            return redirect()
                ->route('admin.products.edit', $product)
                ->with('success', 'Product updated successfully.');
        } catch (ValidationException $e) {
            DB::rollBack();
            throw $e;
        } catch (\Throwable $e) {
            DB::rollBack();

            Log::error('Admin ProductController@update error', [
                'user_id' => Auth::user()?->id,
                'product_id' => $product->id ?? null,
                'error' => $e->getMessage(),
            ]);

            return redirect()
                ->route('admin.products.edit', $product)
                ->with('error', 'Unable to update product. Please try again.');
        }
    }

    public function store(StoreProductRequest $request)
    {
        try {
            $this->authorize('create', Product::class);

            $data = $request->validated();

            // Slug safety: if UI sends empty/duplicate, ensure it's normalized.
            $data['slug'] = Str::slug($data['slug'] ?? $data['name'] ?? '');

            $descriptionValue = $data['long_description'] ?? ($data['description'] ?? null);
            $descriptionColumn = Schema::hasColumn('products', 'long_description') ? 'long_description' : 'description';

            DB::beginTransaction();

            /** @var \App\Models\Product $product */
            $product = Product::create([
                'category_id' => $data['category_id'],
                'product_code' => $data['product_code'],
                'name' => $data['name'],
                'slug' => $data['slug'],
                'short_description' => $data['short_description'] ?? null,
                $descriptionColumn => $descriptionValue,

                'product_type' => $data['product_type'],
                'visibility' => $data['visibility'],
                'status' => $data['status'],

                'min_qty' => $data['min_qty'] ?? null,
                'max_qty' => $data['max_qty'] ?? null,

                'requires_dimensions' => (bool) ($data['requires_dimensions'] ?? false),
                'allow_custom_size' => (bool) ($data['allow_custom_size'] ?? false),
                'allow_predefined_sizes' => (bool) ($data['allow_predefined_sizes'] ?? false),
                'allow_rotation_to_fit_roll' => (bool) ($data['allow_rotation_to_fit_roll'] ?? false),

                'min_width_in' => $data['min_width_in'] ?? null,
                'max_width_in' => $data['max_width_in'] ?? null,
                'min_height_in' => $data['min_height_in'] ?? null,
                'max_height_in' => $data['max_height_in'] ?? null,
                'roll_max_width_in' => $data['roll_max_width_in'] ?? null,

                'allow_manual_pricing' => (bool) ($data['allow_manual_pricing'] ?? false),

                'meta' => $data['meta'] ?? null,

                'created_by' => Auth::user()?->id,
                'updated_by' => Auth::user()?->id,
            ]);

            // ==========================
            // STEP 2: PUBLIC BASE PRICING (INITIAL)
            // ==========================
            $pricingRow = null;

            try {
                $pricingValidated = validator($request->all(), [
                    'pricing' => ['nullable', 'array'],
                    'pricing.min_qty' => ['nullable', 'integer', 'min:1'],
                    'pricing.allow_tiers' => ['nullable', 'in:0,1'],
                    'pricing.rate_per_sqft' => ['nullable', 'numeric', 'min:0'],
                    'pricing.offcut_per_sqft' => ['nullable', 'numeric', 'min:0'],
                    'pricing.base_price' => ['nullable', 'numeric', 'min:0'],
                ])->validate();

                $pricingInput = $pricingValidated['pricing'] ?? [];

                $basePrice = $pricingInput['base_price'] ?? null;
                $ratePerSqft = $pricingInput['rate_per_sqft'] ?? null;
                $offcutRatePerSqft = $pricingInput['offcut_per_sqft'] ?? null;

                $hasBase = $basePrice !== null && $basePrice !== '';
                $hasRate = $ratePerSqft !== null && $ratePerSqft !== '';
                $hasOffcut = $offcutRatePerSqft !== null && $offcutRatePerSqft !== '';

                if ($hasBase || $hasRate || $hasOffcut) {
                    $pricingRow = $product->pricings()->create([
                        'context' => 'public',
                        'override_base' => false,
                        'override_variants' => false,
                        'override_finishings' => false,
                        'base_price' => $hasBase ? (float) $basePrice : null,
                        'rate_per_sqft' => $hasRate ? (float) $ratePerSqft : null,
                        'offcut_rate_per_sqft' => $hasOffcut ? (float) $offcutRatePerSqft : null,
                        'min_charge' => null,
                        'is_active' => true,
                        'created_by' => Auth::user()?->id,
                        'updated_by' => Auth::user()?->id,
                    ]);
                }

                if (! empty($pricingInput['min_qty'])) {
                    $product->min_qty = (int) $pricingInput['min_qty'];
                    $product->save();
                }
            } catch (ValidationException $e) {
                throw $e;
            } catch (\Throwable $e) {
                Log::error('Product store Step 2 (public pricing) failed', [
                    'product_id' => $product->id ?? null,
                    'user_id' => Auth::user()?->id,
                    'error' => $e->getMessage(),
                ]);

                throw ValidationException::withMessages([
                    'pricing.base_price' => 'Could not save base pricing. Please check values and try again.',
                ]);
            }

            // ==========================
            // STEP 3: OPTIONS + VARIANTS
            // ==========================
            try {
                $raw = $request->input('variants_payload');

                // If no payload => treat as "no variants" (valid for products without variants)
                if ($raw !== null && $raw !== '') {

                    $payload = json_decode($raw, true);

                    if (! is_array($payload) || json_last_error() !== JSON_ERROR_NONE) {
                        throw ValidationException::withMessages([
                            'variants_payload' => 'Invalid variants payload. Please rebuild variants and try again.',
                        ]);
                    }

                    $optionsPayload = $payload['options'] ?? [];
                    $variantsPayload = $payload['variants'] ?? [];

                    // Basic shape validation (strict + safe)
                    if (! is_array($optionsPayload) || ! is_array($variantsPayload)) {
                        throw ValidationException::withMessages([
                            'variants_payload' => 'Invalid variants payload structure.',
                        ]);
                    }

                    // Guardrails (avoid accidental huge payloads)
                    if (count($optionsPayload) > 8) {
                        throw ValidationException::withMessages([
                            'variants_payload' => 'Too many option groups. Keep it within 8 groups.',
                        ]);
                    }
                    if (count($variantsPayload) > 800) {
                        throw ValidationException::withMessages([
                            'variants_payload' => 'Too many variants generated. Reduce options or disable extra combinations.',
                        ]);
                    }

                    // Clean existing product option/variant records (idempotent store)
                    // We do NOT delete global option_groups/options (library).
                    ProductVariantSet::where('product_id', $product->id)->delete();
                    ProductOption::where('product_id', $product->id)->delete();
                    ProductOptionGroup::where('product_id', $product->id)->delete();

                    $userId = Auth::user()?->id;

                    // Map: [group_slug => option_group_id]
                    $groupIdBySlug = [];

                    // Map: ["groupSlug:valueSlug" => option_id]
                    $optionIdByKey = [];

                    // Map: [variant_key => variant_set_id] for later pricing
                    $variantSetIdByKey = [];

                    foreach ($optionsPayload as $gi => $g) {
                        $groupName = trim((string) ($g['name'] ?? ''));
                        $groupSlug = trim((string) ($g['slug'] ?? ''));
                        $values = $g['values'] ?? [];

                        if ($groupName === '' || $groupSlug === '' || ! is_array($values) || count($values) === 0) {
                            continue; // ignore empty groups
                        }

                        // OptionGroup is global library (unique code + unique name)
                        // We use code = slug (stable)
                        $optionGroup = OptionGroup::query()->firstOrCreate(
                            ['code' => $groupSlug],
                            ['name' => $groupName, 'description' => null]
                        );

                        // Keep name fresh (admin might rename)
                        if ($optionGroup->name !== $groupName) {
                            $optionGroup->name = $groupName;
                            $optionGroup->save();
                        }

                        $groupIdBySlug[$groupSlug] = $optionGroup->id;

                        // Link group to product
                        ProductOptionGroup::query()->create([
                            'product_id' => $product->id,
                            'option_group_id' => $optionGroup->id,
                            'is_required' => true,
                            'sort_index' => (int) $gi,
                            'created_by' => $userId,
                            'updated_by' => $userId,
                        ]);

                        // Create/reuse options (values) inside that group
                        $valueCount = 0;

                        foreach ($values as $vi => $v) {
                            $label = trim((string) ($v['label'] ?? ''));
                            $slug = trim((string) ($v['slug'] ?? ''));

                            if ($label === '' || $slug === '') {
                                continue;
                            }

                            $valueCount++;
                            if ($valueCount > 80) {
                                throw ValidationException::withMessages([
                                    'variants_payload' => 'Too many values in a group. Keep it within 80 values per group.',
                                ]);
                            }

                            $opt = Option::query()->firstOrCreate(
                                ['option_group_id' => $optionGroup->id, 'code' => $slug],
                                ['label' => $label, 'meta' => null]
                            );

                            // Keep label fresh
                            if ($opt->label !== $label) {
                                $opt->label = $label;
                                $opt->save();
                            }

                            // Link option to product
                            ProductOption::query()->firstOrCreate(
                                ['product_id' => $product->id, 'option_id' => $opt->id],
                                [
                                    'is_active' => true,
                                    'sort_index' => (int) $vi,
                                    'created_by' => $userId,
                                    'updated_by' => $userId,
                                ]
                            );

                            $optionIdByKey["{$groupSlug}:{$slug}"] = $opt->id;
                        }
                    }

                    // Now create enabled variants as product_variant_sets (+ items)
                    foreach ($variantsPayload as $variantIndex => $vr) {
                        $variantKey = trim((string) ($vr['key'] ?? ''));
                        $label = trim((string) ($vr['label'] ?? ''));
                        $selections = $vr['selections'] ?? [];

                        if ($variantKey === '' || ! is_array($selections) || count($selections) === 0) {
                            continue;
                        }

                        // Create the variant set
                        $set = ProductVariantSet::query()->create([
                            'product_id' => $product->id,
                            'code' => mb_substr($variantKey, 0, 80), // column is 80
                            'is_active' => true,
                            'created_by' => $userId,
                            'updated_by' => $userId,
                        ]);

                        $variantSetIdByKey[$variantKey] = $set->id;

                        // Add items (option IDs)
                        $usedOptionIds = [];

                        foreach ($selections as $s) {
                            $gSlug = trim((string) ($s['group_slug'] ?? ''));
                            $vSlug = trim((string) ($s['value_slug'] ?? ''));

                            if ($gSlug === '' || $vSlug === '') {
                                continue;
                            }

                            $optId = $optionIdByKey["{$gSlug}:{$vSlug}"] ?? null;
                            if (! $optId) {
                                // If payload references missing option, fail fast (data mismatch)
                                throw ValidationException::withMessages([
                                    'variants_payload' => "Variant selection not found: {$gSlug}:{$vSlug}. Rebuild variants and try again.",
                                ]);
                            }

                            $usedOptionIds[] = $optId;
                        }

                        // Remove accidental duplicates inside same variant
                        $usedOptionIds = array_values(array_unique($usedOptionIds));

                        foreach ($usedOptionIds as $optId) {
                            ProductVariantSetItem::query()->create([
                                'variant_set_id' => $set->id,
                                'option_id' => $optId,
                            ]);
                        }
                    }

                    // Attach public variant pricing (optional) if we created a base pricing row
                    if ($pricingRow instanceof ProductPricing) {
                        foreach ($variantsPayload as $vr) {
                            $variantKey = trim((string) ($vr['key'] ?? ''));
                            $price = $vr['price'] ?? null;

                            if ($variantKey === '' || $price === null || $price === '') {
                                continue;
                            }

                            $setId = $variantSetIdByKey[$variantKey] ?? null;
                            if (! $setId) {
                                continue;
                            }

                            $pricingRow->variantPricings()->create([
                                'variant_set_id' => $setId,
                                'fixed_price' => (float) $price,
                                'rate_per_sqft' => null,
                                'offcut_rate_per_sqft' => null,
                                'min_charge' => null,
                                'is_active' => true,
                                'created_by' => $userId,
                                'updated_by' => $userId,
                            ]);
                        }
                    }

                    // Optional: store something helpful into product->meta so UI can rebuild Step 3 on edit if needed.
                    $meta = is_array($product->meta) ? $product->meta : (json_decode($product->meta ?? '[]', true) ?: []);
                    $meta['variants_payload'] = [
                        'options_count' => count($optionsPayload),
                        'variants_count' => count($variantsPayload),
                        'stored_at' => now()->toDateTimeString(),
                    ];
                    $product->meta = $meta;
                    $product->updated_by = $userId;
                    $product->save();
                }

            } catch (ValidationException $e) {
                // Re-throw so Laravel redirects back with validation errors
                throw $e;
            } catch (\Throwable $e) {
                Log::error('Product store Step 3 failed', [
                    'product_id' => $product->id ?? null,
                    'user_id' => Auth::user()?->id,
                    'error' => $e->getMessage(),
                ]);

                throw ValidationException::withMessages([
                    'variants_payload' => 'Could not save options & variants. Please try again. If it keeps happening, contact admin.',
                ]);
            }

            // ==========================
            // STEP 4: ROLLS (BINDING)
            // ==========================
            try {
                // Only for dimension_based products
                if ($product->product_type === 'dimension_based') {

                    $rollIds = $request->input('roll_ids', []);
                    if (! is_array($rollIds)) {
                        $rollIds = [];
                    }

                    // Validate IDs are numeric-ish
                    $rollIds = array_values(array_unique(array_filter($rollIds, fn ($v) => is_numeric($v))));

                    // Optional: enforce at least 1 roll for dimension products
                    if (count($rollIds) === 0) {
                        throw ValidationException::withMessages([
                            'roll_ids' => 'Select at least one roll for dimension-based products.',
                        ]);
                    }

                    // Ensure all rolls exist AND are active
                    $activeCount = Roll::query()
                        ->whereIn('id', $rollIds)
                        ->where('is_active', true)
                        ->count();

                    if ($activeCount !== count($rollIds)) {
                        throw ValidationException::withMessages([
                            'roll_ids' => 'One or more selected rolls are invalid or inactive. Please re-check and try again.',
                        ]);
                    }

                    // Idempotent sync: clear then insert
                    ProductRoll::query()->where('product_id', $product->id)->delete();

                    $userId = Auth::user()?->id;

                    $rows = array_map(fn ($id) => [
                        'product_id' => $product->id,
                        'roll_id' => (int) $id,
                        'is_active' => true,
                        'created_by' => $userId,
                        'updated_by' => $userId,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ], $rollIds);

                    // Chunk insert to be safe
                    foreach (array_chunk($rows, 500) as $chunk) {
                        ProductRoll::query()->insert($chunk);
                    }

                } else {
                    // If not dimension_based, ensure no leftovers (safety)
                    ProductRoll::query()->where('product_id', $product->id)->delete();
                }

            } catch (ValidationException $e) {
                throw $e;
            } catch (\Throwable $e) {
                Log::error('Product store Step 4 (roll binding) failed', [
                    'product_id' => $product->id ?? null,
                    'user_id' => Auth::user()?->id,
                    'error' => $e->getMessage(),
                ]);

                throw ValidationException::withMessages([
                    'roll_ids' => 'Could not save roll bindings. Please try again.',
                ]);
            }

            // ==========================
            // STEP 5: FINISHINGS (RULES)
            // ==========================
            try {
                $finishingIds = $request->input('finishings', []);
                if (! is_array($finishingIds)) {
                    $finishingIds = [];
                }

                $finishingIds = array_values(array_unique(array_filter($finishingIds, fn ($v) => is_numeric($v))));

                $config = $request->input('finishing_config', []);
                if (! is_array($config)) {
                    $config = [];
                }

                // Soft-delete existing links (because the model uses SoftDeletes)
                ProductFinishingLink::query()
                    ->where('product_id', $product->id)
                    ->delete();

                // If none selected, we're done.
                if (count($finishingIds) === 0) {
                    // nothing
                } else {
                    // Validate selected finishings are INTERNAL products
                    $validIds = Product::query()
                        ->whereIn('id', $finishingIds)
                        ->where('product_type', 'finishing')
                        ->where('visibility', 'internal')
                        ->pluck('id')
                        ->all();

                    if (count($validIds) !== count($finishingIds)) {
                        throw ValidationException::withMessages([
                            'finishings' => 'One or more selected finishings are invalid. Please select only internal finishing products.',
                        ]);
                    }

                    $allowedModes = ['per_piece', 'per_side', 'flat'];
                    $userId = Auth::user()?->id;

                    foreach ($finishingIds as $idx => $finishingId) {
                        $c = $config[$finishingId] ?? [];

                        $mode = (string) ($c['pricing_mode'] ?? 'per_piece');
                        if (! in_array($mode, $allowedModes, true)) {
                            throw ValidationException::withMessages([
                                "finishing_config.{$finishingId}.pricing_mode" => 'Invalid pricing mode.',
                            ]);
                        }

                        $min = $c['min_qty'] ?? null;
                        $max = $c['max_qty'] ?? null;

                        $min = is_numeric($min) ? (int) $min : null;
                        $max = is_numeric($max) ? (int) $max : null;

                        if ($min !== null && $min < 0) {
                            throw ValidationException::withMessages([
                                "finishing_config.{$finishingId}.min_qty" => 'Min qty must be 0 or more.',
                            ]);
                        }
                        if ($max !== null && $max < 0) {
                            throw ValidationException::withMessages([
                                "finishing_config.{$finishingId}.max_qty" => 'Max qty must be 0 or more.',
                            ]);
                        }
                        if ($min !== null && $max !== null && $min > $max) {
                            throw ValidationException::withMessages([
                                "finishing_config.{$finishingId}.max_qty" => 'Max qty must be greater than or equal to Min qty.',
                            ]);
                        }

                        $isRequired = (bool) ($c['is_required'] ?? false);
                        $sortIndex = is_numeric($c['sort_index'] ?? null) ? (int) $c['sort_index'] : (int) $idx;

                        ProductFinishingLink::query()->create([
                            'product_id' => $product->id,
                            'finishing_product_id' => (int) $finishingId,
                            'pricing_mode' => $mode,
                            'min_qty' => $min,
                            'max_qty' => $max,
                            'is_required' => $isRequired,
                            'sort_index' => $sortIndex,
                            'is_active' => true,
                            'created_by' => $userId,
                            'updated_by' => $userId,
                        ]);
                    }
                }
            } catch (ValidationException $e) {
                throw $e;
            } catch (\Throwable $e) {
                Log::error('Product store Step 5 (finishings) failed', [
                    'product_id' => $product->id ?? null,
                    'user_id' => Auth::user()?->id,
                    'error' => $e->getMessage(),
                ]);

                throw ValidationException::withMessages([
                    'finishings' => 'Could not save finishings. Please try again.',
                ]);
            }

            // ==========================
            // STEP 6: MEDIA (IMAGES + ATTACHMENTS)
            // ==========================
            try {
                $validated = validator($request->all(), [
                    'images' => ['nullable', 'array', 'max:12'],
                    'images.*' => ['file', 'mimes:jpg,jpeg,png,webp', 'max:10240'],
                    'primary_image_index' => ['nullable', 'integer', 'min:0', 'max:11'],
                    'image_sort' => ['nullable', 'array'],
                    'image_sort.*' => ['nullable', 'integer', 'min:0', 'max:9999'],

                    'attachments' => ['nullable', 'array', 'max:12'],
                    'attachments.*' => ['file', 'mimes:pdf,ai,psd,eps,svg,jpg,jpeg,png,zip,rar', 'max:25600'],
                    'attachment_sort' => ['nullable', 'array'],
                    'attachment_sort.*' => ['nullable', 'integer', 'min:0', 'max:9999'],
                ])->validate();

                $images = $request->file('images', []);
                $attachments = $request->file('attachments', []);

                $primaryIndex = $validated['primary_image_index'] ?? null;
                $imageSort = $validated['image_sort'] ?? [];
                $attachmentSort = $validated['attachment_sort'] ?? [];

                app(ProductMediaService::class)->storeMediaForProduct(
                    $product,
                    $images,
                    $primaryIndex,
                    $imageSort,
                    $attachments,
                    $attachmentSort
                );
            } catch (ValidationException $e) {
                throw $e;
            } catch (\Throwable $e) {
                Log::error('Product store Step 6 (media) failed', [
                    'product_id' => $product->id ?? null,
                    'user_id' => Auth::user()?->id,
                    'error' => $e->getMessage(),
                ]);

                throw ValidationException::withMessages([
                    'images' => 'Could not upload media. Please try again.',
                ]);
            }

            // ==========================
            // STEP 7: SEO
            // ==========================
            try {
                $seoValidated = validator($request->all(), [
                    'seo' => ['nullable', 'array'],
                    'seo.seo_title' => ['nullable', 'string', 'max:160'],
                    'seo.seo_description' => ['nullable', 'string', 'max:255'],
                    'seo.seo_keywords' => ['nullable', 'string', 'max:255'],
                    'seo.og_title' => ['nullable', 'string', 'max:160'],
                    'seo.og_description' => ['nullable', 'string', 'max:255'],
                    'seo.canonical_url' => ['nullable', 'url', 'max:500'],
                    'seo.is_indexable' => ['nullable', 'in:0,1'],
                    'seo.og_image' => ['nullable', 'file', 'mimes:jpg,jpeg,png,webp', 'max:10240'],
                ])->validate();

                $seoInput = $seoValidated['seo'] ?? [];
                $userId = Auth::user()?->id;

                $ogImagePath = null;
                if ($request->hasFile('seo.og_image')) {
                    $ogFile = $request->file('seo.og_image');
                    $ogImagePath = $ogFile->storePublicly("products/{$product->id}/seo", 'public');
                }

                $seoTitle = $seoInput['seo_title'] ?? null;
                if ($seoTitle === null || $seoTitle === '') {
                    $seoTitle = $product->name;
                }

                $seoDescription = $seoInput['seo_description'] ?? null;
                if (($seoDescription === null || $seoDescription === '') && $product->short_description) {
                    $seoDescription = $product->short_description;
                }

                $ogTitle = $seoInput['og_title'] ?? null;
                if ($ogTitle === null || $ogTitle === '') {
                    $ogTitle = $seoTitle;
                }

                $ogDescription = $seoInput['og_description'] ?? null;
                if ($ogDescription === null || $ogDescription === '') {
                    $ogDescription = $seoDescription;
                }

                $isIndexable = array_key_exists('is_indexable', $seoInput)
                    ? (bool) ((int) $seoInput['is_indexable'])
                    : true;

                $payload = [
                    'seo_title' => $seoTitle,
                    'seo_description' => $seoDescription,
                    'seo_keywords' => $seoInput['seo_keywords'] ?? null,
                    'og_title' => $ogTitle,
                    'og_description' => $ogDescription,
                    'canonical_url' => $seoInput['canonical_url'] ?? null,
                    'is_indexable' => $isIndexable,
                    'updated_by' => $userId,
                ];

                if ($ogImagePath) {
                    $payload['og_image_path'] = $ogImagePath;
                }

                $existingSeo = $product->seo;
                if ($existingSeo) {
                    $existingSeo->fill($payload);
                    if (! $existingSeo->created_by) {
                        $existingSeo->created_by = $userId;
                    }
                    $existingSeo->save();
                } else {
                    $payload['created_by'] = $userId;
                    $product->seo()->create($payload);
                }
            } catch (ValidationException $e) {
                throw $e;
            } catch (\Throwable $e) {
                Log::error('Product store Step 7 (SEO) failed', [
                    'product_id' => $product->id ?? null,
                    'user_id' => Auth::user()?->id,
                    'error' => $e->getMessage(),
                ]);

                throw ValidationException::withMessages([
                    'seo.seo_title' => 'Could not save SEO. Please try again.',
                ]);
            }

            DB::commit();

            // Recommended flow: go to Edit OR go to Pricing setup
            return redirect()
                ->route('admin.products.edit', $product)
                ->with('success', 'Product created successfully. Now configure details and pricing.');
        } catch (ValidationException $e) {
            DB::rollBack();

            // Let Laravel handle redirect + error bag
            throw $e;
        } catch (\Throwable $e) {
            DB::rollBack();

            Log::error('Admin ProductController@store error', [
                'user_id' => Auth::user()?->id,
                'payload' => $request->safe()->except([]),
                'error' => $e->getMessage(),
            ]);

            return back()
                ->withInput()
                ->with('error', 'Failed to create the product. Please check the details and try again.');
        }
    }

    private function mediaDisk(): string
    {
        return 'public';
    }

    private function nextSortIndex($query): int
    {
        $max = (int) $query->max('sort_index');
        return $max + 1;
    }

    public function wizardSaveDraft(Product $product)
    {
        $this->authorize('update', $product);

        try {
            $product->update([
                'status' => 'draft',
            ]);

            return back()->with('success', 'Saved as draft.');
        } catch (\Throwable $e) {
            Log::error('Product wizardSaveDraft failed', [
                'product_id' => $product->id,
                'user_id' => Auth::user()->id(),
                'error' => $e->getMessage(),
            ]);

            return back()->with('error', 'Could not save draft. Please try again.');
        }
    }

    public function wizardPublish(Product $product)
    {
        $this->authorize('publish', $product);

        try {
            $hasImage = $product->images()->exists();
            $hasPricing = $product->pricings()
                ->public()
                ->active()
                ->exists();

            if (! $hasPricing) {
                throw ValidationException::withMessages([
                    'publish' => 'Public pricing is required before publishing.',
                ]);
            }

            if (! $hasImage) {
                throw ValidationException::withMessages([
                    'publish' => 'At least one image is recommended before publishing.',
                ]);
            }

            $product->update([
                'status' => 'active',
            ]);

            if (Auth::user()?->id) {
                ProductWizardCache::forgetState($product, (int) Auth::user()?->id);
            }

            return redirect()
                ->route('admin.products.index')
                ->with('success', 'Product published successfully.');
        } catch (ValidationException $e) {
            throw $e;
        } catch (\Throwable $e) {
            Log::error('Product wizardPublish failed', [
                'product_id' => $product->id,
                'user_id' => Auth::user()->id(),
                'error' => $e->getMessage(),
            ]);

            return back()->with('error', 'Could not publish product. Please try again.');
        }
    }

    public function uploadImage(Request $request, Product $product)
    {
        $this->authorize('update', $product);

        $data = $request->validate([
            'image' => ['required', 'file', 'mimes:jpg,jpeg,png,webp', 'max:15360'],
            'alt_text' => ['nullable', 'string', 'max:180'],
        ]);

        $disk = $this->mediaDisk();
        $file = $data['image'];

        $folder = "products/{$product->id}/images";
        $safeName = Str::uuid()->toString().'.'.$file->getClientOriginalExtension();
        $path = $file->storeAs($folder, $safeName, $disk);

        $isFirst = $product->images()->count() === 0;

        $img = $product->images()->create([
            'disk' => $disk,
            'path' => $path,
            'original_name' => $file->getClientOriginalName(),
            'mime' => $file->getMimeType(),
            'size_bytes' => $file->getSize(),
            'alt_text' => $data['alt_text'] ?? null,
            'sort_index' => $this->nextSortIndex($product->images()),
            'is_featured' => $isFirst ? 1 : 0,
            'meta' => null,
        ]);

        return response()->json([
            'ok' => true,
            'image' => [
                'id' => $img->id,
                'url' => Storage::url($path),
                'is_featured' => (bool) $img->is_featured,
                'alt_text' => $img->alt_text,
            ],
        ]);
    }

    public function deleteImage(Request $request, Product $product, ProductImage $image)
    {
        $this->authorize('update', $product);

        abort_unless($image->product_id === $product->id, 404);

        $disk = $image->disk ?: $this->mediaDisk();
        $path = $image->path;

        $wasFeatured = (bool) $image->is_featured;

        $image->delete();

        if ($path && Storage::disk($disk)->exists($path)) {
            Storage::disk($disk)->delete($path);
        }

        if ($wasFeatured) {
            $next = $product->images()->whereNull('deleted_at')->orderBy('sort_index')->first();
            if ($next) {
                $product->images()->update(['is_featured' => 0]);
                $next->update(['is_featured' => 1]);
            }
        }

        return response()->json(['ok' => true]);
    }

    public function setFeaturedImage(Request $request, Product $product, ProductImage $image)
    {
        $this->authorize('update', $product);
        abort_unless($image->product_id === $product->id, 404);

        $product->images()->update(['is_featured' => 0]);
        $image->update(['is_featured' => 1]);

        return response()->json(['ok' => true]);
    }

    public function reorderImages(Request $request, Product $product)
    {
        $this->authorize('update', $product);

        $data = $request->validate([
            'ids' => ['required', 'array', 'min:1'],
            'ids.*' => ['integer'],
        ]);

        $ids = $product->images()->whereIn('id', $data['ids'])->pluck('id')->toArray();

        foreach ($data['ids'] as $i => $id) {
            if (!in_array($id, $ids, true)) {
                continue;
            }
            ProductImage::where('id', $id)->update(['sort_index' => $i + 1]);
        }

        return response()->json(['ok' => true]);
    }

    public function updateImage(Request $request, Product $product, ProductImage $image)
    {
        $this->authorize('update', $product);
        abort_unless($image->product_id === $product->id, 404);

        $data = $request->validate([
            'alt_text' => ['nullable', 'string', 'max:180'],
        ]);

        $image->update([
            'alt_text' => $data['alt_text'] ?? null,
        ]);

        return response()->json(['ok' => true]);
    }

    public function uploadFile(Request $request, Product $product)
    {
        $this->authorize('update', $product);

        $data = $request->validate([
            'file' => ['required', 'file', 'max:25600'],
            'label' => ['nullable', 'string', 'max:120'],
            'visibility' => ['nullable', 'in:public,internal'],
            'file_type' => ['nullable', 'in:guideline,template,spec_sheet,other'],
        ]);

        $disk = $this->mediaDisk();
        $file = $data['file'];

        $folder = "products/{$product->id}/files";
        $ext = $file->getClientOriginalExtension();
        $safeName = Str::uuid()->toString().($ext ? ".{$ext}" : '');
        $path = $file->storeAs($folder, $safeName, $disk);

        $pf = $product->files()->create([
            'label' => $data['label'] ?? $file->getClientOriginalName(),
            'file_path' => $path,
            'file_type' => $data['file_type'] ?? 'other',
            'visibility' => $data['visibility'] ?? 'internal',
            'created_by' => Auth::user()?->id,
            'updated_by' => Auth::user()?->id,
        ]);

        return response()->json([
            'ok' => true,
            'file' => [
                'id' => $pf->id,
                'label' => $pf->label,
                'visibility' => $pf->visibility,
                'name' => $pf->label,
                'url' => Storage::url($pf->file_path),
            ],
        ]);
    }

    public function deleteFile(Request $request, Product $product, ProductFile $file)
    {
        $this->authorize('update', $product);
        abort_unless($file->product_id === $product->id, 404);

        $disk = $this->mediaDisk();
        $path = $file->file_path;

        $file->delete();

        if ($path && Storage::disk($disk)->exists($path)) {
            Storage::disk($disk)->delete($path);
        }

        return response()->json(['ok' => true]);
    }

    public function reorderFiles(Request $request, Product $product)
    {
        $this->authorize('update', $product);

        throw ValidationException::withMessages([
            'files' => 'Reordering files is not enabled yet.',
        ]);
    }

    public function updateFile(Request $request, Product $product, ProductFile $file)
    {
        $this->authorize('update', $product);
        abort_unless($file->product_id === $product->id, 404);

        $data = $request->validate([
            'label' => ['nullable', 'string', 'max:120'],
            'visibility' => ['nullable', 'in:public,internal'],
            'file_type' => ['nullable', 'in:guideline,template,spec_sheet,other'],
        ]);

        $file->update([
            'label' => $data['label'] ?? $file->label,
            'visibility' => $data['visibility'] ?? $file->visibility,
            'file_type' => $data['file_type'] ?? $file->file_type,
            'updated_by' => Auth::user()?->id,
        ]);

        return response()->json(['ok' => true]);
    }
}

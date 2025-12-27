<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\WorkingGroup;
use App\Services\Pricing\DimensionCalculatorService;
use App\Services\Pricing\PricingResolverService;
use App\Services\Pricing\VariantAvailabilityResolverService;
use Illuminate\Http\Request;

class EstimatePricingController extends Controller
{
    public function __construct(
        private readonly PricingResolverService $pricing,
        private readonly DimensionCalculatorService $dimensions,
        private readonly VariantAvailabilityResolverService $variantAvailability,
    ) {
    }

    public function products(Request $request)
    {
        $validated = $request->validate([
            'working_group_id' => ['required', 'integer', 'exists:working_groups,id'],
            'q' => ['nullable', 'string', 'max:120'],
            'ids' => ['nullable', 'string', 'max:2000'], // comma-separated
            'limit' => ['nullable', 'integer', 'min:1', 'max:50'],
        ]);

        $wgId = (int) $validated['working_group_id'];
        $q = trim((string) ($validated['q'] ?? ''));
        $limit = (int) ($validated['limit'] ?? 20);
        $limit = max(1, min(50, $limit));

        $ids = [];
        $rawIds = trim((string) ($validated['ids'] ?? ''));
        if ($rawIds !== '') {
            $ids = collect(explode(',', $rawIds))
                ->map(fn ($v) => (int) trim($v))
                ->filter(fn ($v) => $v > 0)
                ->unique()
                ->take(100)
                ->values()
                ->all();
        }

        // Keep only products/services that can be quoted in estimates
        $productsQuery = Product::query()
            ->where('status', 'active')
            ->whereIn('product_type', ['standard', 'dimension_based', 'service'])
            ->with(['primaryImage:id,product_id,path,is_featured'])
            ->when(!empty($ids), fn ($qq) => $qq->whereIn('id', $ids))
            ->when(empty($ids) && $q !== '', function ($qq) use ($q) {
                $like = '%'.$q.'%';
                $qq->where(function ($sub) use ($like) {
                    $sub->where('name', 'like', $like)
                        ->orWhere('product_code', 'like', $like);
                });
            })
            ->orderBy('name')
            ->limit($limit);

        $products = $productsQuery->get([
                'id',
                'name',
                'product_code',
                'product_type',
                'requires_dimensions',
                'allow_rotation_to_fit_roll',
                'min_width_in',
                'max_width_in',
                'min_height_in',
                'max_height_in',
            ]);

        $items = $products->map(function (Product $p) use ($wgId) {
            $rp = $this->pricing->resolve($p, $wgId);

            $isDimensionBased = (bool) ($p->product_type === 'dimension_based' || $p->requires_dimensions);

            $price = null;
            $priceMode = 'none'; // unit | sqft | none
            $label = null;
            $rates = null;

            if ($rp) {
	        if ($isDimensionBased) {
                    $rates = $this->pricing->dimensionRates($rp);
                    $rate = $rates['rate_per_sqft'] ?? null;
                    $priceMode = 'sqft';
                    $price = $rate;
                    $label = $rate === null ? null : ('LKR '.number_format((float) $rate, 4).'/sqft');
                } else {
                    $unit = $this->pricing->baseUnitPrice($rp, 1);
                    $priceMode = 'unit';
                    $price = $unit;
                    $label = $unit === null ? null : ('LKR '.number_format((float) $unit, 2));
                }
            }

            $placeholder = asset('assets/placeholders/product.png');
            $path = $p->primaryImage?->path ? ltrim((string) $p->primaryImage->path, '/') : '';
            $imageUrl = $path !== '' ? asset('storage/' . $path) : $placeholder;

            return [
                'id' => $p->id,
                'name' => $p->name,
                'code' => $p->product_code,
                'product_type' => $p->product_type,
                'is_dimension_based' => $isDimensionBased,
                'image_url' => $imageUrl,
                'allow_rotation_to_fit_roll' => (bool) $p->allow_rotation_to_fit_roll,
                'min_width_in' => $p->min_width_in,
                'max_width_in' => $p->max_width_in,
                'min_height_in' => $p->min_height_in,
                'max_height_in' => $p->max_height_in,
                'price_mode' => $priceMode,
                'price' => $price,
                'price_label' => $label,
                'rates' => $rates,
            ];
        })->values();

        return response()->json([
            'working_group_id' => $wgId,
            'items' => $items,
        ]);
    }

    public function rolls(Request $request, Product $product)
    {
        $request->validate([
            'working_group_id' => ['nullable', 'integer', 'exists:working_groups,id'],
        ]);

        if ($product->status !== 'active' || $product->product_type !== 'dimension_based') {
            return response()->json([
                'message' => 'This product does not support roll selection.',
            ], 422);
        }

        $rolls = $product->allowedRolls()
            ->active()
            ->orderBy('width_in')
            ->get(['rolls.id', 'rolls.name', 'rolls.width_in'])
            ->map(fn ($r) => [
                'roll_id' => (int) $r->id,
                'name' => (string) $r->name,
                'width_in' => (float) $r->width_in,
            ])
            ->values();

        return response()->json([
            'product_id' => (int) $product->id,
            'items' => $rolls,
        ]);
    }

    public function variants(Request $request, Product $product)
    {
        $validated = $request->validate([
            'working_group_id' => ['required', 'integer', 'exists:working_groups,id'],
        ]);

        if ($product->status !== 'active') {
            return response()->json(['message' => 'Product inactive.'], 422);
        }

        $wgId = (int) $validated['working_group_id'];

        // Match public product variants structure:
        // option_groups + variant_matrix (enabled by WG)
        $product->loadMissing([
            'optionGroups:id,name',
            'options:id,option_group_id,label',
            'activeVariantSets:id,product_id,code,is_active',
            'activeVariantSets.items:id,variant_set_id,option_id',
            'activeVariantSets.items.option:id,option_group_id,label',
        ]);

        $optionRows = ($product->options ?? collect())->values();
        $optionsByGroup = $optionRows->groupBy('option_group_id');

        $optionGroups = ($product->optionGroups ?? collect())->values()->map(function ($g) use ($optionsByGroup) {
            $opts = ($optionsByGroup[$g->id] ?? collect())->map(fn ($o) => [
                'id' => (int) $o->id,
                'name' => (string) $o->label,
            ])->values()->all();

            return [
                'id' => (int) $g->id,
                'name' => (string) $g->name,
                'is_required' => (bool) ($g->pivot?->is_required ?? false),
                'options' => $opts,
            ];
        })->values();

        $sets = ($product->activeVariantSets ?? collect())->where('is_active', true)->values();

        $enabledSetIds = $this->variantAvailability->filterEnabledVariantSetIds(
            $product,
            $sets->pluck('id')->map(fn ($x) => (int) $x)->all(),
            $wgId
        );
        $enabledLookup = array_flip($enabledSetIds);

        $variantMatrix = $sets
            ->filter(fn ($set) => isset($enabledLookup[(int) $set->id]))
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
            $optionGroups = $optionGroups->filter(fn ($g) => in_array((int) $g['id'], $variantGroupIds, true))->values();
        }

        return response()->json([
            'product_id' => (int) $product->id,
            'working_group_id' => $wgId,
            'option_groups' => $optionGroups->values()->all(),
            'variant_matrix' => $variantMatrix->values()->all(),
        ]);
    }

    public function finishings(Request $request, Product $product)
    {
        $validated = $request->validate([
            'working_group_id' => ['required', 'integer', 'exists:working_groups,id'],
        ]);

        if ($product->status !== 'active') {
            return response()->json(['message' => 'Product inactive.'], 422);
        }

        $wgId = (int) $validated['working_group_id'];

        $rp = $this->pricing->resolve($product, $wgId);
        if (!$rp) {
            return response()->json(['message' => 'No pricing configured for this working group.'], 422);
        }

        $links = \App\Models\ProductFinishingLink::query()
            ->where('product_id', $product->id)
            ->where('is_active', true)
            ->whereNull('deleted_at')
            ->get([
                'finishing_product_id',
                'is_required',
                'default_qty',
                'min_qty',
                'max_qty',
            ])
            ->keyBy('finishing_product_id');

        $finishings = $product->finishings()
            ->where('products.status', 'active')
            ->with(['primaryImage:id,product_id,path,is_featured'])
            ->get(['products.id', 'products.name', 'products.product_code'])
            ->values();

        $items = $finishings->map(function (Product $fp) use ($links, $rp, $wgId) {
            $link = $links->get($fp->id);
            $defaultQty = (int) ($link?->default_qty ?? 1);
            $defaultQty = $defaultQty > 0 ? $defaultQty : 1;

            $unitPrice = null;
            $priceMode = 'unknown';

            $fpRow = $this->pricing->finishingPricing($rp, (int) $fp->id);
            if ($fpRow && $fpRow->is_active) {
                if ($fpRow->price_per_piece !== null) {
                    $unitPrice = (float) $fpRow->price_per_piece;
                    $priceMode = 'per_piece';
                } elseif ($fpRow->price_per_side !== null) {
                    $unitPrice = (float) $fpRow->price_per_side;
                    $priceMode = 'per_side';
                } elseif ($fpRow->flat_price !== null) {
                    $unitPrice = (float) $fpRow->flat_price;
                    $priceMode = 'flat';
                }
            }

            if ($unitPrice === null) {
                $frp = $this->pricing->resolve($fp, $wgId);
                $fallback = $frp ? $this->pricing->baseUnitPrice($frp, $defaultQty) : null;
                if ($fallback !== null) {
                    $unitPrice = (float) $fallback;
                    $priceMode = 'fallback_unit';
                }
            }

            $placeholder = asset('assets/placeholders/product.png');
            $path = $fp->primaryImage?->path ? ltrim((string) $fp->primaryImage->path, '/') : '';
            $imageUrl = $path !== '' ? asset('storage/' . $path) : $placeholder;

            $label = $unitPrice === null ? null : ('LKR ' . number_format((float) $unitPrice, 2));
            if ($priceMode === 'flat' && $unitPrice !== null) {
                $label = 'LKR ' . number_format((float) $unitPrice, 2) . ' (flat)';
            }

            return [
                'finishing_product_id' => (int) $fp->id,
                'name' => (string) $fp->name,
                'code' => (string) ($fp->product_code ?? ''),
                'image_url' => $imageUrl,
                'is_required' => (bool) ($link?->is_required ?? false),
                'default_qty' => $link?->default_qty !== null ? (int) $link->default_qty : null,
                'min_qty' => $link?->min_qty !== null ? (int) $link->min_qty : null,
                'max_qty' => $link?->max_qty !== null ? (int) $link->max_qty : null,
                'unit_price' => $unitPrice,
                'price_mode' => $priceMode,
                'price_label' => $label,
            ];
        })->values();

        return response()->json([
            'product_id' => (int) $product->id,
            'working_group_id' => $wgId,
            'items' => $items,
        ]);
    }

    public function quote(Request $request)
    {
        $validated = $request->validate([
            'working_group_id' => ['required', 'integer', 'exists:working_groups,id'],
            'product_id' => ['required', 'integer', 'exists:products,id'],
            'qty' => ['required', 'integer', 'min:1', 'max:100000'],

            'width' => ['nullable', 'numeric', 'min:0.01'],
	            'height' => ['nullable', 'numeric', 'min:0.01'],
	            'unit' => ['nullable', 'string', 'in:in,ft,mm,cm,m'],
	            'roll_id' => ['nullable', 'integer'],
	            'options' => ['nullable', 'array', 'max:60'],
	            'options.*' => ['nullable', 'integer'],
	            'finishings' => ['nullable', 'array'],
	            'finishings.*' => ['nullable'],
	        ]);

        $wg = WorkingGroup::query()->whereKey((int) $validated['working_group_id'])->firstOrFail();
        $product = Product::query()->whereKey((int) $validated['product_id'])->firstOrFail();

        if ($product->status !== 'active' || !in_array($product->product_type, ['standard', 'dimension_based', 'service'], true)) {
            return response()->json(['message' => 'This product is not available for estimates.'], 422);
        }

        $qty = (int) $validated['qty'];

        $rp = $this->pricing->resolve($product, $wg->id);
        if (!$rp) {
            return response()->json(['message' => 'No pricing configured for this working group.'], 422);
        }

        $isDimensionBased = (bool) ($product->product_type === 'dimension_based' || $product->requires_dimensions);

	        $breakdown = [];
	        $baseTotal = 0.0;
	        $variantTotal = 0.0;
	        $finishingsTotal = 0.0;
	        $finishingsRows = [];
	        $total = 0.0; // returned as base + finishings (see below)

        $w = $validated['width'] ?? null;
        $h = $validated['height'] ?? null;
        $unit = (string) ($validated['unit'] ?? 'in');

        $widthIn = null;
        $heightIn = null;
        $requestedWidthIn = null;
        $requestedHeightIn = null;

        $selectedRollId = null;
        $selectedRollWidthIn = null;
        $selectedRollRotated = false;
        $selectedRollAuto = false;

        $rollId = isset($validated['roll_id']) ? (int) $validated['roll_id'] : null;
        $rollWidthIn = null;

        // Variants (dependent option groups -> match a variant set combination; same logic as public product quote)
        $matchedVariantSetId = null;
        $variantLabel = null;
        $vp = null;
        $selectedByGroup = [];

        foreach ((array) ($validated['options'] ?? []) as $gid => $oid) {
            if (!is_numeric($gid) || !is_numeric($oid)) {
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
            $optionIds = array_values($selectedByGroup);

            // Validate option IDs belong to the claimed group IDs
            $groupByOptionId = \App\Models\Option::query()
                ->whereIn('id', $optionIds)
                ->pluck('option_group_id', 'id')
                ->all();

            foreach ($selectedByGroup as $gid => $oid) {
                if (!isset($groupByOptionId[$oid]) || (int) $groupByOptionId[$oid] !== (int) $gid) {
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
                ->with(['items.option:id,option_group_id,label'])
                ->get();

            if ($sets->count() > 0) {
                // Filter enabled sets for WG
                $enabledSetIds = $this->variantAvailability->filterEnabledVariantSetIds(
                    $product,
                    $sets->pluck('id')->map(fn($x) => (int) $x)->all(),
                    $wg->id
                );
                $enabledLookup = array_flip($enabledSetIds);
                $sets = $sets->filter(fn($s) => isset($enabledLookup[(int) $s->id]))->values();

                $requiredGroupIds = $sets
                    ->flatMap(fn($s) => ($s->items ?? collect())->map(fn($it) => (int) ($it->option?->option_group_id ?: 0)))
                    ->filter(fn($v) => $v > 0)
                    ->unique()
                    ->values()
                    ->all();

                $isComplete = count($requiredGroupIds) > 0
                    && collect($requiredGroupIds)->every(fn($gid) => isset($selectedByGroup[(int) $gid]));

                if ($isComplete) {
                    foreach ($sets as $set) {
                        $setMap = [];
                        foreach (($set->items ?? collect()) as $it) {
                            $gid = (int) ($it->option?->option_group_id ?: 0);
                            if ($gid <= 0) continue;
                            $setMap[$gid] = (int) $it->option_id;
                        }

                        $matches = true;
                        foreach ($requiredGroupIds as $gid) {
                            $gid = (int) $gid;
                            if (!isset($setMap[$gid]) || (int) $setMap[$gid] !== (int) ($selectedByGroup[$gid] ?? 0)) {
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
                        $vp = $this->pricing->variantPricing($rp, $matchedVariantSetId);

                        // Build label from selected options (group: option)
                        $optRows = \App\Models\Option::query()
                            ->with('group:id,name')
                            ->whereIn('id', $optionIds)
                            ->get(['id', 'label', 'option_group_id'])
                            ->keyBy('id');

                        $parts = [];
                        foreach ($requiredGroupIds as $gid) {
                            $oid = (int) ($selectedByGroup[(int) $gid] ?? 0);
                            $o = $optRows->get($oid);
                            if (!$o) continue;
                            $gName = (string) ($o->group?->name ?? '');
                            $oName = (string) ($o->label ?? '');
                            $parts[] = trim(($gName !== '' ? ($gName . ': ') : '') . $oName);
                        }
                        $variantLabel = trim(implode(', ', array_filter($parts)));
                    }
                }
            }
        }

        if ($isDimensionBased) {
            if ($w === null || $h === null) {
                return response()->json([
                    'message' => 'Width and height are required for this product.',
                ], 422);
            }

            $widthIn = $this->dimensions->toInches((float) $w, $unit);
            $heightIn = $this->dimensions->toInches((float) $h, $unit);
            $requestedWidthIn = (float) $widthIn;
            $requestedHeightIn = (float) $heightIn;

            $allowRotate = (bool) $product->allow_rotation_to_fit_roll;

            // Match public logic: try placing the larger side across roll width first.
            $firstSide = $requestedWidthIn >= $requestedHeightIn ? 'w' : 'h';
            $secondSide = $firstSide === 'w' ? 'h' : 'w';

            if ($rollId) {
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
                $fit = false;

                foreach ([$firstSide, $secondSide] as $side) {
                    if ($side === 'w') {
                        if ($rollWidthIn < (float) $requestedWidthIn) {
                            continue;
                        }
                        $widthIn = (float) $requestedWidthIn;
                        $heightIn = (float) $requestedHeightIn;
                        $selectedRollRotated = false;
                        $fit = true;
                        break;
                    }

                    // side === 'h' => requires rotation unless it's square
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
                $selectedRollWidthIn = (float) $rollWidthIn;
                $selectedRollAuto = false;
            } else {
                $rolls = $product->allowedRolls()
                    ->active()
                    ->get(['rolls.id', 'rolls.width_in'])
                    ->sortBy(fn ($r) => (float) $r->width_in)
                    ->values();

                if ($rolls->count() > 0) {
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

            // Enforce product constraints (based on requested values, not rotated)
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

	            $rates = $rollId ? $this->pricing->rollRates($rp, $rollId) : $this->pricing->dimensionRates($rp);
	            $rate = !empty($rates['rate_per_sqft']) ? (float) $rates['rate_per_sqft'] : null;
	            $offcutRate = !empty($rates['offcut_rate_per_sqft']) ? (float) $rates['offcut_rate_per_sqft'] : null;
	            $minCharge = !empty($rates['min_charge']) ? (float) $rates['min_charge'] : null;

            $calc = $rate === null ? null : $this->dimensions->calculateDimensionPrice(
                (float) $widthIn,
                (float) $heightIn,
                (float) $qty,
                (float) $rate,
                $offcutRate,
                $minCharge,
                $rollWidthIn
            );

            $baseTotal = (float) ($calc['total'] ?? 0);
            $total = $baseTotal;

            if ($calc) {
                $areaCharge = (float) ($calc['area_charge'] ?? 0);
                $offcutCharge = (float) ($calc['offcut_charge'] ?? 0);
                $preMin = $areaCharge + $offcutCharge;

                $breakdown[] = ['label' => 'Printing area', 'amount' => $areaCharge];
                if ($offcutCharge > 0) {
                    $breakdown[] = ['label' => 'Offcut', 'amount' => $offcutCharge];
                }
                if ($minCharge !== null && $baseTotal > $preMin) {
                    $breakdown[] = ['label' => 'Min charge', 'amount' => ($baseTotal - $preMin)];
                }
	            } else {
	                $breakdown[] = ['label' => 'Base', 'amount' => $baseTotal];
	            }

	            // Variant pricing (additive, public-style)
	            if ($vp && $vp->is_active) {
	                if ($vp->fixed_price !== null) {
	                    $variantTotal = (float) $vp->fixed_price * $qty;
	                } elseif ($vp->rate_per_sqft !== null) {
	                    $calcVar = $this->dimensions->calculateDimensionPrice(
	                        (float) $widthIn,
	                        (float) $heightIn,
	                        (float) $qty,
	                        (float) $vp->rate_per_sqft,
	                        $vp->offcut_rate_per_sqft !== null ? (float) $vp->offcut_rate_per_sqft : null,
	                        $vp->min_charge !== null ? (float) $vp->min_charge : null,
	                        $rollWidthIn
	                    );
	                    $variantTotal = (float) ($calcVar['total'] ?? 0);
	                }
	                if ($variantTotal > 0) {
	                    $breakdown[] = ['label' => 'Variants', 'amount' => $variantTotal];
	                }
	            }
	        } else {
	            $unitPriceStr = null;

	            $unitPriceStr = $this->pricing->baseUnitPrice($rp, $qty);

	            if ($unitPriceStr === null) {
	                return response()->json(['message' => 'Unable to resolve unit price.'], 422);
	            }

	            $baseTotal = (float) $unitPriceStr * $qty;
	            $total = $baseTotal;
	            $breakdown[] = ['label' => 'Base', 'amount' => $baseTotal];

	            // Variant pricing (additive, fixed only for unit-mode)
	            if ($vp && $vp->is_active && $vp->fixed_price !== null) {
	                $variantTotal = (float) $vp->fixed_price * $qty;
	                if ($variantTotal > 0) {
	                    $breakdown[] = ['label' => 'Variants', 'amount' => $variantTotal];
	                }
	            }
	        }

        if ($variantLabel) {
            $breakdown[] = ['label' => 'Variant', 'amount' => 0, 'meta' => ['label' => $variantLabel]];
        }

        // Finishings (optional; future-proof)
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
                ->get(['id', 'status', 'product_type'])
                ->keyBy('id');

            foreach ($validFinishingIds as $fid) {
                $requestedQty = $finishingsInput[(string) $fid] ?? 0;
                $requestedQty = is_numeric($requestedQty) ? (int) $requestedQty : 0;
                if ($requestedQty <= 0) {
                    continue;
                }

                $fp = $this->pricing->finishingPricing($rp, (int) $fid);
                $usedFallback = false;

                $unit = null;
                $line = null;
                $mode = null;

                if ($fp && $fp->is_active) {
                    if ($fp->price_per_piece !== null) {
                        $unit = (float) $fp->price_per_piece;
                        $line = $unit * $requestedQty;
                        $mode = 'per_piece';
                    } elseif ($fp->price_per_side !== null) {
                        $unit = (float) $fp->price_per_side;
                        $line = $unit * $requestedQty;
                        $mode = 'per_side';
                    } elseif ($fp->flat_price !== null) {
                        $unit = (float) $fp->flat_price;
                        $line = (float) $fp->flat_price;
                        $mode = 'flat';
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

                    $frp = $this->pricing->resolve($finishingProduct, $wg->id);
                    if (! $frp) {
                        continue;
                    }

                    $unitFallback = $this->pricing->baseUnitPrice($frp, $requestedQty);
                    if ($unitFallback === null) {
                        continue;
                    }

                    $unit = (float) $unitFallback;
                    $line = $unit * $requestedQty;
                    $mode = 'fallback_unit';
                }

                $finishingsTotal += (float) ($line ?? 0);
                $finishingsRows[] = [
                    'finishing_product_id' => (int) $fid,
                    'label' => (string) ($finishingProductsById->get((int) $fid)?->name ?? ('Finishing #' . $fid)),
                    'qty' => $requestedQty,
                    'unit_price' => round((float) ($unit ?? 0), 2),
                    'total' => round((float) ($line ?? 0), 2),
                    'pricing_snapshot' => [
                        'source' => 'admin.estimates.quote.finishings',
                        'mode' => $mode,
                        'qty' => $requestedQty,
                        'unit_price' => $unit,
                        'total' => $line,
                        'captured_at' => now()->toISOString(),
                    ],
                ];
            }

            if ($finishingsTotal > 0) {
                $breakdown[] = ['label' => 'Finishings', 'amount' => $finishingsTotal];
            }
        }

	        $lineSubtotal = $baseTotal + $variantTotal;
	        $total = $lineSubtotal + $finishingsTotal;
	        $unitPrice = $qty > 0 ? ($lineSubtotal / $qty) : 0.0;

        $areaSqft = $isDimensionBased && isset($calc) && is_array($calc) ? (float) ($calc['area_sqft'] ?? 0) : null;
        $offcutSqft = $isDimensionBased && isset($calc) && is_array($calc) ? (float) ($calc['offcut_sqft'] ?? 0) : 0.0;

	        $pricingSnapshot = [
	            'source' => 'admin.estimates.quote',
	            'mode' => $isDimensionBased ? 'dimension' : 'unit',
	            'working_group_id' => $wg->id,
	            'product_id' => $product->id,
	            'qty' => $qty,
	            'options' => $selectedByGroup,
	            'variant_set_id' => $matchedVariantSetId,
	            'variant_label' => $variantLabel,
            'requested' => $isDimensionBased ? [
                'width' => (float) $w,
                'height' => (float) $h,
                'unit' => $unit,
            ] : null,
            'roll' => ($isDimensionBased && $selectedRollId && $selectedRollWidthIn !== null) ? [
                'id' => (int) $selectedRollId,
                'width_in' => (float) $selectedRollWidthIn,
                'rotated' => (bool) $selectedRollRotated,
                'auto' => (bool) $selectedRollAuto,
            ] : null,
	            'breakdown' => $breakdown,
	            'base_total' => round($baseTotal, 2),
	            'variant_total' => round($variantTotal, 2),
	            'finishings_total' => round($finishingsTotal, 2),
	            'total' => round($total, 2),
	            'captured_at' => now()->toISOString(),
	        ];

        return response()->json([
            'ok' => true,
	            'data' => [
	                'product_id' => $product->id,
	                'working_group_id' => $wg->id,
	                'qty' => $qty,
	                'unit_price' => round($unitPrice, 2),
	                'line_subtotal' => round($lineSubtotal, 2),
	                'discount_amount' => 0,
	                'tax_amount' => 0,
	                'line_total' => round($lineSubtotal, 2),
	                'width' => $isDimensionBased ? (float) $w : null,
	                'height' => $isDimensionBased ? (float) $h : null,
	                'unit' => $isDimensionBased ? $unit : null,
	                'area_sqft' => $areaSqft === null ? null : round($areaSqft, 4),
	                'offcut_sqft' => $isDimensionBased ? round($offcutSqft, 4) : 0,
	                'roll_id' => $isDimensionBased ? $selectedRollId : null,
	                'roll_auto' => $isDimensionBased ? (bool) $selectedRollAuto : false,
	                'roll_rotated' => $isDimensionBased ? (bool) $selectedRollRotated : false,
	                'variant_set_id' => $matchedVariantSetId,
	                'variant_label' => $variantLabel,
	                'options' => $selectedByGroup,
	                'finishings' => $finishingsRows,
	                'pricing_snapshot' => $pricingSnapshot,
	            ],
	        ]);
    }
}

<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\WorkingGroup;
use App\Services\Pricing\DimensionCalculatorService;
use App\Services\Pricing\PricingResolverService;
use Illuminate\Http\Request;

class EstimatePricingController extends Controller
{
    public function __construct(
        private readonly PricingResolverService $pricing,
        private readonly DimensionCalculatorService $dimensions,
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
        $total = 0.0;

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
            $total += $baseTotal;

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
        } else {
            $unitPriceStr = $this->pricing->baseUnitPrice($rp, $qty);
            if ($unitPriceStr === null) {
                return response()->json(['message' => 'Unable to resolve unit price.'], 422);
            }

            $baseTotal = (float) $unitPriceStr * $qty;
            $total += $baseTotal;
            $breakdown[] = ['label' => 'Base', 'amount' => $baseTotal];
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

            $finishingTotal = 0.0;
            foreach ($validFinishingIds as $fid) {
                $requestedQty = $finishingsInput[(string) $fid] ?? 0;
                $requestedQty = is_numeric($requestedQty) ? (int) $requestedQty : 0;
                if ($requestedQty <= 0) {
                    continue;
                }

                $fp = $this->pricing->finishingPricing($rp, (int) $fid);
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

                    $frp = $this->pricing->resolve($finishingProduct, $wg->id);
                    if (! $frp) {
                        continue;
                    }

                    $unitFallback = $this->pricing->baseUnitPrice($frp, $requestedQty);
                    if ($unitFallback === null) {
                        continue;
                    }

                    $finishingTotal += ((float) $unitFallback * $requestedQty);
                }
            }

            if ($finishingTotal > 0) {
                $total += $finishingTotal;
                $breakdown[] = ['label' => 'Finishings', 'amount' => $finishingTotal];
            }
        }

        $unitPrice = $qty > 0 ? ($total / $qty) : 0.0;

        $areaSqft = $isDimensionBased && isset($calc) && is_array($calc) ? (float) ($calc['area_sqft'] ?? 0) : null;
        $offcutSqft = $isDimensionBased && isset($calc) && is_array($calc) ? (float) ($calc['offcut_sqft'] ?? 0) : 0.0;

        $pricingSnapshot = [
            'source' => 'admin.estimates.quote',
            'mode' => $isDimensionBased ? 'dimension' : 'unit',
            'working_group_id' => $wg->id,
            'product_id' => $product->id,
            'qty' => $qty,
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
                'line_subtotal' => round($total, 2),
                'discount_amount' => 0,
                'tax_amount' => 0,
                'line_total' => round($total, 2),
                'width' => $isDimensionBased ? (float) $w : null,
                'height' => $isDimensionBased ? (float) $h : null,
                'unit' => $isDimensionBased ? $unit : null,
                'area_sqft' => $areaSqft === null ? null : round($areaSqft, 4),
                'offcut_sqft' => $isDimensionBased ? round($offcutSqft, 4) : 0,
                'roll_id' => $isDimensionBased ? $selectedRollId : null,
                'roll_auto' => $isDimensionBased ? (bool) $selectedRollAuto : false,
                'roll_rotated' => $isDimensionBased ? (bool) $selectedRollRotated : false,
                'pricing_snapshot' => $pricingSnapshot,
            ],
        ]);
    }
}

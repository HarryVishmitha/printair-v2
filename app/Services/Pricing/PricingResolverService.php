<?php

namespace App\Services\Pricing;

use App\DTO\Pricing\ResolvedPricing;
use App\Models\Product;
use App\Models\ProductPricing;
use App\Models\WorkingGroup;
use Illuminate\Support\Facades\Log;

class PricingResolverService
{
    /**
     * Resolve the effective pricing row for a product + working group.
     *
     * Rules:
     * - If WG pricing exists => it can override base/variants/finishings selectively.
     * - If WG pricing doesn't exist => fall back to public pricing.
     * - If neither exists => return null (caller decides what to do).
     */
    public function resolve(Product $product, WorkingGroup|int|null $workingGroup = null): ?ResolvedPricing
    {
        try {
            $requestedWgId = $workingGroup instanceof WorkingGroup
                ? $workingGroup->id
                : (is_int($workingGroup) ? $workingGroup : null);

            $requestedWgId = ($requestedWgId && $requestedWgId > 0) ? $requestedWgId : null;

            $publicWgId = null;
            $effectiveWgId = $requestedWgId;
            if ($effectiveWgId === null) {
                $publicWgId = WorkingGroup::getPublicId();
                $effectiveWgId = $publicWgId;
            }

            $public = ProductPricing::query()
                ->where('product_id', $product->id)
                ->public()
                ->active()
                ->with($this->pricingRelations())
                ->first();

            $wgPricing = null;

            if ($effectiveWgId) {
                $wgPricing = ProductPricing::query()
                    ->where('product_id', $product->id)
                    ->forWorkingGroup($effectiveWgId)
                    ->active()
                    ->with($this->pricingRelations())
                    ->first();
            }

            if (! $public && ! $wgPricing) {
                return null;
            }

            // Decide effective pricing row:
            // If WG pricing exists, it is the "effectivePricing" container,
            // but we still may pull some values from public depending on override flags.
            $effective = $wgPricing ?: $public;

            return new ResolvedPricing(
                effectivePricing: $effective,
                publicPricing: $public,
                workingGroupPricing: $wgPricing,
                usingWorkingGroupOverride: (bool) $wgPricing,
                meta: [
                    'product_id' => $product->id,
                    'requested_working_group_id' => $requestedWgId,
                    'public_working_group_id' => $publicWgId,
                    'effective_working_group_id' => $effectiveWgId,
                ]
            );
        } catch (\Throwable $e) {
            Log::error('PricingResolverService@resolve error', [
                'product_id' => $product->id ?? null,
                'working_group_id' => $workingGroup instanceof WorkingGroup ? $workingGroup->id : $workingGroup,
                'error' => $e->getMessage(),
            ]);
            return null;
        }
    }

    /**
     * Get base price for a qty (supports tier pricing).
     * This respects WG override flags:
     * - If WG exists and override_base = true => use WG base/tier values
     * - else => use Public base/tier values (fallback)
     */
    public function baseUnitPrice(ResolvedPricing $rp, int $qty): ?string
    {
        try {
            $source = $this->pickBaseSource($rp);
            if (! $source) {
                return null;
            }

            // Tiered pricing has priority if tiers exist
            $tierPrice = $this->tierPriceForQty($source, $qty);
            if ($tierPrice !== null) {
                return $tierPrice;
            }

            return $source->base_price !== null ? (string) $source->base_price : null;
        } catch (\Throwable $e) {
            Log::error('PricingResolverService@baseUnitPrice error', [
                'pricing_id' => $rp->effectivePricing->id ?? null,
                'qty' => $qty,
                'error' => $e->getMessage(),
            ]);
            return null;
        }
    }

    /**
     * Get dimension-based rate_per_sqft/offcut rates (string decimals).
     * Uses WG override_base if set; otherwise public.
     */
    public function dimensionRates(ResolvedPricing $rp): array
    {
        try {
            $source = $this->pickBaseSource($rp);

            return [
                'rate_per_sqft' => $source?->rate_per_sqft !== null ? (string) $source->rate_per_sqft : null,
                'offcut_rate_per_sqft' => $source?->offcut_rate_per_sqft !== null ? (string) $source->offcut_rate_per_sqft : null,
                'min_charge' => $source?->min_charge !== null ? (string) $source->min_charge : null,
            ];
        } catch (\Throwable $e) {
            Log::error('PricingResolverService@dimensionRates error', [
                'pricing_id' => $rp->effectivePricing->id ?? null,
                'error' => $e->getMessage(),
            ]);
            return [
                'rate_per_sqft' => null,
                'offcut_rate_per_sqft' => null,
                'min_charge' => null,
            ];
        }
    }

    /**
     * Resolve a variant pricing row by variant_set_id.
     * Respects WG override_variants:
     * - If WG exists and override_variants = true => use WG variant pricing if exists
     * - else => use Public variant pricing
     */
    public function variantPricing(ResolvedPricing $rp, int $variantSetId)
    {
        try {
            $source = $this->pickVariantSource($rp);
            if (! $source) {
                return null;
            }

            $source->loadMissing('variantPricings');

            return $source->variantPricings
                ->firstWhere('variant_set_id', $variantSetId);
        } catch (\Throwable $e) {
            Log::error('PricingResolverService@variantPricing error', [
                'pricing_id' => $rp->effectivePricing->id ?? null,
                'variant_set_id' => $variantSetId,
                'error' => $e->getMessage(),
            ]);
            return null;
        }
    }

    /**
     * Resolve finishing pricing by finishing_product_id.
     * Respects WG override_finishings similarly to variants.
     */
    public function finishingPricing(ResolvedPricing $rp, int $finishingProductId)
    {
        try {
            $source = $this->pickFinishingSource($rp);
            if (! $source) {
                return null;
            }

            $source->loadMissing('finishingPricings');

            return $source->finishingPricings
                ->firstWhere('finishing_product_id', $finishingProductId);
        } catch (\Throwable $e) {
            Log::error('PricingResolverService@finishingPricing error', [
                'pricing_id' => $rp->effectivePricing->id ?? null,
                'finishing_product_id' => $finishingProductId,
                'error' => $e->getMessage(),
            ]);
            return null;
        }
    }

    /**
     * Roll-specific rates (optional override).
     *
     * RULE: ProductPricing is always the base.
     * If roll override exists under the chosen source context (WG override_base OR public),
     * it can override rate/offcut/min_charge. If a roll override field is null, fallback to base.
     */
    public function rollRates(ResolvedPricing $rp, int $rollId): array
    {
        try {
            // Base always comes from ProductPricing (resolved with override_base logic)
            $base = $this->pickBaseSource($rp);

            $baseRates = [
                'rate_per_sqft' => $base?->rate_per_sqft !== null ? (string) $base->rate_per_sqft : null,
                'offcut_rate_per_sqft' => $base?->offcut_rate_per_sqft !== null ? (string) $base->offcut_rate_per_sqft : null,
                'min_charge' => $base?->min_charge !== null ? (string) $base->min_charge : null,
            ];

            // If no base pricing exists somehow, return nulls
            if (! $base) {
                return $baseRates;
            }

            $base->loadMissing('rollPricings.roll');

            // Only look for roll overrides inside the SAME pricing source context we chose for base.
            // That keeps "ProductPricing first" and makes roll pricing optional extension.
            $rollOverride = $base->rollPricings
                ? $base->rollPricings->firstWhere('roll_id', $rollId)
                : null;

            if (! $rollOverride || ! $rollOverride->is_active) {
                return $baseRates;
            }

            return [
                'rate_per_sqft' => $rollOverride->rate_per_sqft !== null
                    ? (string) $rollOverride->rate_per_sqft
                    : $baseRates['rate_per_sqft'],

                'offcut_rate_per_sqft' => $rollOverride->offcut_rate_per_sqft !== null
                    ? (string) $rollOverride->offcut_rate_per_sqft
                    : $baseRates['offcut_rate_per_sqft'],

                'min_charge' => $rollOverride->min_charge !== null
                    ? (string) $rollOverride->min_charge
                    : $baseRates['min_charge'],
            ];
        } catch (\Throwable $e) {
            Log::error('PricingResolverService@rollRates error', [
                'pricing_id' => $rp->effectivePricing->id ?? null,
                'roll_id' => $rollId,
                'error' => $e->getMessage(),
            ]);

            return [
                'rate_per_sqft' => null,
                'offcut_rate_per_sqft' => null,
                'min_charge' => null,
            ];
        }
    }

    /**
     * Return the chosen roll override record (for admin UI).
     */
    public function rollOverrideRow(ResolvedPricing $rp, int $rollId)
    {
        $base = $this->pickBaseSource($rp);
        if (! $base) {
            return null;
        }

        $base->loadMissing('rollPricings');
        $row = $base->rollPricings->firstWhere('roll_id', $rollId);

        return ($row && $row->is_active) ? $row : null;
    }

    // -------------------------
    // Internals
    // -------------------------

    private function pickBaseSource(ResolvedPricing $rp): ?ProductPricing
    {
        // If WG pricing exists AND it wants to override base => use WG pricing
        if ($rp->workingGroupPricing && (bool) $rp->workingGroupPricing->override_base === true) {
            return $rp->workingGroupPricing;
        }

        // Otherwise use public if available, else fall back to WG pricing
        return $rp->publicPricing ?: $rp->workingGroupPricing;
    }

    private function pickVariantSource(ResolvedPricing $rp): ?ProductPricing
    {
        if ($rp->workingGroupPricing && (bool) $rp->workingGroupPricing->override_variants === true) {
            return $rp->workingGroupPricing;
        }

        return $rp->publicPricing ?: $rp->workingGroupPricing;
    }

    private function pickFinishingSource(ResolvedPricing $rp): ?ProductPricing
    {
        if ($rp->workingGroupPricing && (bool) $rp->workingGroupPricing->override_finishings === true) {
            return $rp->workingGroupPricing;
        }

        return $rp->publicPricing ?: $rp->workingGroupPricing;
    }

    private function tierPriceForQty(ProductPricing $pricing, int $qty): ?string
    {
        // tiers() relation is ordered in model, but we safely handle anyway
        $pricing->loadMissing('tiers');
        $tiers = $pricing->tiers;

        if (! $tiers || $tiers->isEmpty()) {
            return null;
        }

        $tier = $tiers->first(function ($t) use ($qty) {
            $min = (int) $t->min_qty;
            $max = $t->max_qty !== null ? (int) $t->max_qty : null;

            if ($qty < $min) {
                return false;
            }
            if ($max === null) {
                return true;
            }

            return $qty <= $max;
        });

        return $tier?->price !== null ? (string) $tier->price : null;
    }

    private function pricingRelations(): array
    {
        return ['tiers', 'variantPricings', 'finishingPricings', 'rollPricings.roll'];
    }
}

<?php

namespace App\Services\Pricing;

use App\Models\Product;
use App\Models\WorkingGroup;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class VariantAvailabilityResolverService
{
    /**
     * Determine if a variant_set is enabled for a given product + working group.
     * Fallback logic:
     * - If no override row exists => enabled (default)
     * - If override exists => obey is_enabled
     */
    public function isVariantSetEnabled(
        Product $product,
        int $variantSetId,
        WorkingGroup|int|null $workingGroup = null
    ): bool {
        try {
            $wgId = $workingGroup instanceof WorkingGroup
                ? $workingGroup->id
                : (is_int($workingGroup) ? $workingGroup : null);

            if (! $wgId) {
                // No working group context => treat as public => enabled by default
                return true;
            }

            // Defensive: ensure this variant set belongs to the product
            $belongs = DB::table('product_variant_sets')
                ->where('id', $variantSetId)
                ->where('product_id', $product->id)
                ->exists();

            if (! $belongs) {
                return false;
            }

            $row = DB::table('product_variant_availability_overrides')
                ->where('variant_set_id', $variantSetId)
                ->where('working_group_id', $wgId)
                ->select('is_enabled')
                ->first();

            if (! $row) {
                return true;
            }

            return (bool) $row->is_enabled;
        } catch (\Throwable $e) {
            Log::error('VariantAvailabilityResolverService@isVariantSetEnabled error', [
                'product_id' => $product->id ?? null,
                'variant_set_id' => $variantSetId,
                'working_group_id' => $workingGroup instanceof WorkingGroup ? $workingGroup->id : $workingGroup,
                'error' => $e->getMessage(),
            ]);

            // Fail-open is safer for UX, but fail-closed is safer for pricing/security.
            // For ordering/estimates, fail-closed prevents ordering blocked variants if DB errors happen.
            return false;
        }
    }

    /**
     * Filter enabled variant_set_ids in bulk.
     * This is the method you'll use for product screens and estimate UIs.
     */
    public function filterEnabledVariantSetIds(
        Product $product,
        array $variantSetIds,
        WorkingGroup|int|null $workingGroup = null
    ): array {
        try {
            $wgId = $workingGroup instanceof WorkingGroup
                ? $workingGroup->id
                : (is_int($workingGroup) ? $workingGroup : null);

            if (! $wgId || empty($variantSetIds)) {
                return $variantSetIds;
            }

            $variantSetIds = DB::table('product_variant_sets')
                ->where('product_id', $product->id)
                ->whereIn('id', $variantSetIds)
                ->pluck('id')
                ->map(fn ($id) => (int) $id)
                ->all();

            if (empty($variantSetIds)) {
                return [];
            }

            $disabledIds = DB::table('product_variant_availability_overrides')
                ->where('working_group_id', $wgId)
                ->whereIn('variant_set_id', $variantSetIds)
                ->where('is_enabled', 0)
                ->pluck('variant_set_id')
                ->all();

            if (empty($disabledIds)) {
                return $variantSetIds;
            }

            $disabledLookup = array_flip($disabledIds);

            return array_values(array_filter($variantSetIds, fn ($id) => ! isset($disabledLookup[$id])));
        } catch (\Throwable $e) {
            Log::error('VariantAvailabilityResolverService@filterEnabledVariantSetIds error', [
                'product_id' => $product->id ?? null,
                'working_group_id' => $workingGroup instanceof WorkingGroup ? $workingGroup->id : $workingGroup,
                'count' => count($variantSetIds),
                'error' => $e->getMessage(),
            ]);

            // Safer to block than leak a disabled variant.
            return [];
        }
    }

    /**
     * Return a map [variant_set_id => bool enabled].
     * Useful for admin UI grids (toggle switches).
     */
    public function enabledMap(
        Product $product,
        array $variantSetIds,
        WorkingGroup|int $workingGroup
    ): array {
        try {
            $wgId = $workingGroup instanceof WorkingGroup ? $workingGroup->id : $workingGroup;

            $map = [];
            foreach ($variantSetIds as $id) {
                $map[(int) $id] = true;
            }

            if (empty($variantSetIds)) {
                return $map;
            }

            $rows = DB::table('product_variant_availability_overrides')
                ->where('working_group_id', $wgId)
                ->whereIn('variant_set_id', $variantSetIds)
                ->select('variant_set_id', 'is_enabled')
                ->get();

            foreach ($rows as $row) {
                $map[(int) $row->variant_set_id] = (bool) $row->is_enabled;
            }

            return $map;
        } catch (\Throwable $e) {
            Log::error('VariantAvailabilityResolverService@enabledMap error', [
                'product_id' => $product->id ?? null,
                'working_group_id' => $workingGroup instanceof WorkingGroup ? $workingGroup->id : $workingGroup,
                'error' => $e->getMessage(),
            ]);

            return [];
        }
    }
}

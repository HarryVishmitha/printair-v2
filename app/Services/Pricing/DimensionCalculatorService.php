<?php

namespace App\Services\Pricing;

use Illuminate\Support\Facades\Log;

class DimensionCalculatorService
{
    /**
     * Convert a value from a unit to inches.
     * Supported units: in, ft, mm, cm, m
     */
    public function toInches(float $value, string $unit): float
    {
        $unit = strtolower(trim($unit));

        return match ($unit) {
            'in', 'inch', 'inches' => $value,
            'ft', 'feet' => $value * 12.0,
            'mm' => $value / 25.4,
            'cm' => $value / 2.54,
            'm', 'meter', 'metre' => ($value * 100.0) / 2.54,
            default => throw new \InvalidArgumentException("Unsupported unit: {$unit}"),
        };
    }

    /**
     * Convert inches to feet.
     */
    public function inchesToFeet(float $inches): float
    {
        return $inches / 12.0;
    }

    /**
     * Calculate area in sq.ft from inches.
     */
    public function areaSqFtFromInches(float $widthIn, float $heightIn): float
    {
        $wFt = $this->inchesToFeet($widthIn);
        $hFt = $this->inchesToFeet($heightIn);

        return $wFt * $hFt;
    }

    /**
     * Decide whether we should rotate to fit roll width.
     *
     * Example:
     * - Banner requested: 12ft (144in) wide x 10ft (120in) high
     * - Roll max width: 10ft (120in)
     * - If rotation allowed => rotate to 120in wide x 144in high (fits roll width)
     */
    public function fitToRoll(float $widthIn, float $heightIn, float $rollMaxWidthIn, bool $allowRotate = true): array
    {
        try {
            $fitsNormal = $widthIn <= $rollMaxWidthIn;
            $fitsRotated = $allowRotate && ($heightIn <= $rollMaxWidthIn);

            if ($fitsNormal) {
                return [
                    'fits' => true,
                    'rotated' => false,
                    'final_width_in' => $widthIn,
                    'final_height_in' => $heightIn,
                ];
            }

            if ($fitsRotated) {
                return [
                    'fits' => true,
                    'rotated' => true,
                    'final_width_in' => $heightIn,
                    'final_height_in' => $widthIn,
                ];
            }

            return [
                'fits' => false,
                'rotated' => false,
                'final_width_in' => $widthIn,
                'final_height_in' => $heightIn,
            ];
        } catch (\Throwable $e) {
            Log::error('DimensionCalculatorService@fitToRoll error', [
                'width_in' => $widthIn,
                'height_in' => $heightIn,
                'roll_max_width_in' => $rollMaxWidthIn,
                'allow_rotate' => $allowRotate,
                'error' => $e->getMessage(),
            ]);

            return [
                'fits' => false,
                'rotated' => false,
                'final_width_in' => $widthIn,
                'final_height_in' => $heightIn,
            ];
        }
    }

    /**
     * Offcut area concept (common in printing):
     * If roll is fixed width, and user prints smaller width,
     * offcut is wasted area based on (roll_width - used_width) * height.
     *
     * All in inches; returns sq.ft.
     */
    public function offcutSqFt(float $usedWidthIn, float $heightIn, float $rollWidthIn): float
    {
        $wasteWidth = max(0.0, $rollWidthIn - $usedWidthIn);

        return $this->areaSqFtFromInches($wasteWidth, $heightIn);
    }

    /**
     * Calculate dimension-based pricing totals using rates.
     * - area charge = areaSqft * rate
     * - offcut charge = offcutSqft * offcutRate (optional)
     * - apply min_charge if provided
     *
     * Returns amounts as float; you can format in UI.
     */
    public function calculateDimensionPrice(
        float $widthIn,
        float $heightIn,
        float $qty,
        float $ratePerSqft,
        ?float $offcutRatePerSqft = null,
        ?float $minCharge = null,
        ?float $rollWidthIn = null
    ): array {
        $areaSqft = $this->areaSqFtFromInches($widthIn, $heightIn);

        $offcutSqft = 0.0;
        if ($rollWidthIn !== null) {
            $offcutSqft = $this->offcutSqFt($widthIn, $heightIn, $rollWidthIn);
        }

        $areaCharge = $areaSqft * $ratePerSqft * $qty;

        $offcutCharge = 0.0;
        if ($offcutRatePerSqft !== null) {
            $offcutCharge = $offcutSqft * $offcutRatePerSqft * $qty;
        }

        $subtotal = $areaCharge + $offcutCharge;

        if ($minCharge !== null && $subtotal < $minCharge) {
            $subtotal = $minCharge;
        }

        return [
            'area_sqft' => $areaSqft,
            'offcut_sqft' => $offcutSqft,
            'area_charge' => $areaCharge,
            'offcut_charge' => $offcutCharge,
            'total' => $subtotal,
        ];
    }
}


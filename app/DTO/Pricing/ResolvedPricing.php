<?php

namespace App\DTO\Pricing;

use App\Models\ProductPricing;

class ResolvedPricing
{
    public function __construct(
        public ProductPricing $effectivePricing,   // The pricing row we finally use (WG or Public)
        public ?ProductPricing $publicPricing,      // Public pricing row (if exists)
        public ?ProductPricing $workingGroupPricing,// WG pricing row (if exists)
        public bool $usingWorkingGroupOverride,     // true if WG is the effective source
        public array $meta = []                     // any debug / extra info
    ) {
    }
}


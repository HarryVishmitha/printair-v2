<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class ProductSeeder extends Seeder
{
    use WithoutModelEvents;

    public function run(): void
    {
        // This seeder is meant for local/dev/demo catalogs.
        // Keep it explicit: `php artisan db:seed --class=ProductSeeder`
        if (app()->environment('production')) {
            $this->command?->warn('Skipping ProductSeeder in production environment.');

            return;
        }

        $now = now();

        // -------------------------
        // Categories
        // -------------------------
        $categories = [
            [
                'name' => 'Banners',
                'slug' => 'banners',
                'code' => 'BANNERS',
                'short_description' => 'Outdoor + indoor banners for every use case.',
                'description' => 'Flex, vinyl, and promotional banners with fast turnaround.',
                'sort_order' => 10,
                'is_active' => true,
                'show_in_menu' => true,
                'show_in_navbar' => true,
            ],
            [
                'name' => 'Stickers',
                'slug' => 'stickers',
                'code' => 'STICKERS',
                'short_description' => 'Custom stickers for branding, packaging, and events.',
                'description' => 'Die-cut and sheet stickers in multiple materials.',
                'sort_order' => 20,
                'is_active' => true,
                'show_in_menu' => true,
                'show_in_navbar' => true,
            ],
            [
                'name' => 'Business Essentials',
                'slug' => 'business-essentials',
                'code' => 'BIZ',
                'short_description' => 'Cards, flyers, and other everyday prints.',
                'description' => 'High quality prints for day-to-day business needs.',
                'sort_order' => 30,
                'is_active' => true,
                'show_in_menu' => true,
                'show_in_navbar' => true,
            ],
        ];

        foreach ($categories as $cat) {
            DB::table('categories')->updateOrInsert(
                [
                    'working_group_id' => null,
                    'slug' => $cat['slug'],
                ],
                [
                    ...$cat,
                    'working_group_id' => null,
                    'parent_id' => null,
                    'is_featured' => false,
                    'is_indexable' => true,
                    'meta' => null,
                    'settings' => null,
                    'created_by' => null,
                    'updated_by' => null,
                    'deleted_at' => null,
                    'updated_at' => $now,
                    'created_at' => $now,
                ]
            );
        }

        $categoryIdsBySlug = DB::table('categories')
            ->whereNull('working_group_id')
            ->whereIn('slug', collect($categories)->pluck('slug')->all())
            ->pluck('id', 'slug');

        // -------------------------
        // Rolls (for dimension-based quoting)
        // -------------------------
        $rolls = [
            ['name' => 'Flex 4ft', 'slug' => 'flex-4ft', 'material_type' => 'flex', 'width_in' => 48.000],
            ['name' => 'Flex 5ft', 'slug' => 'flex-5ft', 'material_type' => 'flex', 'width_in' => 60.000],
            ['name' => 'Sticker 4ft', 'slug' => 'sticker-4ft', 'material_type' => 'sticker', 'width_in' => 48.000],
        ];

        foreach ($rolls as $r) {
            DB::table('rolls')->updateOrInsert(
                ['slug' => $r['slug']],
                [
                    ...$r,
                    'is_active' => true,
                    'meta' => null,
                    'created_by' => null,
                    'updated_by' => null,
                    'deleted_at' => null,
                    'updated_at' => $now,
                    'created_at' => $now,
                ]
            );
        }

        $rollIdsBySlug = DB::table('rolls')
            ->whereIn('slug', collect($rolls)->pluck('slug')->all())
            ->pluck('id', 'slug');

        // -------------------------
        // Products
        // -------------------------
        $products = [
            [
                'product_code' => 'VINYL-BANNER',
                'name' => 'Vinyl Banner',
                'slug' => 'vinyl-banner',
                'category_slug' => 'banners',
                'product_type' => 'dimension_based',
                'status' => 'active',
                'visibility' => 'public',
                'short_description' => 'Durable vinyl banner printing (priced by sqft).',
                'description' => 'High quality vinyl banners suitable for indoor/outdoor promotions.',
                'requires_dimensions' => true,
                'allow_custom_size' => true,
                'allow_predefined_sizes' => false,
                'allow_rotation_to_fit_roll' => true,
                'min_width_in' => 12.000,
                'max_width_in' => 600.000,
                'min_height_in' => 12.000,
                'max_height_in' => 600.000,
                'roll_max_width_in' => 60.000,
                'min_qty' => 1,
            ],
            [
                'product_code' => 'BUSINESS-CARDS',
                'name' => 'Business Cards',
                'slug' => 'business-cards',
                'category_slug' => 'business-essentials',
                'product_type' => 'standard',
                'status' => 'active',
                'visibility' => 'public',
                'short_description' => 'Premium business cards with optional lamination.',
                'description' => 'Full color business cards, multiple finishes available.',
                'requires_dimensions' => false,
                'allow_custom_size' => false,
                'allow_predefined_sizes' => false,
                'allow_rotation_to_fit_roll' => true,
                'min_qty' => 100,
            ],
            [
                'product_code' => 'DESIGN-SERVICE',
                'name' => 'Design Service',
                'slug' => 'design-service',
                'category_slug' => 'business-essentials',
                'product_type' => 'service',
                'status' => 'active',
                'visibility' => 'public',
                'short_description' => 'Need artwork? Our designers can help.',
                'description' => 'Hire our designers for custom artwork and layout.',
                'allow_manual_pricing' => true,
            ],
            [
                'product_code' => 'EYELETS',
                'name' => 'Eyelets (Finishing)',
                'slug' => 'eyelets-finishing',
                'category_slug' => 'banners',
                'product_type' => 'finishing',
                'status' => 'active',
                'visibility' => 'internal',
                'short_description' => 'Metal eyelets for banners.',
                'description' => 'Per-piece finishing (internal only).',
                'finishing_charge_mode' => 'per_piece',
                'finishing_min_qty' => 1,
            ],
        ];

        foreach ($products as $p) {
            $slug = $p['slug'] ?? Str::slug((string) ($p['name'] ?? ''));
            $categoryId = $p['category_slug'] ? ($categoryIdsBySlug[$p['category_slug']] ?? null) : null;

            DB::table('products')->updateOrInsert(
                ['product_code' => $p['product_code']],
                [
                    'category_id' => $categoryId,
                    'product_code' => $p['product_code'],
                    'name' => $p['name'],
                    'slug' => $slug,
                    'product_type' => $p['product_type'],
                    'status' => $p['status'],
                    'visibility' => $p['visibility'],
                    'short_description' => $p['short_description'] ?? null,
                    'description' => $p['description'] ?? null,
                    'min_qty' => $p['min_qty'] ?? null,
                    'requires_dimensions' => (bool) ($p['requires_dimensions'] ?? false),
                    'allow_custom_size' => (bool) ($p['allow_custom_size'] ?? false),
                    'allow_predefined_sizes' => (bool) ($p['allow_predefined_sizes'] ?? false),
                    'allow_rotation_to_fit_roll' => (bool) ($p['allow_rotation_to_fit_roll'] ?? true),
                    'min_width_in' => $p['min_width_in'] ?? null,
                    'max_width_in' => $p['max_width_in'] ?? null,
                    'min_height_in' => $p['min_height_in'] ?? null,
                    'max_height_in' => $p['max_height_in'] ?? null,
                    'roll_max_width_in' => $p['roll_max_width_in'] ?? null,
                    'finishing_charge_mode' => $p['finishing_charge_mode'] ?? null,
                    'finishing_min_qty' => $p['finishing_min_qty'] ?? null,
                    'finishing_max_qty' => $p['finishing_max_qty'] ?? null,
                    'allow_manual_pricing' => (bool) ($p['allow_manual_pricing'] ?? false),
                    'meta' => null,
                    'created_by' => null,
                    'updated_by' => null,
                    'deleted_at' => null,
                    'updated_at' => $now,
                    'created_at' => $now,
                ]
            );
        }

        $productIdsByCode = DB::table('products')
            ->whereIn('product_code', collect($products)->pluck('product_code')->all())
            ->pluck('id', 'product_code');

        // -------------------------
        // Public pricing (one per product)
        // -------------------------
        $pricingByCode = [
            'VINYL-BANNER' => [
                'base_price' => null,
                'rate_per_sqft' => 350.0000,
                'offcut_rate_per_sqft' => 100.0000,
                'min_charge' => 2000.00,
            ],
            'BUSINESS-CARDS' => [
                'base_price' => 1500.00, // unit/tier base (per "qty=1" base; UI can add tiers later)
                'rate_per_sqft' => null,
                'offcut_rate_per_sqft' => null,
                'min_charge' => null,
            ],
            'DESIGN-SERVICE' => [
                'base_price' => 5000.00,
                'rate_per_sqft' => null,
                'offcut_rate_per_sqft' => null,
                'min_charge' => null,
            ],
            'EYELETS' => [
                'base_price' => 50.00,
                'rate_per_sqft' => null,
                'offcut_rate_per_sqft' => null,
                'min_charge' => null,
            ],
        ];

        $pricingIdsByProductId = collect();
        foreach ($pricingByCode as $productCode => $price) {
            $productId = $productIdsByCode[$productCode] ?? null;
            if (! $productId) {
                continue;
            }

            DB::table('product_pricings')->updateOrInsert(
                [
                    'product_id' => $productId,
                    'context' => 'public',
                    'working_group_id' => null,
                ],
                [
                    'product_id' => $productId,
                    'context' => 'public',
                    'working_group_id' => null,
                    'override_base' => false,
                    'override_variants' => false,
                    'override_finishings' => false,
                    'base_price' => $price['base_price'],
                    'rate_per_sqft' => $price['rate_per_sqft'],
                    'offcut_rate_per_sqft' => $price['offcut_rate_per_sqft'],
                    'min_charge' => $price['min_charge'],
                    'is_active' => true,
                    'created_by' => null,
                    'updated_by' => null,
                    'deleted_at' => null,
                    'updated_at' => $now,
                    'created_at' => $now,
                ]
            );

            $pricingId = DB::table('product_pricings')
                ->where('product_id', $productId)
                ->where('context', 'public')
                ->whereNull('working_group_id')
                ->value('id');

            if ($pricingId) {
                $pricingIdsByProductId->put((int) $productId, (int) $pricingId);
            }
        }

        // -------------------------
        // Attach rolls to VINYL-BANNER
        // -------------------------
        $vinylId = $productIdsByCode['VINYL-BANNER'] ?? null;
        if ($vinylId) {
            foreach (['flex-4ft', 'flex-5ft'] as $rollSlug) {
                $rollId = $rollIdsBySlug[$rollSlug] ?? null;
                if (! $rollId) {
                    continue;
                }

                DB::table('product_rolls')->updateOrInsert(
                    [
                        'product_id' => $vinylId,
                        'roll_id' => $rollId,
                    ],
                    [
                        'product_id' => $vinylId,
                        'roll_id' => $rollId,
                        'is_active' => true,
                        'min_height_in' => null,
                        'max_height_in' => null,
                        'meta' => null,
                        'created_by' => null,
                        'updated_by' => null,
                        'deleted_at' => null,
                        'updated_at' => $now,
                        'created_at' => $now,
                    ]
                );
            }
        }

        // -------------------------
        // Variants for BUSINESS-CARDS (options + variant sets + pricing add-ons)
        // -------------------------
        $bizCardId = $productIdsByCode['BUSINESS-CARDS'] ?? null;
        $bizPricingId = $bizCardId ? ($pricingIdsByProductId->get((int) $bizCardId) ?: null) : null;

        if ($bizCardId && $bizPricingId) {
            // Option groups
            $optionGroups = [
                ['code' => 'SIZE', 'name' => 'Size', 'description' => 'Card size'],
                ['code' => 'LAMINATION', 'name' => 'Lamination', 'description' => 'Optional lamination'],
            ];

            foreach ($optionGroups as $og) {
                DB::table('option_groups')->updateOrInsert(
                    ['code' => $og['code']],
                    [
                        ...$og,
                        'updated_at' => $now,
                        'created_at' => $now,
                    ]
                );
            }

            $ogIds = DB::table('option_groups')
                ->whereIn('code', collect($optionGroups)->pluck('code')->all())
                ->pluck('id', 'code');

            // Options
            $options = [
                'SIZE' => [
                    ['code' => 'standard', 'label' => 'Standard (3.5 × 2 in)'],
                    ['code' => 'square', 'label' => 'Square (2.5 × 2.5 in)'],
                ],
                'LAMINATION' => [
                    ['code' => 'none', 'label' => 'None'],
                    ['code' => 'matte', 'label' => 'Matte'],
                    ['code' => 'glossy', 'label' => 'Glossy'],
                ],
            ];

            $optionIds = collect();
            foreach ($options as $ogCode => $rows) {
                $ogId = $ogIds[$ogCode] ?? null;
                if (! $ogId) {
                    continue;
                }

                foreach ($rows as $row) {
                    DB::table('options')->updateOrInsert(
                        [
                            'option_group_id' => $ogId,
                            'code' => $row['code'],
                        ],
                        [
                            'option_group_id' => $ogId,
                            'code' => $row['code'],
                            'label' => $row['label'],
                            'meta' => null,
                            'updated_at' => $now,
                            'created_at' => $now,
                        ]
                    );

                    $id = DB::table('options')
                        ->where('option_group_id', $ogId)
                        ->where('code', $row['code'])
                        ->value('id');

                    if ($id) {
                        $optionIds->put($ogCode.':'.$row['code'], (int) $id);
                    }
                }
            }

            // Attach option groups to product
            $sortIndex = 1;
            foreach ($ogIds as $ogCode => $ogId) {
                DB::table('product_option_groups')->updateOrInsert(
                    [
                        'product_id' => $bizCardId,
                        'option_group_id' => $ogId,
                    ],
                    [
                        'product_id' => $bizCardId,
                        'option_group_id' => $ogId,
                        'is_required' => true,
                        'sort_index' => $sortIndex++,
                        'created_by' => null,
                        'updated_by' => null,
                        'deleted_at' => null,
                        'updated_at' => $now,
                        'created_at' => $now,
                    ]
                );
            }

            // Attach options to product
            $sortIndex = 1;
            foreach ($optionIds->values() as $oid) {
                DB::table('product_options')->updateOrInsert(
                    [
                        'product_id' => $bizCardId,
                        'option_id' => $oid,
                    ],
                    [
                        'product_id' => $bizCardId,
                        'option_id' => $oid,
                        'is_active' => true,
                        'sort_index' => $sortIndex++,
                        'created_by' => null,
                        'updated_by' => null,
                        'deleted_at' => null,
                        'updated_at' => $now,
                        'created_at' => $now,
                    ]
                );
            }

            // Variant sets = combinations (SIZE x LAMINATION)
            $sizeCodes = ['standard', 'square'];
            $lamCodes = ['none', 'matte', 'glossy'];

            foreach ($sizeCodes as $sz) {
                foreach ($lamCodes as $lm) {
                    $setCode = 'size:'.$sz.'|lamination:'.$lm;

                    DB::table('product_variant_sets')->updateOrInsert(
                        [
                            'product_id' => $bizCardId,
                            'code' => $setCode,
                        ],
                        [
                            'product_id' => $bizCardId,
                            'code' => $setCode,
                            'is_active' => true,
                            'created_by' => null,
                            'updated_by' => null,
                            'deleted_at' => null,
                            'updated_at' => $now,
                            'created_at' => $now,
                        ]
                    );

                    $variantSetId = DB::table('product_variant_sets')
                        ->where('product_id', $bizCardId)
                        ->where('code', $setCode)
                        ->value('id');

                    if (! $variantSetId) {
                        continue;
                    }

                    $sizeOptionId = $optionIds->get('SIZE:'.$sz);
                    $lamOptionId = $optionIds->get('LAMINATION:'.$lm);

                    foreach (array_filter([$sizeOptionId, $lamOptionId]) as $optId) {
                        DB::table('product_variant_set_items')->insertOrIgnore([
                            'variant_set_id' => (int) $variantSetId,
                            'option_id' => (int) $optId,
                        ]);
                    }

                    // Simple add-ons for lamination (added on top of base)
                    $lamAddon = match ($lm) {
                        'matte' => 300.00,
                        'glossy' => 300.00,
                        default => 0.00,
                    };

                    // Square cards cost slightly more
                    $sizeAddon = $sz === 'square' ? 250.00 : 0.00;

                    $addon = $lamAddon + $sizeAddon;
                    if ($addon > 0) {
                        DB::table('product_variant_pricings')->updateOrInsert(
                            [
                                'product_pricing_id' => $bizPricingId,
                                'variant_set_id' => (int) $variantSetId,
                            ],
                            [
                                'product_pricing_id' => $bizPricingId,
                                'variant_set_id' => (int) $variantSetId,
                                'fixed_price' => $addon,
                                'rate_per_sqft' => null,
                                'offcut_rate_per_sqft' => null,
                                'min_charge' => null,
                                'is_active' => true,
                                'created_by' => null,
                                'updated_by' => null,
                                'deleted_at' => null,
                                'updated_at' => $now,
                                'created_at' => $now,
                            ]
                        );
                    }
                }
            }
        }

        // -------------------------
        // Finishing link + pricing (VINYL-BANNER -> EYELETS)
        // -------------------------
        $eyeletsId = $productIdsByCode['EYELETS'] ?? null;
        $vinylPricingId = $vinylId ? ($pricingIdsByProductId->get((int) $vinylId) ?: null) : null;

        if ($vinylId && $eyeletsId && $vinylPricingId) {
            DB::table('product_finishing_links')->updateOrInsert(
                [
                    'product_id' => $vinylId,
                    'finishing_product_id' => $eyeletsId,
                ],
                [
                    'product_id' => $vinylId,
                    'finishing_product_id' => $eyeletsId,
                    'is_required' => false,
                    'default_qty' => null,
                    'min_qty' => 0,
                    'max_qty' => null,
                    'is_active' => true,
                    'created_by' => null,
                    'updated_by' => null,
                    'deleted_at' => null,
                    'updated_at' => $now,
                    'created_at' => $now,
                ]
            );

            DB::table('product_finishing_pricings')->updateOrInsert(
                [
                    'product_pricing_id' => $vinylPricingId,
                    'finishing_product_id' => $eyeletsId,
                ],
                [
                    'product_pricing_id' => $vinylPricingId,
                    'finishing_product_id' => $eyeletsId,
                    'price_per_piece' => 50.00,
                    'price_per_side' => null,
                    'flat_price' => null,
                    'min_qty' => null,
                    'max_qty' => null,
                    'is_active' => true,
                    'created_by' => null,
                    'updated_by' => null,
                    'deleted_at' => null,
                    'updated_at' => $now,
                    'created_at' => $now,
                ]
            );
        }

        $this->command?->info('ProductSeeder: categories/products/pricing seeded (dev/demo catalog).');
    }
}


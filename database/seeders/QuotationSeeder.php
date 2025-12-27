<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class QuotationSeeder extends Seeder
{
    use WithoutModelEvents;

    public function run(): void
    {
        // Explicit dev/demo seed only:
        // `php artisan db:seed --class=QuotationSeeder`
        if (app()->environment('production')) {
            $this->command?->warn('Skipping QuotationSeeder in production environment.');
            return;
        }

        // Ensure baseline data exists for foreign keys.
        $this->call([
            RoleSeeder::class,
            WorkingGroupSeeder::class,
            SuperAdminSeeder::class,
            ProductSeeder::class,
        ]);

        $now = now();

        $wgId = (int) DB::table('working_groups')->where('slug', 'public')->value('id');
        if ($wgId <= 0) {
            $this->command?->error('Public working group not found.');
            return;
        }

        $userId = (int) DB::table('users')->where('email', 'superadmin@printair.com')->value('id');
        if ($userId <= 0) {
            $userId = (int) DB::table('users')->value('id');
        }

        if ($userId <= 0) {
            $roleId = (int) DB::table('roles')->where('name', 'Super Admin')->value('id');

            DB::table('users')->insert([
                'first_name' => 'Dev',
                'last_name' => 'Admin',
                'name' => 'Dev Admin',
                'email' => 'devadmin@printair.com',
                'password' => Hash::make('password'),
                'role_id' => $roleId ?: null,
                'working_group_id' => $wgId,
                'status' => 'active',
                'email_verified_at' => $now,
                'created_at' => $now,
                'updated_at' => $now,
            ]);

            $userId = (int) DB::table('users')->where('email', 'devadmin@printair.com')->value('id');
        }

        if ($userId <= 0) {
            $this->command?->error('No users found/created to attach estimates to (created_by).');
            return;
        }

        // Customers (public WG)
        $customers = [
            [
                'customer_code' => 'CUST-DEV-0001',
                'full_name' => 'Walk-in Customer',
                'email' => 'walkin@example.com',
                'phone' => '+94 77 123 4567',
                'whatsapp_number' => '+94 77 123 4567',
                'type' => 'walk_in',
                'status' => 'active',
                'notes' => 'Seeded dev customer',
            ],
            [
                'customer_code' => 'CUST-DEV-0002',
                'full_name' => 'Acme Trading (Pvt) Ltd',
                'email' => 'purchasing@acme.example',
                'phone' => '+94 11 234 5678',
                'whatsapp_number' => '+94 71 987 6543',
                'company_name' => 'Acme Trading (Pvt) Ltd',
                'company_phone' => '+94 11 234 5678',
                'company_reg_no' => 'PV-12345',
                'type' => 'corporate',
                'status' => 'active',
                'notes' => 'Seeded dev corporate customer',
            ],
        ];

        foreach ($customers as $c) {
            DB::table('customers')->updateOrInsert(
                ['customer_code' => $c['customer_code']],
                [
                    'user_id' => null,
                    'working_group_id' => $wgId,
                    'customer_code' => $c['customer_code'],
                    'full_name' => $c['full_name'],
                    'email' => $c['email'] ?? null,
                    'phone' => $c['phone'],
                    'whatsapp_number' => $c['whatsapp_number'] ?? null,
                    'company_name' => $c['company_name'] ?? null,
                    'company_phone' => $c['company_phone'] ?? null,
                    'company_reg_no' => $c['company_reg_no'] ?? null,
                    'type' => $c['type'] ?? 'walk_in',
                    'status' => $c['status'] ?? 'active',
                    'email_notifications' => true,
                    'sms_notifications' => false,
                    'notes' => $c['notes'] ?? null,
                    'deleted_at' => null,
                    'updated_at' => $now,
                    'created_at' => $now,
                ]
            );
        }

        $customerIdsByCode = DB::table('customers')
            ->whereIn('customer_code', collect($customers)->pluck('customer_code')->all())
            ->pluck('id', 'customer_code');

        // Products seeded by ProductSeeder (fallback to any active product if missing)
        $productsByCode = DB::table('products')
            ->whereIn('product_code', ['VINYL-BANNER', 'BUSINESS-CARDS', 'DESIGN-SERVICE'])
            ->pluck('id', 'product_code');

        $vinylId = (int) ($productsByCode['VINYL-BANNER'] ?? 0);
        $cardsId = (int) ($productsByCode['BUSINESS-CARDS'] ?? 0);
        $designId = (int) ($productsByCode['DESIGN-SERVICE'] ?? 0);

        if ($vinylId <= 0) {
            $vinylId = (int) DB::table('products')
                ->where('status', 'active')
                ->where('product_type', 'dimension_based')
                ->value('id');
        }
        if ($cardsId <= 0) {
            $cardsId = (int) DB::table('products')
                ->where('status', 'active')
                ->where('product_type', 'standard')
                ->value('id');
        }
        if ($designId <= 0) {
            $designId = (int) DB::table('products')
                ->where('status', 'active')
                ->where('product_type', 'service')
                ->value('id');
        }

        $rollId = (int) DB::table('rolls')->where('slug', 'flex-5ft')->value('id');
        if ($rollId <= 0) {
            $rollId = (int) DB::table('rolls')->value('id');
        }

        $seedEstimates = [
            [
                'estimate_no' => 'EST-DEV-0001',
                'status' => 'draft',
                'customer_code' => 'CUST-DEV-0001',
                'valid_until' => now()->addDays(14)->endOfDay(),
                'notes_internal' => 'Dev seed: draft estimate',
                'items' => [
                    [
                        'product_id' => $vinylId,
                        'title' => 'Vinyl Banner (Large Format)',
                        'description' => '96 × 36 inches, full color, hems + eyelets (pricing sample).',
                        'qty' => 2,
                        'width' => 96.000,
                        'height' => 36.000,
                        'unit' => 'in',
                        'area_sqft' => 24.0000,
                        'offcut_sqft' => 0.0000,
                        'roll_id' => $rollId ?: null,
                        'unit_price' => 8400.00,
                        'line_subtotal' => 16800.00,
                        'discount_amount' => 0.00,
                        'tax_amount' => 0.00,
                        'line_total' => 16800.00,
                        'pricing_snapshot' => [
                            'source' => 'seed',
                            'mode' => 'dimension',
                            'breakdown' => [
                                ['label' => 'Base', 'amount' => 16800.00],
                            ],
                            'roll' => $rollId ? ['id' => $rollId, 'auto' => false, 'rotated' => false] : null,
                        ],
                    ],
                    [
                        'product_id' => $designId,
                        'title' => 'Artwork / Design Service',
                        'description' => 'Simple design + print-ready export.',
                        'qty' => 1,
                        'unit_price' => 5000.00,
                        'line_subtotal' => 5000.00,
                        'discount_amount' => 0.00,
                        'tax_amount' => 0.00,
                        'line_total' => 5000.00,
                        'pricing_snapshot' => [
                            'source' => 'seed',
                            'mode' => 'unit',
                            'breakdown' => [
                                ['label' => 'Base', 'amount' => 5000.00],
                            ],
                        ],
                    ],
                ],
            ],
            [
                'estimate_no' => 'EST-DEV-0002',
                'status' => 'sent',
                'sent_at' => now()->subDays(2),
                'customer_code' => 'CUST-DEV-0002',
                'valid_until' => now()->addDays(7)->endOfDay(),
                'notes_internal' => 'Dev seed: sent estimate',
                'items' => [
                    [
                        'product_id' => $cardsId,
                        'title' => 'Business Cards (1 set)',
                        'description' => 'Single set pricing example (qty represents sets).',
                        'qty' => 1,
                        'unit_price' => 1500.00,
                        'line_subtotal' => 1500.00,
                        'discount_amount' => 0.00,
                        'tax_amount' => 0.00,
                        'line_total' => 1500.00,
                        'pricing_snapshot' => [
                            'source' => 'seed',
                            'mode' => 'unit',
                            'breakdown' => [
                                ['label' => 'Base', 'amount' => 1500.00],
                            ],
                        ],
                    ],
                    [
                        'product_id' => $designId,
                        'title' => 'Artwork / Design Service',
                        'description' => 'Logo touch-up + layout.',
                        'qty' => 1,
                        'unit_price' => 5000.00,
                        'line_subtotal' => 5000.00,
                        'discount_amount' => 0.00,
                        'tax_amount' => 0.00,
                        'line_total' => 5000.00,
                        'pricing_snapshot' => [
                            'source' => 'seed',
                            'mode' => 'unit',
                            'breakdown' => [
                                ['label' => 'Base', 'amount' => 5000.00],
                            ],
                        ],
                    ],
                ],
            ],
            [
                'estimate_no' => 'EST-DEV-0003',
                'status' => 'accepted',
                'sent_at' => now()->subDays(10),
                'accepted_at' => now()->subDays(8),
                'customer_code' => 'CUST-DEV-0002',
                'valid_until' => now()->addDays(30)->endOfDay(),
                'notes_internal' => 'Dev seed: accepted estimate',
                'items' => [
                    [
                        'product_id' => $vinylId,
                        'title' => 'Vinyl Banner (Medium)',
                        'description' => '72 × 24 inches.',
                        'qty' => 1,
                        'width' => 72.000,
                        'height' => 24.000,
                        'unit' => 'in',
                        'area_sqft' => 12.0000,
                        'offcut_sqft' => 0.0000,
                        'roll_id' => $rollId ?: null,
                        'unit_price' => 4200.00,
                        'line_subtotal' => 4200.00,
                        'discount_amount' => 0.00,
                        'tax_amount' => 0.00,
                        'line_total' => 4200.00,
                        'pricing_snapshot' => [
                            'source' => 'seed',
                            'mode' => 'dimension',
                            'breakdown' => [
                                ['label' => 'Base', 'amount' => 4200.00],
                            ],
                            'roll' => $rollId ? ['id' => $rollId, 'auto' => true, 'rotated' => false] : null,
                        ],
                    ],
                ],
            ],
        ];

        foreach ($seedEstimates as $seed) {
            $estimateNo = (string) $seed['estimate_no'];

            $customerId = isset($seed['customer_code'])
                ? (int) ($customerIdsByCode[$seed['customer_code']] ?? 0)
                : 0;

            $uuid = (string) Str::uuid();

            DB::transaction(function () use ($seed, $estimateNo, $uuid, $customerId, $wgId, $userId, $now) {
                DB::table('estimates')->updateOrInsert(
                    [
                        'working_group_id' => $wgId,
                        'estimate_no' => $estimateNo,
                    ],
                    [
                        'uuid' => $uuid,
                        'estimate_no' => $estimateNo,
                        'working_group_id' => $wgId,
                        'customer_id' => $customerId > 0 ? $customerId : null,
                        'customer_snapshot' => null,
                        'currency' => 'LKR',
                        'price_tier_id' => null,
                        'subtotal' => 0,
                        'discount_total' => 0,
                        'tax_total' => 0,
                        'shipping_fee' => 0,
                        'other_fee' => 0,
                        'grand_total' => 0,
                        'tax_mode' => 'none',
                        'discount_mode' => 'none',
                        'discount_value' => 0,
                        'status' => $seed['status'],
                        'valid_until' => $seed['valid_until'] ?? now()->addDays(14)->endOfDay(),
                        'sent_at' => $seed['sent_at'] ?? null,
                        'accepted_at' => $seed['accepted_at'] ?? null,
                        'rejected_at' => null,
                        'converted_at' => null,
                        'locked_at' => in_array((string) $seed['status'], ['sent', 'viewed', 'accepted', 'converted'], true) ? ($seed['sent_at'] ?? $now) : null,
                        'locked_by' => in_array((string) $seed['status'], ['sent', 'viewed', 'accepted', 'converted'], true) ? $userId : null,
                        'revision' => 1,
                        'parent_estimate_id' => null,
                        'notes_internal' => $seed['notes_internal'] ?? null,
                        'notes_customer' => $seed['notes_customer'] ?? null,
                        'terms' => $seed['terms'] ?? null,
                        'meta' => isset($seed['meta']) && is_array($seed['meta'])
                            ? json_encode($seed['meta'])
                            : ($seed['meta'] ?? null),
                        'created_by' => $userId,
                        'updated_by' => $userId,
                        'deleted_at' => null,
                        'updated_at' => $now,
                        'created_at' => $now,
                    ]
                );

                $estimateId = (int) DB::table('estimates')
                    ->where('working_group_id', $wgId)
                    ->where('estimate_no', $estimateNo)
                    ->value('id');

                if ($estimateId <= 0) {
                    return;
                }

                // Reset existing children for idempotency
                $itemIds = DB::table('estimate_items')
                    ->where('estimate_id', $estimateId)
                    ->pluck('id')
                    ->all();

                if (! empty($itemIds)) {
                    DB::table('estimate_item_finishings')->whereIn('estimate_item_id', $itemIds)->delete();
                    DB::table('estimate_items')->whereIn('id', $itemIds)->delete();
                }

                DB::table('estimate_status_histories')->where('estimate_id', $estimateId)->delete();

                $shareIds = DB::table('estimate_shares')->where('estimate_id', $estimateId)->pluck('id')->all();
                if (! empty($shareIds)) {
                    DB::table('estimate_share_otps')->whereIn('estimate_share_id', $shareIds)->delete();
                    DB::table('estimate_shares')->whereIn('id', $shareIds)->delete();
                }

                $subtotal = 0.0;

                foreach ((array) ($seed['items'] ?? []) as $idx => $it) {
                    $lineSubtotal = (float) ($it['line_subtotal'] ?? 0);
                    $discount = (float) ($it['discount_amount'] ?? 0);
                    $tax = (float) ($it['tax_amount'] ?? 0);
                    $lineTotal = (float) ($it['line_total'] ?? max(0, $lineSubtotal - $discount + $tax));

                    $pricingSnapshot = is_array($it['pricing_snapshot'] ?? null) ? $it['pricing_snapshot'] : [];
                    $pricingSnapshot = array_merge($pricingSnapshot, [
                        'stored_at' => now()->toISOString(),
                        'stored_by' => $userId,
                        'working_group_id' => $wgId,
                        'product_id' => (int) ($it['product_id'] ?? 0),
                    ]);

                    DB::table('estimate_items')->insert([
                        'estimate_id' => $estimateId,
                        'working_group_id' => $wgId,
                        'product_id' => (int) ($it['product_id'] ?? 0),
                        'variant_set_item_id' => null,
                        'roll_id' => isset($it['roll_id']) && $it['roll_id'] ? (int) $it['roll_id'] : null,
                        'title' => (string) ($it['title'] ?? 'Item'),
                        'description' => $it['description'] ?? null,
                        'qty' => (int) ($it['qty'] ?? 1),
                        'width' => $it['width'] ?? null,
                        'height' => $it['height'] ?? null,
                        'unit' => $it['unit'] ?? null,
                        'area_sqft' => $it['area_sqft'] ?? null,
                        'offcut_sqft' => $it['offcut_sqft'] ?? 0,
                        'unit_price' => $it['unit_price'] ?? 0,
                        'line_subtotal' => $lineSubtotal,
                        'discount_amount' => $discount,
                        'tax_amount' => $tax,
                        'line_total' => $lineTotal,
                        'pricing_snapshot' => json_encode($pricingSnapshot),
                        'sort_order' => (int) $idx,
                        'created_at' => $now,
                        'updated_at' => $now,
                        'deleted_at' => null,
                    ]);

                    $subtotal += $lineSubtotal;
                }

                DB::table('estimates')
                    ->where('id', $estimateId)
                    ->update([
                        'subtotal' => $subtotal,
                        'discount_total' => 0,
                        'tax_total' => 0,
                        'grand_total' => $subtotal,
                        'updated_by' => $userId,
                        'updated_at' => $now,
                    ]);

                // Minimal status trail (for the UI audit panel)
                $status = (string) ($seed['status'] ?? 'draft');
                $sentAt = $seed['sent_at'] ?? null;
                $acceptedAt = $seed['accepted_at'] ?? null;

                DB::table('estimate_status_histories')->insert([
                    'estimate_id' => $estimateId,
                    'from_status' => null,
                    'to_status' => 'draft',
                    'changed_by' => $userId,
                    'reason' => 'Seeded draft',
                    'meta' => json_encode(['seed' => true]),
                    'created_at' => $now,
                ]);

                if ($status === 'sent' || $status === 'viewed' || $status === 'accepted' || $status === 'converted') {
                    DB::table('estimate_status_histories')->insert([
                        'estimate_id' => $estimateId,
                        'from_status' => 'draft',
                        'to_status' => 'sent',
                        'changed_by' => $userId,
                        'reason' => 'Seeded sent',
                        'meta' => json_encode(['seed' => true]),
                        'created_at' => $sentAt ?: $now,
                    ]);
                }

                if ($status === 'accepted') {
                    DB::table('estimate_status_histories')->insert([
                        'estimate_id' => $estimateId,
                        'from_status' => 'sent',
                        'to_status' => 'accepted',
                        'changed_by' => $userId,
                        'reason' => 'Seeded accepted',
                        'meta' => json_encode(['seed' => true]),
                        'created_at' => $acceptedAt ?: $now,
                    ]);
                }
            });
        }

        $this->command?->info('QuotationSeeder: seeded 3 dev estimates (draft/sent/accepted).');
    }
}

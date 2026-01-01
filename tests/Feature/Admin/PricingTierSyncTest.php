<?php

namespace Tests\Feature\Admin;

use App\Models\Product;
use App\Models\ProductPriceTier;
use App\Models\ProductPricing;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PricingTierSyncTest extends TestCase
{
    use RefreshDatabase;

    public function test_sync_tiers_soft_deletes_removed_tiers(): void
    {
        $role = Role::query()->create([
            'name' => 'Admin',
            'description' => 'Admin',
            'is_staff' => true,
        ]);

        $user = User::factory()->create([
            'role_id' => $role->id,
        ]);

        $product = Product::query()->create([
            'product_code' => 'TEST-PROD-1',
            'name' => 'Test Product',
            'slug' => 'test-product',
            'product_type' => 'standard',
            'status' => 'active',
            'visibility' => 'public',
        ]);

        $pricing = ProductPricing::query()->create([
            'product_id' => $product->id,
            'context' => 'public',
            'working_group_id' => null,
            'override_base' => false,
            'override_variants' => false,
            'override_finishings' => false,
            'base_price' => 1000,
            'is_active' => true,
        ]);

        $tier1 = ProductPriceTier::query()->create([
            'product_pricing_id' => $pricing->id,
            'min_qty' => 1,
            'max_qty' => 9,
            'price' => 1000,
        ]);

        $tier2 = ProductPriceTier::query()->create([
            'product_pricing_id' => $pricing->id,
            'min_qty' => 10,
            'max_qty' => null,
            'price' => 900,
        ]);

        $token = 'test';

        $res = $this
            ->actingAs($user)
            ->withSession(['_token' => $token])
            ->withHeader('X-CSRF-TOKEN', $token)
            ->patchJson(route('admin.pricing.products.tiers.sync', $product), [
                'product_pricing_id' => $pricing->id,
                'tiers' => [
                    [
                        'id' => $tier1->id,
                        'min_qty' => 1,
                        'max_qty' => 9,
                        'price' => 1000,
                    ],
                ],
            ]);

        $res->assertOk()->assertJsonPath('ok', true);

        $this->assertSame(1, ProductPriceTier::query()->where('product_pricing_id', $pricing->id)->count());
        $this->assertNotNull(ProductPriceTier::withTrashed()->find($tier2->id)?->deleted_at);
    }
}


<?php

namespace Tests\Feature\Estimates;

use App\Models\Estimate;
use App\Models\EstimateItem;
use App\Models\Product;
use App\Models\User;
use App\Models\WorkingGroup;
use App\Services\Estimates\EstimatePdfService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class EstimatePdfRenderTest extends TestCase
{
    use RefreshDatabase;

    public function test_pdf_renders_for_large_estimate(): void
    {
        $wg = WorkingGroup::factory()->create();
        $user = User::factory()->create(['working_group_id' => $wg->id]);

        $product = Product::query()->create([
            'category_id' => null,
            'product_code' => 'P-'.Str::upper(Str::random(8)),
            'name' => 'Test Product',
            'slug' => 'test-product-'.Str::lower(Str::random(6)),
            'product_type' => 'standard',
            'status' => 'active',
            'visibility' => 'public',
            'requires_dimensions' => false,
            'allow_custom_size' => false,
            'allow_predefined_sizes' => true,
            'allow_rotation_to_fit_roll' => true,
            'allow_manual_pricing' => false,
            'meta' => null,
            'created_by' => $user->id,
            'updated_by' => $user->id,
        ]);

        $estimate = Estimate::query()->create([
            'uuid' => (string) Str::uuid(),
            'estimate_no' => 'EST-PDF-'.Str::upper(Str::random(6)),
            'working_group_id' => $wg->id,
            'customer_id' => null,
            'customer_snapshot' => [
                'full_name' => 'PDF Customer',
                'email' => 'pdf@example.com',
            ],
            'currency' => 'LKR',
            'subtotal' => 0,
            'discount_total' => 0,
            'tax_total' => 0,
            'shipping_fee' => 0,
            'other_fee' => 0,
            'grand_total' => 0,
            'tax_mode' => 'none',
            'discount_mode' => 'none',
            'discount_value' => 0,
            'status' => 'sent',
            'valid_until' => now()->addDays(7),
            'sent_at' => now(),
            'locked_at' => now(),
            'locked_by' => $user->id,
            'revision' => 1,
            'created_by' => $user->id,
            'updated_by' => $user->id,
        ]);

        // Force multiple pages by creating many items.
        for ($i = 1; $i <= 80; $i++) {
            EstimateItem::query()->create([
                'estimate_id' => $estimate->id,
                'working_group_id' => $wg->id,
                'product_id' => $product->id,
                'variant_set_item_id' => null,
                'roll_id' => null,
                'title' => "Line {$i}",
                'description' => 'Long list item',
                'qty' => 1,
                'pricing_snapshot' => ['source' => 'test'],
                'unit_price' => 100,
                'line_subtotal' => 100,
                'discount_amount' => 0,
                'tax_amount' => 0,
                'line_total' => 100,
                'sort_order' => $i,
            ]);
        }

        /** @var EstimatePdfService $svc */
        $svc = app(EstimatePdfService::class);
        $bytes = $svc->render($estimate, 'https://example.com/estimate/token');

        $this->assertIsString($bytes);
        $this->assertStringStartsWith('%PDF', $bytes);
    }
}


<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // In case migration partially ran before, only create if missing.
        if (! Schema::hasTable('product_pricings')) {
            Schema::create('product_pricings', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('product_id');

            $table->enum('context', ['public', 'working_group'])->default('public');
            $table->unsignedBigInteger('working_group_id')->nullable();

            // working_group_key generated column will be added via raw SQL

            $table->boolean('override_base')->default(false);
            $table->boolean('override_variants')->default(false);
            $table->boolean('override_finishings')->default(false);

            // standard/service
            $table->decimal('base_price', 12, 2)->nullable();

            // dimension_based
            $table->decimal('rate_per_sqft', 12, 4)->nullable();
            $table->decimal('offcut_rate_per_sqft', 12, 4)->nullable();
            $table->decimal('min_charge', 12, 2)->nullable();

            $table->boolean('is_active')->default(true);

            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();

            $table->timestamps();
            $table->softDeletes();

            $table->index(['product_id', 'context'], 'product_pricings_product_context_index');
            $table->index('working_group_id', 'product_pricings_working_group_index');
            $table->index('is_active', 'product_pricings_active_index');

            $table->foreign('product_id', 'product_pricings_product_id_foreign')
                ->references('id')
                ->on('products')
                ->cascadeOnDelete();

            $table->foreign('working_group_id', 'product_pricings_working_group_id_foreign')
                ->references('id')
                ->on('working_groups')
                ->cascadeOnDelete();

            $table->foreign('created_by', 'product_pricings_created_by_foreign')
                ->references('id')
                ->on('users')
                ->nullOnDelete();

            $table->foreign('updated_by', 'product_pricings_updated_by_foreign')
                ->references('id')
                ->on('users')
                ->nullOnDelete();
            });
        }

        Schema::create('product_price_tiers', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('product_pricing_id');

            $table->unsignedInteger('min_qty');
            $table->unsignedInteger('max_qty')->nullable();
            $table->decimal('price', 12, 2);

            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();

            $table->timestamps();
            $table->softDeletes();

            $table->index(
                ['product_pricing_id', 'min_qty', 'max_qty'],
                'product_price_tiers_pricing_qty_index'
            );

            $table->foreign('product_pricing_id', 'product_price_tiers_product_pricing_id_foreign')
                ->references('id')
                ->on('product_pricings')
                ->cascadeOnDelete();

            $table->foreign('created_by', 'product_price_tiers_created_by_foreign')
                ->references('id')
                ->on('users')
                ->nullOnDelete();

            $table->foreign('updated_by', 'product_price_tiers_updated_by_foreign')
                ->references('id')
                ->on('users')
                ->nullOnDelete();
        });

        Schema::create('product_variant_pricings', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('product_pricing_id');
            $table->unsignedBigInteger('variant_set_id');

            $table->decimal('fixed_price', 12, 2)->nullable();
            $table->decimal('rate_per_sqft', 12, 4)->nullable();
            $table->decimal('offcut_rate_per_sqft', 12, 4)->nullable();
            $table->decimal('min_charge', 12, 2)->nullable();

            $table->boolean('is_active')->default(true);

            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();

            $table->timestamps();
            $table->softDeletes();

            $table->unique(
                ['product_pricing_id', 'variant_set_id'],
                'variant_pricing_unique'
            );

            $table->foreign('product_pricing_id', 'product_variant_pricings_product_pricing_id_foreign')
                ->references('id')
                ->on('product_pricings')
                ->cascadeOnDelete();

            $table->foreign('variant_set_id', 'product_variant_pricings_variant_set_id_foreign')
                ->references('id')
                ->on('product_variant_sets')
                ->cascadeOnDelete();

            $table->foreign('created_by', 'product_variant_pricings_created_by_foreign')
                ->references('id')
                ->on('users')
                ->nullOnDelete();

            $table->foreign('updated_by', 'product_variant_pricings_updated_by_foreign')
                ->references('id')
                ->on('users')
                ->nullOnDelete();
        });

        Schema::create('product_finishing_pricings', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('product_pricing_id');
            $table->unsignedBigInteger('finishing_product_id');

            $table->decimal('price_per_piece', 12, 2)->nullable();
            $table->decimal('price_per_side', 12, 2)->nullable();
            $table->decimal('flat_price', 12, 2)->nullable();

            $table->unsignedInteger('min_qty')->nullable();
            $table->unsignedInteger('max_qty')->nullable();

            $table->boolean('is_active')->default(true);

            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();

            $table->timestamps();
            $table->softDeletes();

            $table->unique(
                ['product_pricing_id', 'finishing_product_id'],
                'finishing_pricing_unique'
            );

            $table->foreign('product_pricing_id', 'product_finishing_pricings_product_pricing_id_foreign')
                ->references('id')
                ->on('product_pricings')
                ->cascadeOnDelete();

            $table->foreign('finishing_product_id', 'product_finishing_pricings_finishing_product_id_foreign')
                ->references('id')
                ->on('products')
                ->restrictOnDelete();

            $table->foreign('created_by', 'product_finishing_pricings_created_by_foreign')
                ->references('id')
                ->on('users')
                ->nullOnDelete();

            $table->foreign('updated_by', 'product_finishing_pricings_updated_by_foreign')
                ->references('id')
                ->on('users')
                ->nullOnDelete();
        });

        Schema::create('product_variant_availability_overrides', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('variant_set_id');
            $table->unsignedBigInteger('working_group_id');

            $table->boolean('is_enabled')->default(true);
            $table->string('reason', 255)->nullable();

            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();

            $table->timestamps();

            $table->unique(
                ['variant_set_id', 'working_group_id'],
                'variant_availability_unique'
            );

            $table->index(
                ['working_group_id', 'is_enabled'],
                'variant_availability_wg_enabled_index'
            );

            $table->foreign('variant_set_id', 'variant_availability_variant_set_id_foreign')
                ->references('id')
                ->on('product_variant_sets')
                ->cascadeOnDelete();

            $table->foreign('working_group_id', 'variant_availability_working_group_id_foreign')
                ->references('id')
                ->on('working_groups')
                ->cascadeOnDelete();

            $table->foreign('created_by', 'variant_availability_created_by_foreign')
                ->references('id')
                ->on('users')
                ->nullOnDelete();

            $table->foreign('updated_by', 'variant_availability_updated_by_foreign')
                ->references('id')
                ->on('users')
                ->nullOnDelete();
        });

        Schema::create('pricing_audits', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('product_pricing_id');
            $table->unsignedBigInteger('user_id')->nullable();

            $table->enum('action', ['created', 'updated', 'deleted', 'restored', 'published'])
                ->default('updated');

            $table->json('old_values')->nullable();
            $table->json('new_values')->nullable();

            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();

            $table->timestamp('created_at')->nullable();

            $table->index('product_pricing_id', 'pricing_audits_pricing_id_index');
            $table->index('user_id', 'pricing_audits_user_id_index');

            $table->foreign('product_pricing_id', 'pricing_audits_product_pricing_id_foreign')
                ->references('id')
                ->on('product_pricings')
                ->cascadeOnDelete();

            $table->foreign('user_id', 'pricing_audits_user_id_foreign')
                ->references('id')
                ->on('users')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pricing_audits');
        Schema::dropIfExists('product_variant_availability_overrides');
        Schema::dropIfExists('product_finishing_pricings');
        Schema::dropIfExists('product_variant_pricings');
        Schema::dropIfExists('product_price_tiers');

        // Drop product_pricings last (includes generated column and unique index)
        Schema::dropIfExists('product_pricings');
    }
};

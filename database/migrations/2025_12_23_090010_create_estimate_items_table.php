<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('estimate_items', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('estimate_id');
            $table->unsignedBigInteger('working_group_id');

            $table->unsignedBigInteger('product_id');
            $table->unsignedBigInteger('variant_set_item_id')->nullable();
            $table->unsignedBigInteger('roll_id')->nullable();

            $table->string('title', 255);
            $table->text('description')->nullable();

            $table->unsignedInteger('qty')->default(1);

            $table->decimal('width', 10, 3)->nullable();
            $table->decimal('height', 10, 3)->nullable();
            $table->enum('unit', ['mm', 'cm', 'in', 'ft', 'm'])->nullable();
            $table->decimal('area_sqft', 12, 4)->nullable();
            $table->decimal('offcut_sqft', 12, 4)->default(0);

            $table->decimal('unit_price', 12, 2)->default(0);
            $table->decimal('line_subtotal', 12, 2)->default(0);
            $table->decimal('discount_amount', 12, 2)->default(0);
            $table->decimal('tax_amount', 12, 2)->default(0);
            $table->decimal('line_total', 12, 2)->default(0);

            $table->json('pricing_snapshot');
            $table->integer('sort_order')->default(0);

            $table->timestamps();
            $table->softDeletes();

            $table->index('estimate_id', 'estimate_items_estimate_id_index');
            $table->index(['estimate_id', 'sort_order'], 'estimate_items_estimate_sort_index');
            $table->index('product_id', 'estimate_items_product_id_index');
            $table->index('working_group_id', 'estimate_items_wg_index');

            $table->foreign('estimate_id', 'estimate_items_estimate_id_foreign')
                ->references('id')
                ->on('estimates')
                ->cascadeOnDelete();

            $table->foreign('working_group_id', 'estimate_items_working_group_id_foreign')
                ->references('id')
                ->on('working_groups')
                ->restrictOnDelete();

            $table->foreign('product_id', 'estimate_items_product_id_foreign')
                ->references('id')
                ->on('products')
                ->restrictOnDelete();

            $table->foreign('variant_set_item_id', 'estimate_items_variant_set_item_id_foreign')
                ->references('id')
                ->on('product_variant_set_items')
                ->nullOnDelete();

            $table->foreign('roll_id', 'estimate_items_roll_id_foreign')
                ->references('id')
                ->on('rolls')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('estimate_items');
    }
};


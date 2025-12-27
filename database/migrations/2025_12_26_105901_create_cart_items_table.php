<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('cart_items', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('cart_id')->index();

            $table->unsignedBigInteger('product_id')->index();

            // Optional: a chosen variant set item (ex: size)
            $table->unsignedBigInteger('variant_set_item_id')->nullable()->index();

            // Optional: chosen roll
            $table->unsignedBigInteger('roll_id')->nullable()->index();

            $table->unsignedInteger('qty')->default(1);

            // Dimensions (printing world)
            $table->decimal('width', 10, 3)->nullable();
            $table->decimal('height', 10, 3)->nullable();
            $table->string('unit', 16)->nullable(); // mm|cm|in|ft etc.

            $table->decimal('area_sqft', 12, 4)->nullable();
            $table->decimal('offcut_sqft', 12, 4)->nullable();

            // Frozen data for UI + later conversion to order snapshots
            $table->json('pricing_snapshot')->nullable();

            $table->text('notes')->nullable();
            $table->json('meta')->nullable();

            $table->timestamps();
            $table->softDeletes();

            $table->foreign('cart_id')->references('id')->on('carts')->cascadeOnDelete();

            // Keep these as FK if tables exist in your DB; otherwise switch to plain indexes.
            $table->foreign('product_id')->references('id')->on('products')->cascadeOnDelete();

            // If you already have product_variant_set_items table:
            $table->foreign('variant_set_item_id')->references('id')->on('product_variant_set_items')->nullOnDelete();

            // If you already have rolls table:
            $table->foreign('roll_id')->references('id')->on('rolls')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cart_items');
    }
};


<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('order_item_finishings', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('order_item_id');
            $table->unsignedBigInteger('finishing_product_id');
            $table->unsignedBigInteger('option_id')->nullable();

            $table->string('label', 255);
            $table->unsignedInteger('qty')->default(1);

            $table->decimal('unit_price', 12, 2)->default(0);
            $table->decimal('total', 12, 2)->default(0);

            $table->json('pricing_snapshot')->nullable();

            $table->timestamps();
            $table->softDeletes();

            $table->index('order_item_id', 'order_item_finishings_order_item_id_index');

            $table->foreign('order_item_id', 'order_item_finishings_order_item_id_foreign')
                ->references('id')
                ->on('order_items')
                ->cascadeOnDelete();

            $table->foreign('finishing_product_id', 'order_item_finishings_finishing_product_id_foreign')
                ->references('id')
                ->on('products')
                ->restrictOnDelete();

            $table->foreign('option_id', 'order_item_finishings_option_id_foreign')
                ->references('id')
                ->on('options')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('order_item_finishings');
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('invoice_item_finishings', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('invoice_item_id');
            $table->unsignedBigInteger('order_item_finishing_id')->nullable();

            $table->unsignedBigInteger('finishing_product_id')->nullable();
            $table->unsignedBigInteger('option_id')->nullable();

            $table->string('label', 255)->nullable();
            $table->unsignedInteger('qty')->default(1);

            $table->decimal('unit_price', 12, 2)->default(0);
            $table->decimal('total', 12, 2)->default(0);

            $table->json('pricing_snapshot')->nullable();
            $table->json('meta')->nullable();

            $table->timestamps();
            $table->softDeletes();

            $table->index('invoice_item_id', 'invoice_item_finishings_invoice_item_id_index');
            $table->index('order_item_finishing_id', 'invoice_item_finishings_order_item_finishing_id_index');

            $table->foreign('invoice_item_id', 'invoice_item_finishings_invoice_item_id_foreign')
                ->references('id')
                ->on('invoice_items')
                ->cascadeOnDelete();

            $table->foreign('order_item_finishing_id', 'invoice_item_finishings_order_item_finishing_id_foreign')
                ->references('id')
                ->on('order_item_finishings')
                ->nullOnDelete();

            $table->foreign('finishing_product_id', 'invoice_item_finishings_finishing_product_id_foreign')
                ->references('id')
                ->on('products')
                ->nullOnDelete();

            $table->foreign('option_id', 'invoice_item_finishings_option_id_foreign')
                ->references('id')
                ->on('options')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('invoice_item_finishings');
    }
};


<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('cart_item_finishings', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('cart_item_id')->index();
            $table->unsignedBigInteger('finishing_product_id')->index(); // finishing is a product in your system

            $table->unsignedInteger('qty')->default(1);

            $table->json('pricing_snapshot')->nullable();
            $table->json('meta')->nullable();

            $table->timestamps();
            $table->softDeletes();

            $table->foreign('cart_item_id')->references('id')->on('cart_items')->cascadeOnDelete();
            $table->foreign('finishing_product_id')->references('id')->on('products')->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cart_item_finishings');
    }
};


<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {

        Schema::create('product_rolls', function (Blueprint $table) {
            Schema::dropIfExists('product_rolls');
            $table->id();

            $table->unsignedBigInteger('product_id');
            $table->unsignedBigInteger('roll_id');

            // Per-product availability (not per WG; WG disabling can be added later)
            $table->boolean('is_active')->default(true);

            // Optional constraints (keep nullable for now)
            $table->decimal('min_height_in', 10, 3)->nullable();
            $table->decimal('max_height_in', 10, 3)->nullable();

            $table->json('meta')->nullable();

            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->timestamps();
            $table->softDeletes();

            // Prevent duplicates per product
            $table->unique(['product_id', 'roll_id']);

            $table->index(['product_id', 'is_active']);
            $table->index(['roll_id', 'is_active']);

            $table->foreign('product_id')->references('id')->on('products')->cascadeOnDelete();
            $table->foreign('roll_id')->references('id')->on('rolls')->restrictOnDelete();

            $table->foreign('created_by')->references('id')->on('users')->nullOnDelete();
            $table->foreign('updated_by')->references('id')->on('users')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('product_rolls');
    }
};

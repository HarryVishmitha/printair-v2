
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('product_roll_pricings', function (Blueprint $table) {
            
            $table->id();

            // Context container (public or working_group pricing row)
            $table->unsignedBigInteger('product_pricing_id');

            // Redundant but intentional (fast querying + integrity checks)
            $table->unsignedBigInteger('product_id');
            $table->unsignedBigInteger('roll_id');

            // Rates (dimension-based money)
            $table->decimal('rate_per_sqft', 12, 2)->nullable();
            $table->decimal('offcut_rate_per_sqft', 12, 2)->nullable();
            $table->decimal('min_charge', 12, 2)->nullable();

            $table->boolean('is_active')->default(true);
            $table->json('meta')->nullable();

            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->timestamps();
            $table->softDeletes();

            // Prevent duplicates within the same pricing context
            $table->unique(['product_pricing_id', 'roll_id']);

            $table->index(['product_id', 'roll_id', 'is_active']);
            $table->index(['product_pricing_id', 'is_active']);

            $table->foreign('product_pricing_id')->references('id')->on('product_pricings')->cascadeOnDelete();
            $table->foreign('product_id')->references('id')->on('products')->cascadeOnDelete();
            $table->foreign('roll_id')->references('id')->on('rolls')->restrictOnDelete();

            $table->foreign('created_by')->references('id')->on('users')->nullOnDelete();
            $table->foreign('updated_by')->references('id')->on('users')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('product_roll_pricings');
    }
};

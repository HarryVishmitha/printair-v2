<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('order_deliveries', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('order_id')->unique();

            $table->string('method')->default('pickup')->index(); // pickup|delivery
            $table->string('courier')->nullable(); // pickme_flash|manual|etc

            $table->string('tracking_no')->nullable();
            $table->string('vehicle_no')->nullable();

            $table->string('driver_name')->nullable();
            $table->string('driver_phone')->nullable();

            $table->timestamp('dispatched_at')->nullable();
            $table->timestamp('delivered_at')->nullable();

            $table->json('meta')->nullable();

            $table->timestamps();
            $table->softDeletes();

            $table->foreign('order_id')->references('id')->on('orders')->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('order_deliveries');
    }
};

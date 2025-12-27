<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('cart_item_files', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('cart_item_id')->index();

            $table->string('path');
            $table->string('disk')->default('public'); // adjust if you use s3 or custom disk
            $table->string('original_name')->nullable();

            $table->string('mime', 128)->nullable();
            $table->unsignedBigInteger('size_bytes')->nullable();

            $table->boolean('is_customer_artwork')->default(true);

            $table->json('meta')->nullable();

            $table->timestamps();
            $table->softDeletes();

            $table->foreign('cart_item_id')->references('id')->on('cart_items')->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cart_item_files');
    }
};


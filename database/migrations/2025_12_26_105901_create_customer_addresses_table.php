<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('customer_addresses', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('customer_id')->index();

            $table->string('label')->nullable(); // Home, Office, etc.

            $table->string('line1');
            $table->string('line2')->nullable();

            $table->string('city')->nullable();
            $table->string('district')->nullable();
            $table->string('state')->nullable();

            $table->string('postal_code', 24)->nullable();
            $table->string('country', 2)->default('LK');

            $table->string('phone_number')->nullable();

            $table->boolean('is_primary')->default(false)->index();

            $table->timestamps();
            $table->softDeletes();

            $table->foreign('customer_id')->references('id')->on('customers')->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('customer_addresses');
    }
};

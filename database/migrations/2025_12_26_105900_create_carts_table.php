<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('carts', function (Blueprint $table) {
            $table->id();

            $table->uuid('uuid')->unique();

            $table->unsignedBigInteger('user_id')->nullable()->index();
            $table->unsignedBigInteger('working_group_id')->nullable()->index();

            $table->string('currency', 3)->default('LKR');
            $table->string('status')->default('active')->index(); // active|converted|abandoned

            $table->json('meta')->nullable();

            $table->timestamps();
            $table->softDeletes();

            $table->foreign('user_id')->references('id')->on('users')->nullOnDelete();
            $table->foreign('working_group_id')->references('id')->on('working_groups')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('carts');
    }
};

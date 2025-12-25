<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('order_status_histories', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('order_id');
            $table->string('from_status', 30)->nullable();
            $table->string('to_status', 30);
            $table->unsignedBigInteger('changed_by')->nullable();
            $table->string('reason', 500)->nullable();
            $table->json('meta')->nullable();
            $table->timestamp('created_at')->nullable();

            $table->index('order_id', 'order_status_histories_order_id_index');
            $table->index(['order_id', 'created_at'], 'order_status_histories_order_created_index');

            $table->foreign('order_id', 'order_status_histories_order_id_foreign')
                ->references('id')
                ->on('orders')
                ->cascadeOnDelete();

            $table->foreign('changed_by', 'order_status_histories_changed_by_foreign')
                ->references('id')
                ->on('users')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('order_status_histories');
    }
};


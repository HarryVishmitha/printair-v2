<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('orders', function (Blueprint $table) {
            $table->id();

            $table->uuid('uuid');
            $table->string('order_no', 64);
            $table->unsignedBigInteger('working_group_id');

            $table->unsignedBigInteger('customer_id')->nullable();
            $table->unsignedBigInteger('estimate_id')->nullable();
            $table->json('customer_snapshot')->nullable();

            $table->char('currency', 3)->default('LKR');

            $table->decimal('subtotal', 12, 2)->default(0);
            $table->decimal('discount_total', 12, 2)->default(0);
            $table->decimal('tax_total', 12, 2)->default(0);
            $table->decimal('shipping_fee', 12, 2)->default(0);
            $table->decimal('other_fee', 12, 2)->default(0);
            $table->decimal('grand_total', 12, 2)->default(0);

            $table->enum('status', ['draft', 'confirmed', 'in_production', 'ready', 'out_for_delivery', 'completed', 'cancelled', 'refunded'])
                ->default('draft');
            $table->enum('payment_status', ['unpaid', 'partial', 'paid', 'refunded'])->default('unpaid');

            $table->dateTime('ordered_at')->nullable();
            $table->dateTime('confirmed_at')->nullable();
            $table->dateTime('completed_at')->nullable();
            $table->dateTime('cancelled_at')->nullable();

            $table->dateTime('locked_at')->nullable();
            $table->unsignedBigInteger('locked_by')->nullable();

            $table->json('meta')->nullable();

            $table->unsignedBigInteger('created_by');
            $table->unsignedBigInteger('updated_by')->nullable();

            $table->timestamps();
            $table->softDeletes();

            $table->unique('uuid', 'orders_uuid_unique');
            $table->unique(['working_group_id', 'order_no'], 'orders_wg_order_no_unique');

            $table->index(['working_group_id', 'status'], 'orders_wg_status_index');
            $table->index('customer_id', 'orders_customer_id_index');
            $table->index('estimate_id', 'orders_estimate_id_index');

            $table->foreign('working_group_id', 'orders_working_group_id_foreign')
                ->references('id')
                ->on('working_groups')
                ->restrictOnDelete();

            $table->foreign('customer_id', 'orders_customer_id_foreign')
                ->references('id')
                ->on('customers')
                ->nullOnDelete();

            $table->foreign('estimate_id', 'orders_estimate_id_foreign')
                ->references('id')
                ->on('estimates')
                ->nullOnDelete();

            $table->foreign('locked_by', 'orders_locked_by_foreign')
                ->references('id')
                ->on('users')
                ->nullOnDelete();

            $table->foreign('created_by', 'orders_created_by_foreign')
                ->references('id')
                ->on('users')
                ->restrictOnDelete();

            $table->foreign('updated_by', 'orders_updated_by_foreign')
                ->references('id')
                ->on('users')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};


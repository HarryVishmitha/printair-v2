<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payment_allocations', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('payment_id');
            $table->unsignedBigInteger('invoice_id');
            $table->decimal('amount', 12, 2)->default(0);
            $table->unsignedBigInteger('created_by')->nullable();
            $table->timestamp('created_at')->nullable();

            $table->unique(['payment_id', 'invoice_id'], 'payment_allocations_payment_invoice_unique');
            $table->index('payment_id', 'payment_allocations_payment_id_index');
            $table->index('invoice_id', 'payment_allocations_invoice_id_index');

            $table->foreign('payment_id', 'payment_allocations_payment_id_foreign')
                ->references('id')
                ->on('payments')
                ->cascadeOnDelete();

            $table->foreign('invoice_id', 'payment_allocations_invoice_id_foreign')
                ->references('id')
                ->on('invoices')
                ->cascadeOnDelete();

            $table->foreign('created_by', 'payment_allocations_created_by_foreign')
                ->references('id')
                ->on('users')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payment_allocations');
    }
};

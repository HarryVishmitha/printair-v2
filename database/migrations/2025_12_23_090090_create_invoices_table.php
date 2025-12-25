<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('invoices', function (Blueprint $table) {
            $table->id();

            $table->uuid('uuid');
            $table->string('invoice_no', 64);

            $table->unsignedBigInteger('working_group_id');
            $table->unsignedBigInteger('order_id');

            $table->enum('type', ['final', 'partial', 'credit_note'])->default('final');
            $table->enum('status', ['draft', 'issued', 'void', 'paid', 'partial', 'overdue', 'refunded'])->default('draft');

            $table->dateTime('issued_at')->nullable();
            $table->dateTime('due_at')->nullable();
            $table->dateTime('paid_at')->nullable();
            $table->dateTime('voided_at')->nullable();

            $table->dateTime('locked_at')->nullable();
            $table->unsignedBigInteger('locked_by')->nullable();

            $table->json('customer_snapshot')->nullable();

            $table->char('currency', 3)->default('LKR');
            $table->decimal('subtotal', 12, 2)->default(0);
            $table->decimal('discount_total', 12, 2)->default(0);
            $table->decimal('tax_total', 12, 2)->default(0);
            $table->decimal('shipping_fee', 12, 2)->default(0);
            $table->decimal('other_fee', 12, 2)->default(0);
            $table->decimal('grand_total', 12, 2)->default(0);

            $table->decimal('amount_paid', 12, 2)->default(0);
            $table->decimal('amount_due', 12, 2)->default(0);

            $table->json('meta')->nullable();

            $table->unsignedBigInteger('created_by');
            $table->unsignedBigInteger('updated_by')->nullable();

            $table->timestamps();
            $table->softDeletes();

            $table->unique('uuid', 'invoices_uuid_unique');
            $table->unique(['working_group_id', 'invoice_no'], 'invoices_wg_invoice_no_unique');
            $table->index(['working_group_id', 'status'], 'invoices_wg_status_index');
            $table->index('order_id', 'invoices_order_id_index');
            $table->index('locked_at', 'invoices_locked_at_index');

            $table->foreign('working_group_id', 'invoices_working_group_id_foreign')
                ->references('id')
                ->on('working_groups')
                ->restrictOnDelete();

            $table->foreign('order_id', 'invoices_order_id_foreign')
                ->references('id')
                ->on('orders')
                ->restrictOnDelete();

            $table->foreign('locked_by', 'invoices_locked_by_foreign')
                ->references('id')
                ->on('users')
                ->nullOnDelete();

            $table->foreign('created_by', 'invoices_created_by_foreign')
                ->references('id')
                ->on('users')
                ->restrictOnDelete();

            $table->foreign('updated_by', 'invoices_updated_by_foreign')
                ->references('id')
                ->on('users')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('invoices');
    }
};

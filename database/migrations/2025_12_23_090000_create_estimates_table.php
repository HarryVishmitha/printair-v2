<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('estimates', function (Blueprint $table) {
            $table->id();

            $table->uuid('uuid');
            $table->string('estimate_no', 64);

            $table->unsignedBigInteger('working_group_id');
            $table->unsignedBigInteger('customer_id')->nullable();

            $table->json('customer_snapshot')->nullable();

            $table->char('currency', 3)->default('LKR');
            $table->unsignedBigInteger('price_tier_id')->nullable();

            $table->decimal('subtotal', 12, 2)->default(0);
            $table->decimal('discount_total', 12, 2)->default(0);
            $table->decimal('tax_total', 12, 2)->default(0);
            $table->decimal('shipping_fee', 12, 2)->default(0);
            $table->decimal('other_fee', 12, 2)->default(0);
            $table->decimal('grand_total', 12, 2)->default(0);

            $table->enum('tax_mode', ['none', 'inclusive', 'exclusive'])->default('none');
            $table->enum('discount_mode', ['none', 'percent', 'amount'])->default('none');
            $table->decimal('discount_value', 10, 2)->default(0);

            $table->enum('status', ['draft', 'sent', 'viewed', 'accepted', 'rejected', 'expired', 'cancelled', 'converted'])
                ->default('draft');
            $table->dateTime('valid_until')->nullable();
            $table->dateTime('sent_at')->nullable();
            $table->dateTime('accepted_at')->nullable();
            $table->dateTime('rejected_at')->nullable();
            $table->dateTime('converted_at')->nullable();

            $table->dateTime('locked_at')->nullable();
            $table->unsignedBigInteger('locked_by')->nullable();

            $table->unsignedInteger('revision')->default(1);
            $table->unsignedBigInteger('parent_estimate_id')->nullable();

            $table->text('notes_internal')->nullable();
            $table->text('notes_customer')->nullable();
            $table->longText('terms')->nullable();
            $table->json('meta')->nullable();

            $table->unsignedBigInteger('created_by');
            $table->unsignedBigInteger('updated_by')->nullable();

            $table->timestamps();
            $table->softDeletes();

            $table->unique('uuid', 'estimates_uuid_unique');
            $table->unique(['working_group_id', 'estimate_no'], 'estimates_wg_estimate_no_unique');
            $table->unique(['parent_estimate_id', 'revision'], 'estimates_parent_revision_unique');

            $table->index(['working_group_id', 'status'], 'estimates_wg_status_index');
            $table->index('customer_id', 'estimates_customer_id_index');
            $table->index('created_by', 'estimates_created_by_index');
            $table->index('locked_at', 'estimates_locked_at_index');

            $table->foreign('working_group_id', 'estimates_working_group_id_foreign')
                ->references('id')
                ->on('working_groups')
                ->restrictOnDelete();

            $table->foreign('customer_id', 'estimates_customer_id_foreign')
                ->references('id')
                ->on('customers')
                ->nullOnDelete();

            $table->foreign('locked_by', 'estimates_locked_by_foreign')
                ->references('id')
                ->on('users')
                ->nullOnDelete();

            $table->foreign('parent_estimate_id', 'estimates_parent_estimate_id_foreign')
                ->references('id')
                ->on('estimates')
                ->nullOnDelete();

            $table->foreign('created_by', 'estimates_created_by_foreign')
                ->references('id')
                ->on('users')
                ->restrictOnDelete();

            $table->foreign('updated_by', 'estimates_updated_by_foreign')
                ->references('id')
                ->on('users')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('estimates');
    }
};


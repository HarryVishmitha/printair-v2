<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payments', function (Blueprint $table) {
            $table->id();

            $table->uuid('uuid');

            $table->unsignedBigInteger('working_group_id');
            $table->unsignedBigInteger('customer_id')->nullable();

            $table->enum('method', ['cash', 'card', 'bank_transfer', 'online_gateway']);
            $table->enum('status', ['pending', 'confirmed', 'failed', 'void', 'refunded'])->default('pending');

            $table->decimal('amount', 12, 2)->default(0);
            $table->char('currency', 3)->default('LKR');

            $table->string('reference_no', 100)->nullable();
            $table->dateTime('received_at')->nullable();
            $table->unsignedBigInteger('received_by')->nullable();

            $table->json('meta')->nullable();

            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();

            $table->timestamps();
            $table->softDeletes();

            $table->unique('uuid', 'payments_uuid_unique');
            $table->index('working_group_id', 'payments_wg_index');
            $table->index(['working_group_id', 'status'], 'payments_wg_status_index');
            $table->index('customer_id', 'payments_customer_id_index');
            $table->index('reference_no', 'payments_reference_no_index');
            $table->index('received_at', 'payments_received_at_index');

            $table->foreign('working_group_id', 'payments_working_group_id_foreign')
                ->references('id')
                ->on('working_groups')
                ->restrictOnDelete();

            $table->foreign('customer_id', 'payments_customer_id_foreign')
                ->references('id')
                ->on('customers')
                ->nullOnDelete();

            $table->foreign('received_by', 'payments_received_by_foreign')
                ->references('id')
                ->on('users')
                ->nullOnDelete();

            $table->foreign('created_by', 'payments_created_by_foreign')
                ->references('id')
                ->on('users')
                ->nullOnDelete();

            $table->foreign('updated_by', 'payments_updated_by_foreign')
                ->references('id')
                ->on('users')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};


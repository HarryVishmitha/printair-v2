<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('invoices', function (Blueprint $table) {
            if (! Schema::hasColumn('invoices', 'deposit_type')) {
                $table->string('deposit_type', 16)->nullable();
            }

            if (! Schema::hasColumn('invoices', 'deposit_value')) {
                $table->decimal('deposit_value', 12, 2)->nullable();
            }

            if (! Schema::hasColumn('invoices', 'deposit_required_amount')) {
                $table->decimal('deposit_required_amount', 12, 2)->nullable();
            }

            if (! Schema::hasColumn('invoices', 'pricing_frozen_at')) {
                $table->timestamp('pricing_frozen_at')->nullable();
            }

            if (! Schema::hasColumn('invoices', 'pricing_snapshot')) {
                $table->json('pricing_snapshot')->nullable();
            }
        });
    }

    public function down(): void
    {
        Schema::table('invoices', function (Blueprint $table) {
            if (Schema::hasColumn('invoices', 'pricing_snapshot')) {
                $table->dropColumn('pricing_snapshot');
            }

            if (Schema::hasColumn('invoices', 'pricing_frozen_at')) {
                $table->dropColumn('pricing_frozen_at');
            }

            if (Schema::hasColumn('invoices', 'deposit_required_amount')) {
                $table->dropColumn('deposit_required_amount');
            }

            if (Schema::hasColumn('invoices', 'deposit_value')) {
                $table->dropColumn('deposit_value');
            }

            if (Schema::hasColumn('invoices', 'deposit_type')) {
                $table->dropColumn('deposit_type');
            }
        });
    }
};


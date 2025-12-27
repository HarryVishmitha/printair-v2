<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('orders')) {
            return;
        }

        if (Schema::hasColumn('orders', 'final_grand_total')) {
            return;
        }

        Schema::table('orders', function (Blueprint $table) {
            $table->decimal('final_grand_total', 12, 2)->nullable()->after('grand_total');
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('orders')) {
            return;
        }

        if (! Schema::hasColumn('orders', 'final_grand_total')) {
            return;
        }

        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn('final_grand_total');
        });
    }
};


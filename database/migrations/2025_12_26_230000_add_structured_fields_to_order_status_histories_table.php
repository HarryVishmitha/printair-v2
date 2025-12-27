<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('order_status_histories', function (Blueprint $table) {
            if (! Schema::hasColumn('order_status_histories', 'why')) {
                $table->text('why')->nullable()->after('reason');
            }

            if (! Schema::hasColumn('order_status_histories', 'tracking_no')) {
                $table->string('tracking_no', 120)->nullable()->after('why');
            }

            if (! Schema::hasColumn('order_status_histories', 'vehicle_note')) {
                $table->text('vehicle_note')->nullable()->after('tracking_no');
            }

            if (! Schema::hasColumn('order_status_histories', 'pickup_note')) {
                $table->text('pickup_note')->nullable()->after('vehicle_note');
            }
        });
    }

    public function down(): void
    {
        Schema::table('order_status_histories', function (Blueprint $table) {
            if (Schema::hasColumn('order_status_histories', 'pickup_note')) {
                $table->dropColumn('pickup_note');
            }
            if (Schema::hasColumn('order_status_histories', 'vehicle_note')) {
                $table->dropColumn('vehicle_note');
            }
            if (Schema::hasColumn('order_status_histories', 'tracking_no')) {
                $table->dropColumn('tracking_no');
            }
            if (Schema::hasColumn('order_status_histories', 'why')) {
                $table->dropColumn('why');
            }
        });
    }
};


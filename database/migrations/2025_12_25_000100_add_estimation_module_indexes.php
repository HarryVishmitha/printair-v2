<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('estimates', function (Blueprint $table) {
            $table->index('valid_until', 'estimates_valid_until_index');
            $table->index(['working_group_id', 'valid_until'], 'estimates_wg_valid_until_index');
        });

        Schema::table('estimate_items', function (Blueprint $table) {
            $table->index(['working_group_id', 'roll_id'], 'estimate_items_wg_roll_id_index');
        });
    }

    public function down(): void
    {
        Schema::table('estimate_items', function (Blueprint $table) {
            $table->dropIndex('estimate_items_wg_roll_id_index');
        });

        Schema::table('estimates', function (Blueprint $table) {
            $table->dropIndex('estimates_wg_valid_until_index');
            $table->dropIndex('estimates_valid_until_index');
        });
    }
};


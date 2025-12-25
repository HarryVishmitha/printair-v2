<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('estimate_item_finishings')) {
            return;
        }

        if (Schema::hasColumn('estimate_item_finishings', 'deleted_at')) {
            return;
        }

        Schema::table('estimate_item_finishings', function (Blueprint $table) {
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('estimate_item_finishings')) {
            return;
        }

        if (! Schema::hasColumn('estimate_item_finishings', 'deleted_at')) {
            return;
        }

        Schema::table('estimate_item_finishings', function (Blueprint $table) {
            $table->dropSoftDeletes();
        });
    }
};


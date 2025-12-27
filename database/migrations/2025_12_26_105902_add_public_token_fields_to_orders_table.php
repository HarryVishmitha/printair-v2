<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            if (!Schema::hasColumn('orders', 'public_token_hash')) {
                $table->string('public_token_hash', 255)->nullable()->after('order_no');
            }

            if (!Schema::hasColumn('orders', 'public_token_last_sent_at')) {
                $table->timestamp('public_token_last_sent_at')->nullable()->after('public_token_hash');
            }

            if (!Schema::hasColumn('orders', 'public_token_expires_at')) {
                $table->timestamp('public_token_expires_at')->nullable()->after('public_token_last_sent_at');
            }
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            if (Schema::hasColumn('orders', 'public_token_expires_at')) {
                $table->dropColumn('public_token_expires_at');
            }
            if (Schema::hasColumn('orders', 'public_token_last_sent_at')) {
                $table->dropColumn('public_token_last_sent_at');
            }
            if (Schema::hasColumn('orders', 'public_token_hash')) {
                $table->dropColumn('public_token_hash');
            }
        });
    }
};

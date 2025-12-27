<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('invoices', function (Blueprint $table) {
            if (! Schema::hasColumn('invoices', 'public_token_hash')) {
                $table->string('public_token_hash', 64)->nullable()->after('meta');
            }

            if (! Schema::hasColumn('invoices', 'public_token_expires_at')) {
                $table->timestamp('public_token_expires_at')->nullable()->after('public_token_hash');
            }

            $table->index(['public_token_hash'], 'invoices_public_token_hash_index');
        });
    }

    public function down(): void
    {
        Schema::table('invoices', function (Blueprint $table) {
            $table->dropIndex('invoices_public_token_hash_index');

            if (Schema::hasColumn('invoices', 'public_token_expires_at')) {
                $table->dropColumn('public_token_expires_at');
            }

            if (Schema::hasColumn('invoices', 'public_token_hash')) {
                $table->dropColumn('public_token_hash');
            }
        });
    }
};


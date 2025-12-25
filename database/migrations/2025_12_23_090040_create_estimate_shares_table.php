<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('estimate_shares', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('estimate_id');
            $table->char('token_hash', 64);
            $table->dateTime('expires_at')->nullable();
            $table->dateTime('revoked_at')->nullable();
            $table->unsignedBigInteger('created_by')->nullable();
            $table->dateTime('last_accessed_at')->nullable();
            $table->unsignedInteger('access_count')->default(0);

            $table->timestamps();

            $table->unique('token_hash', 'estimate_shares_token_hash_unique');
            $table->index('estimate_id', 'estimate_shares_estimate_id_index');
            $table->index(['estimate_id', 'expires_at'], 'estimate_shares_estimate_expires_index');

            $table->foreign('estimate_id', 'estimate_shares_estimate_id_foreign')
                ->references('id')
                ->on('estimates')
                ->cascadeOnDelete();

            $table->foreign('created_by', 'estimate_shares_created_by_foreign')
                ->references('id')
                ->on('users')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('estimate_shares');
    }
};


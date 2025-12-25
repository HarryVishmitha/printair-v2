<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('estimate_status_histories', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('estimate_id');
            $table->string('from_status', 30)->nullable();
            $table->string('to_status', 30);
            $table->unsignedBigInteger('changed_by')->nullable();
            $table->string('reason', 500)->nullable();
            $table->json('meta')->nullable();
            $table->timestamp('created_at')->nullable();

            $table->index('estimate_id', 'est_status_hist_estimate_id_index');
            $table->index(['estimate_id', 'created_at'], 'est_status_hist_estimate_created_index');

            $table->foreign('estimate_id', 'est_status_hist_estimate_id_foreign')
                ->references('id')
                ->on('estimates')
                ->cascadeOnDelete();

            $table->foreign('changed_by', 'est_status_hist_changed_by_foreign')
                ->references('id')
                ->on('users')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('estimate_status_histories');
    }
};


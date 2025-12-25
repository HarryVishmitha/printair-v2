<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('estimate_share_otps', function (Blueprint $table) {
            $table->id();

            $table->foreignId('estimate_share_id')
                ->constrained('estimate_shares')
                ->cascadeOnDelete();

            $table->string('sent_to_email', 255);
            $table->string('code_hash', 255);

            $table->unsignedSmallInteger('attempts')->default(0);
            $table->timestamp('expires_at')->nullable();
            $table->timestamp('verified_at')->nullable();

            $table->string('ip', 45)->nullable();
            $table->string('user_agent', 500)->nullable();

            $table->timestamps();

            $table->index(['estimate_share_id', 'created_at']);
            $table->index(['estimate_share_id', 'expires_at']);
            $table->index(['estimate_share_id', 'verified_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('estimate_share_otps');
    }
};


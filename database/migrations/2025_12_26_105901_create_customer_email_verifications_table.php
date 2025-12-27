<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('customer_email_verifications', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('customer_id')->index();

            $table->string('email')->index();

            // store hashed OTP only
            $table->string('otp_hash', 255);

            $table->unsignedTinyInteger('attempts')->default(0);

            $table->timestamp('expires_at')->index();
            $table->timestamp('consumed_at')->nullable()->index();

            $table->string('ip', 64)->nullable();
            $table->string('user_agent', 255)->nullable();

            $table->timestamps();

            $table->foreign('customer_id')->references('id')->on('customers')->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('customer_email_verifications');
    }
};

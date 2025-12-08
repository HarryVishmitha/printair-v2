<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // Basic profile
            $table->string('first_name')->after('id');
            $table->string('last_name')->after('first_name');

            // Role + Working Group links
            $table->foreignId('role_id')
                ->nullable()
                ->after('last_name')
                ->constrained('roles')
                ->nullOnDelete();

            $table->foreignId('working_group_id')
                ->nullable()
                ->after('role_id')
                ->constrained('working_groups')
                ->nullOnDelete();

            // WhatsApp number
            $table->string('whatsapp_number', 20)
                ->nullable()
                ->after('email'); // change 'phone_number' if you use another column name

            // Auth / session related
            $table->timestamp('last_logged_in_at')
                ->nullable()
                ->after('remember_token');

            // 1 = logged in, 0 = logged out
            $table->boolean('login_status')
                ->default(false)
                ->after('last_logged_in_at')
                ->comment('1 = logged in, 0 = logged out');

            // Account status: active / inactive / suspended
            $table->enum('status', ['active', 'inactive', 'suspended'])
                ->default('active')
                ->after('login_status')
                ->index();

            // $table->timestamps(0)->after('status'); // Use 0 for precision if needed
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropConstrainedForeignId('role_id');
            $table->dropConstrainedForeignId('working_group_id');

            $table->dropColumn([
                'first_name',
                'last_name',
                'whatsapp_number',
                'last_logged_in_at',
                'login_status',
                'status',
            ]);
        });
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('customers', function (Blueprint $table) {
            $table->id();

            // Link to system user (optional – only if they have a login)
            $table->foreignId('user_id')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            // Every customer belongs to a working group (default: public)
            $table->foreignId('working_group_id')
                ->constrained('working_groups')
                ->restrictOnDelete();

            // Human-friendly code for quotations/invoices (e.g. CUST-00001)
            $table->string('customer_code', 30)->unique();

            // Core identity
            $table->string('full_name');               // "walk-in name" or main contact person
            $table->string('email')->nullable();       // optional for walk-ins
            $table->string('phone', 30);               // REQUIRED – main contact number
            $table->string('whatsapp_number', 30)->nullable();

            // Company info (for corporate / business clients)
            $table->string('company_name')->nullable();
            $table->string('company_phone', 30)->nullable();
            $table->string('company_reg_no', 50)->nullable(); // BR/VAT/TIN etc.

            // Segmentation
            $table->enum('type', ['walk_in', 'account', 'corporate'])
                ->default('walk_in');  // walk_in = typical counter customer

            $table->enum('status', ['active', 'inactive'])
                ->default('active');

            // Communication preferences
            $table->boolean('email_notifications')->default(true);
            $table->boolean('sms_notifications')->default(false);

            // Internal notes (important client details, instructions, etc.)
            $table->text('notes')->nullable();

            $table->timestamps();
            $table->softDeletes();
        });

        // Set default working_group_id to the Public group if available
        // (Optional but nice to have)
        if (Schema::hasTable('working_groups')) {
            $publicId = DB::table('working_groups')
                ->where('slug', \App\Models\WorkingGroup::PUBLIC_SLUG)
                ->value('id');

            if ($publicId) {
                Schema::table('customers', function (Blueprint $table) use ($publicId) {
                    $table->unsignedBigInteger('working_group_id')->default($publicId)->change();
                });
            }
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('customers');
    }
};

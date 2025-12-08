<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('working_groups', function (Blueprint $table) {
            $table->id();

            // Unique machine-readable key
            $table->string('slug')->unique();
            // Examples: 'public', 'canvas', 'corporate', 'restricted'

            // Human-friendly label
            $table->string('name')->unique();

            // Optional description for clarity
            $table->text('description')->nullable();

            // Whether this group’s designs can be shared
            $table->boolean('is_shareable')->default(true);

            // Whether group content is restricted to internal use only
            $table->boolean('is_restricted')->default(false);

            // Whether users in this group are considered staff
            // (used for dashboards, permissions, UX filters)
            $table->boolean('is_staff_group')->default(false);

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('working_groups');
    }
};

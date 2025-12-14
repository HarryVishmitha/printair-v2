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
        Schema::create('categories', function (Blueprint $table) {
            $table->id();

            // Multi-tenant / scope (optional but future-proof)
            // If you already have working_groups table, keep this.
            $table->foreignId('working_group_id')
                ->nullable()
                ->constrained()
                ->nullOnDelete();

            // Hierarchy (adjacency list)
            $table->foreignId('parent_id')
                ->nullable()
                ->constrained('categories')
                ->nullOnDelete();

            // Core
            $table->string('name', 160);
            $table->string('slug', 200); // unique (scoped with working_group_id)
            $table->string('code', 50)->nullable(); // internal SKU-like category code

            // Descriptions
            $table->string('short_description', 255)->nullable();
            $table->text('description')->nullable();

            // Media (store paths/keys; swap to media-library later if needed)
            $table->string('icon_path', 500)->nullable();
            $table->string('cover_image_path', 500)->nullable();

            // UI / behavior
            $table->unsignedInteger('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->boolean('is_featured')->default(false);
            $table->boolean('show_in_menu')->default(true);
            $table->boolean('show_in_navbar')->default(true);


            // SEO
            $table->string('seo_title', 160)->nullable();
            $table->string('seo_description', 255)->nullable();
            $table->string('seo_keywords', 255)->nullable();
            $table->string('og_image_path', 500)->nullable();
            $table->boolean('is_indexable')->default(true); // noindex control

            // Extensibility (future settings without migrations)
            $table->json('meta')->nullable();     // arbitrary key/value
            $table->json('settings')->nullable(); // UI flags / behavior config

            // Auditing basics
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();

            $table->timestamps();
            $table->softDeletes();

            // Indexes
            $table->index(['working_group_id', 'parent_id']);
            $table->index(['working_group_id', 'is_active', 'sort_order']);

            // Slug uniqueness should be scoped by working group (tenant-safe)
            // This allows same slug in different working groups if needed.
            $table->unique(['working_group_id', 'slug']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('categories');
    }
};

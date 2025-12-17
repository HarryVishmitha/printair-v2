<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('products', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('category_id')->nullable();

            $table->string('product_code', 40);
            $table->string('name', 200);
            $table->string('slug', 220);

            $table->enum('product_type', ['standard', 'dimension_based', 'finishing', 'service']);

            $table->enum('status', ['draft', 'active', 'inactive'])->default('draft');

            $table->enum('visibility', ['public', 'hidden', 'internal'])->default('public');

            $table->string('short_description', 255)->nullable();
            $table->longText('description')->nullable();

            // STANDARD
            $table->unsignedInteger('min_qty')->nullable();

            // DIMENSION BASED (store in inches)
            $table->boolean('requires_dimensions')->default(false);
            $table->boolean('allow_custom_size')->default(false);
            $table->boolean('allow_predefined_sizes')->default(true);

            $table->decimal('min_width_in', 10, 3)->nullable();
            $table->decimal('max_width_in', 10, 3)->nullable();
            $table->decimal('min_height_in', 10, 3)->nullable();
            $table->decimal('max_height_in', 10, 3)->nullable();

            $table->decimal('roll_max_width_in', 10, 3)->nullable();
            $table->boolean('allow_rotation_to_fit_roll')->default(true);

            // FINISHING
            $table->enum('finishing_charge_mode', ['per_piece', 'per_side', 'flat'])->nullable();
            $table->unsignedInteger('finishing_min_qty')->nullable();
            $table->unsignedInteger('finishing_max_qty')->nullable();

            // SERVICE
            $table->boolean('allow_manual_pricing')->default(false);

            $table->json('meta')->nullable();

            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();

            $table->timestamps();
            $table->softDeletes();

            $table->unique('product_code');
            $table->unique('slug');

            $table->index('category_id', 'products_category_id_index');
            $table->index(['product_type', 'status', 'visibility'], 'products_type_status_visibility_index');

            $table->foreign('category_id', 'products_category_id_foreign')
                ->references('id')
                ->on('categories')
                ->nullOnDelete();

            $table->foreign('created_by', 'products_created_by_foreign')
                ->references('id')
                ->on('users')
                ->nullOnDelete();

            $table->foreign('updated_by', 'products_updated_by_foreign')
                ->references('id')
                ->on('users')
                ->nullOnDelete();
        });

        Schema::create('product_seo', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('product_id');

            $table->string('seo_title', 160)->nullable();
            $table->string('seo_description', 255)->nullable();
            $table->string('seo_keywords', 255)->nullable();

            $table->string('og_title', 160)->nullable();
            $table->string('og_description', 255)->nullable();
            $table->string('og_image_path', 500)->nullable();

            $table->string('canonical_url', 500)->nullable();
            $table->boolean('is_indexable')->default(true);

            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();

            $table->timestamps();

            $table->unique('product_id', 'product_seo_product_id_unique');

            $table->foreign('product_id', 'product_seo_product_id_foreign')
                ->references('id')
                ->on('products')
                ->cascadeOnDelete();

            $table->foreign('created_by', 'product_seo_created_by_foreign')
                ->references('id')
                ->on('users')
                ->nullOnDelete();

            $table->foreign('updated_by', 'product_seo_updated_by_foreign')
                ->references('id')
                ->on('users')
                ->nullOnDelete();
        });

        Schema::create('product_images', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('product_id');

            $table->string('path', 600);
            $table->string('alt_text', 255)->nullable();
            $table->enum('role', ['cover', 'gallery', 'spec', 'mockup'])->default('gallery');

            $table->boolean('is_featured')->default(false);
            $table->unsignedInteger('sort_index')->default(0);

            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();

            $table->timestamps();
            $table->softDeletes();

            $table->index(['product_id', 'sort_index'], 'product_images_product_id_sort_index_index');
            $table->index(['product_id', 'is_featured'], 'product_images_product_id_is_featured_index');

            $table->foreign('product_id', 'product_images_product_id_foreign')
                ->references('id')
                ->on('products')
                ->cascadeOnDelete();

            $table->foreign('created_by', 'product_images_created_by_foreign')
                ->references('id')
                ->on('users')
                ->nullOnDelete();

            $table->foreign('updated_by', 'product_images_updated_by_foreign')
                ->references('id')
                ->on('users')
                ->nullOnDelete();
        });

        Schema::create('product_files', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('product_id');

            $table->string('label', 120);
            $table->string('file_path', 700);
            $table->enum('file_type', ['guideline', 'template', 'spec_sheet', 'other'])->default('other');

            $table->enum('visibility', ['public', 'internal'])->default('public');

            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();

            $table->timestamps();
            $table->softDeletes();

            $table->index(['product_id', 'visibility'], 'product_files_product_visibility_index');

            $table->foreign('product_id', 'product_files_product_id_foreign')
                ->references('id')
                ->on('products')
                ->cascadeOnDelete();

            $table->foreign('created_by', 'product_files_created_by_foreign')
                ->references('id')
                ->on('users')
                ->nullOnDelete();

            $table->foreign('updated_by', 'product_files_updated_by_foreign')
                ->references('id')
                ->on('users')
                ->nullOnDelete();
        });

        Schema::create('product_spec_groups', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('product_id');

            $table->string('name', 120);
            $table->unsignedInteger('sort_index')->default(0);
            $table->boolean('is_internal')->default(false);

            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();

            $table->timestamps();
            $table->softDeletes();

            $table->index(['product_id', 'sort_index'], 'product_spec_groups_product_sort_index');

            $table->foreign('product_id', 'product_spec_groups_product_id_foreign')
                ->references('id')
                ->on('products')
                ->cascadeOnDelete();

            $table->foreign('created_by', 'product_spec_groups_created_by_foreign')
                ->references('id')
                ->on('users')
                ->nullOnDelete();

            $table->foreign('updated_by', 'product_spec_groups_updated_by_foreign')
                ->references('id')
                ->on('users')
                ->nullOnDelete();
        });

        Schema::create('product_specs', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('product_id');
            $table->unsignedBigInteger('spec_group_id')->nullable();

            $table->string('spec_key', 120);
            $table->string('spec_value', 500);
            $table->boolean('is_internal')->default(false);
            $table->unsignedInteger('sort_index')->default(0);

            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();

            $table->timestamps();
            $table->softDeletes();

            $table->index(['product_id', 'spec_group_id', 'sort_index'], 'product_specs_product_group_sort_index');

            $table->foreign('product_id', 'product_specs_product_id_foreign')
                ->references('id')
                ->on('products')
                ->cascadeOnDelete();

            $table->foreign('spec_group_id', 'product_specs_spec_group_id_foreign')
                ->references('id')
                ->on('product_spec_groups')
                ->nullOnDelete();

            $table->foreign('created_by', 'product_specs_created_by_foreign')
                ->references('id')
                ->on('users')
                ->nullOnDelete();

            $table->foreign('updated_by', 'product_specs_updated_by_foreign')
                ->references('id')
                ->on('users')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('product_specs');
        Schema::dropIfExists('product_spec_groups');
        Schema::dropIfExists('product_files');
        Schema::dropIfExists('product_images');
        Schema::dropIfExists('product_seo');
        Schema::dropIfExists('products');
    }
};


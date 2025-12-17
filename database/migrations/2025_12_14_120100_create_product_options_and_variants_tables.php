<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('option_groups', function (Blueprint $table) {
            $table->id();
            $table->string('code', 50);
            $table->string('name', 120);
            $table->string('description', 255)->nullable();
            $table->timestamps();

            $table->unique('code', 'option_groups_code_unique');
            $table->unique('name', 'option_groups_name_unique');
        });

        Schema::create('options', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('option_group_id');
            $table->string('code', 80);
            $table->string('label', 160);
            $table->json('meta')->nullable();
            $table->timestamps();

            $table->unique(['option_group_id', 'code'], 'options_group_code_unique');
            $table->index('option_group_id', 'options_option_group_id_index');

            $table->foreign('option_group_id', 'options_option_group_id_foreign')
                ->references('id')
                ->on('option_groups')
                ->cascadeOnDelete();
        });

        Schema::create('product_option_groups', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('product_id');
            $table->unsignedBigInteger('option_group_id');
            $table->boolean('is_required')->default(true);
            $table->unsignedInteger('sort_index')->default(0);
            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['product_id', 'option_group_id'], 'product_option_groups_unique');
            $table->index(['product_id', 'sort_index'], 'product_option_groups_product_sort_index');

            $table->foreign('product_id', 'product_option_groups_product_id_foreign')
                ->references('id')
                ->on('products')
                ->cascadeOnDelete();

            $table->foreign('option_group_id', 'product_option_groups_option_group_id_foreign')
                ->references('id')
                ->on('option_groups')
                ->restrictOnDelete();

            $table->foreign('created_by', 'product_option_groups_created_by_foreign')
                ->references('id')
                ->on('users')
                ->nullOnDelete();

            $table->foreign('updated_by', 'product_option_groups_updated_by_foreign')
                ->references('id')
                ->on('users')
                ->nullOnDelete();
        });

        Schema::create('product_options', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('product_id');
            $table->unsignedBigInteger('option_id');
            $table->boolean('is_active')->default(true);
            $table->unsignedInteger('sort_index')->default(0);
            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['product_id', 'option_id'], 'product_options_unique');
            $table->index('product_id', 'product_options_product_id_index');
            $table->index('option_id', 'product_options_option_id_index');

            $table->foreign('product_id', 'product_options_product_id_foreign')
                ->references('id')
                ->on('products')
                ->cascadeOnDelete();

            $table->foreign('option_id', 'product_options_option_id_foreign')
                ->references('id')
                ->on('options')
                ->restrictOnDelete();

            $table->foreign('created_by', 'product_options_created_by_foreign')
                ->references('id')
                ->on('users')
                ->nullOnDelete();

            $table->foreign('updated_by', 'product_options_updated_by_foreign')
                ->references('id')
                ->on('users')
                ->nullOnDelete();
        });

        Schema::create('product_variant_sets', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('product_id');
            $table->string('code', 80)->nullable();
            $table->boolean('is_active')->default(true);
            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['product_id', 'is_active'], 'product_variant_sets_product_active_index');

            $table->foreign('product_id', 'product_variant_sets_product_id_foreign')
                ->references('id')
                ->on('products')
                ->cascadeOnDelete();

            $table->foreign('created_by', 'product_variant_sets_created_by_foreign')
                ->references('id')
                ->on('users')
                ->nullOnDelete();

            $table->foreign('updated_by', 'product_variant_sets_updated_by_foreign')
                ->references('id')
                ->on('users')
                ->nullOnDelete();
        });

        Schema::create('product_variant_set_items', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('variant_set_id');
            $table->unsignedBigInteger('option_id');

            $table->unique(['variant_set_id', 'option_id'], 'variant_set_option_unique');
            $table->index('option_id', 'variant_set_items_option_id_index');

            $table->foreign('variant_set_id', 'variant_set_items_variant_set_id_foreign')
                ->references('id')
                ->on('product_variant_sets')
                ->cascadeOnDelete();

            $table->foreign('option_id', 'variant_set_items_option_id_foreign')
                ->references('id')
                ->on('options')
                ->restrictOnDelete();
        });

        Schema::create('product_finishing_links', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('product_id');
            $table->unsignedBigInteger('finishing_product_id');

            $table->boolean('is_required')->default(false);
            $table->unsignedInteger('default_qty')->nullable();

            $table->unsignedInteger('min_qty')->nullable();
            $table->unsignedInteger('max_qty')->nullable();

            $table->boolean('is_active')->default(true);

            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['product_id', 'finishing_product_id'], 'product_finishing_links_unique');

            $table->foreign('product_id', 'product_finishing_links_product_id_foreign')
                ->references('id')
                ->on('products')
                ->cascadeOnDelete();

            $table->foreign('finishing_product_id', 'product_finishing_links_finishing_product_id_foreign')
                ->references('id')
                ->on('products')
                ->restrictOnDelete();

            $table->foreign('created_by', 'product_finishing_links_created_by_foreign')
                ->references('id')
                ->on('users')
                ->nullOnDelete();

            $table->foreign('updated_by', 'product_finishing_links_updated_by_foreign')
                ->references('id')
                ->on('users')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('product_finishing_links');
        Schema::dropIfExists('product_variant_set_items');
        Schema::dropIfExists('product_variant_sets');
        Schema::dropIfExists('product_options');
        Schema::dropIfExists('product_option_groups');
        Schema::dropIfExists('options');
        Schema::dropIfExists('option_groups');
    }
};


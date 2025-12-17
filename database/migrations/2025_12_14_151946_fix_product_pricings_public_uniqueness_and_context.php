<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('product_pricings')) {
            return;
        }

        // 1) Add generated key column to make (public + NULL WG) unique-safe
        // MySQL treats NULL as distinct in UNIQUE constraints, so we normalize NULL -> 0
        try {
            DB::statement('
                ALTER TABLE product_pricings
                ADD COLUMN working_group_key BIGINT UNSIGNED
                GENERATED ALWAYS AS (IFNULL(working_group_id, 0)) STORED
            ');
        } catch (\Throwable $e) {
            // Column may already exist; ignore safely
        }

        // 2) Add the uniqueness rule: one pricing per (product, context, WG)
        try {
            DB::statement('
                ALTER TABLE product_pricings
                ADD UNIQUE KEY product_pricings_unique_context
                (product_id, context, working_group_key)
            ');
        } catch (\Throwable $e) {
            // Index may already exist; ignore safely
        }

        // 3) Enforce correct context rules
        // public => working_group_id must be NULL
        // working_group => working_group_id must be NOT NULL
        try {
            DB::statement("
                ALTER TABLE product_pricings
                ADD CONSTRAINT chk_product_pricings_context
                CHECK (
                    (context = 'public' AND working_group_id IS NULL)
                    OR
                    (context = 'working_group' AND working_group_id IS NOT NULL)
                )
            ");
        } catch (\Throwable $e) {
            // Some MySQL setups may not support/enforce CHECK; ignore safely
        }
    }

    public function down(): void
    {
        if (! Schema::hasTable('product_pricings')) {
            return;
        }

        // Drop CHECK constraint (if exists)
        try {
            DB::statement('
                ALTER TABLE product_pricings
                DROP CHECK chk_product_pricings_context
            ');
        } catch (\Throwable $e) {
            // ignore
        }

        // Drop unique index (if exists)
        try {
            DB::statement('
                ALTER TABLE product_pricings
                DROP INDEX product_pricings_unique_context
            ');
        } catch (\Throwable $e) {
            // ignore
        }

        // Drop generated column (if exists)
        try {
            DB::statement('
                ALTER TABLE product_pricings
                DROP COLUMN working_group_key
            ');
        } catch (\Throwable $e) {
            // ignore
        }
    }
};

<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class WorkingGroupSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('working_groups')->insertOrIgnore([
            'slug' => 'public',
            'name' => 'Public',
            'description' => 'Public working group',
            'is_shareable' => true,
            'is_restricted' => false,
            'is_staff_group' => false,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
}

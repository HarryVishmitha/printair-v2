<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class SuperAdminSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $roleId = DB::table('roles')->where('name', 'Super Admin')->value('id');
        $workingGroupId = DB::table('working_groups')->where('slug', 'public')->value('id');

        if (!$roleId || !$workingGroupId) {
            $this->command->error('Role or Working Group not found. Please seed them first.');
            return;
        }

        DB::table('users')->insertOrIgnore([
            'first_name' => 'Super',
            'last_name' => 'Admin',
            'name' => 'Super Admin',
            'email' => 'superadmin@printair.com',
            'password' => Hash::make('SAdmin2119'),
            'role_id' => $roleId,
            'working_group_id' => $workingGroupId,
            'status' => 'active',
            'email_verified_at' => now(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
}

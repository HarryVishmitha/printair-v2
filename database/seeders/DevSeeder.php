<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DevSeeder extends Seeder
{
    public function run(): void
    {
        // Dev/demo dataset only. Keep it explicit:
        // `php artisan db:seed --class=DevSeeder`
        if (app()->environment('production')) {
            $this->command?->warn('Skipping DevSeeder in production environment.');
            return;
        }

        $this->call([
            ProductSeeder::class,
            QuotationSeeder::class,
        ]);
    }
}


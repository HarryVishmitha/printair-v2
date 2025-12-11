<?php

namespace Database\Seeders;

use App\Models\User;
use App\Notifications\SystemNotification;
use Illuminate\Database\Seeder;

class NotificationSeeder extends Seeder
{
    public function run(): void
    {
        $user = User::where('email', 'superadmin@printair.com')->first();

        if (! $user) {
            // Fallback to the first user if superadmin doesn't exist, for testing purposes
            $user = User::first();
            if ($user) {
                $this->command->info("User superadmin@printair.com not found. Seeding for {$user->email} instead.");
            } else {
                $this->command->error('No users found to seed notifications.');
                return;
            }
        }

        $notifications = [
            [
                'message' => 'Welcome to Printair! Your account has been successfully set up.',
                'url' => url('/dashboard'),
                'action' => 'Get Started',
                'type' => 'success',
            ],
            [
                'message' => 'New order #ORD-2025-001 has been placed requiring your approval.',
                'url' => '#',
                'action' => 'View Order',
                'type' => 'info',
            ],
            [
                'message' => 'System maintenance scheduled for Dec 12th at 02:00 AM UTC.',
                'url' => '#',
                'action' => 'Read More',
                'type' => 'warning',
            ],
            [
                'message' => 'Failed to process payment for Invoice #INV-9928.',
                'url' => '#',
                'action' => 'Retry Payment',
                'type' => 'error',
            ],
             [
                'message' => 'Your design file "Business_Card_v2.pdf" has been approved.',
                'url' => '#',
                'action' => 'Download',
                'type' => 'success',
            ],
        ];

        foreach ($notifications as $n) {
            $user->notify(new SystemNotification($n['message'], $n['url'], $n['action'], $n['type']));
        }
        
        $this->command->info("Notifications seeded for {$user->email}");
    }
}

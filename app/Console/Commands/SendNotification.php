<?php

namespace App\Console\Commands;

use App\Models\User;
use App\Notifications\SystemNotification;
use Illuminate\Console\Command;

class SendNotification extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'send:notification {email} {message} {--url=} {--type=info}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send a system notification to a specific user by email';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $email = $this->argument('email');
        $message = $this->argument('message');
        $url = $this->option('url');
        $type = $this->option('type');

        $user = User::where('email', $email)->first();

        if (! $user) {
            $this->error("User with email {$email} not found.");
            return;
        }

        $user->notify(new SystemNotification($message, $url, 'View', $type));

        $this->info("Notification sent to {$user->name} ({$email})");
    }
}

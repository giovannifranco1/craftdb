<?php

namespace App\Console\Commands;

use App\Models\Team\Invitation;
use Illuminate\Console\Command;

class CleanupAcceptedInvitations extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'invitations:cleanup {--days=90 : Number of days to keep accepted invitations}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Remove accepted invitations older than specified days (default: 90 days)';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $days = (int) $this->option('days');

        $this->info("Cleaning up accepted invitations older than {$days} days...");

        $deleted = Invitation::whereNotNull('accepted_at')
            ->where('accepted_at', '<', now()->subDays($days))
            ->delete();

        if ($deleted > 0) {
            $this->info("✓ Successfully deleted {$deleted} accepted invitation(s).");
        } else {
            $this->info("No accepted invitations found older than {$days} days.");
        }

        return Command::SUCCESS;
    }
}

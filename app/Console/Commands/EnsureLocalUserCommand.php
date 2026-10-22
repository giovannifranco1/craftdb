<?php

namespace App\Console\Commands;

use App\Actions\Local\EnsureLocalUser;
use Illuminate\Console\Command;

class EnsureLocalUserCommand extends Command
{
    protected $signature = 'craftdb:local-user';

    protected $description = 'Ensure the CraftDB Local user exists and print its access info';

    public function handle(EnsureLocalUser $ensureLocalUser): int
    {
        $email = (string) config('craftdb.local_user.email');

        $ensureLocalUser->exec($email);

        $this->newLine();
        $this->info('CraftDB Local está pronto');
        $this->line('  URL:    '.config('app.url'));
        $this->line('  E-mail: '.$email);
        $this->newLine();

        return self::SUCCESS;
    }
}

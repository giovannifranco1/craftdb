<?php

namespace App\Console\Commands;

use App\Actions\Local\EnsureLocalUser;
use Illuminate\Console\Command;

class EnsureLocalUserCommand extends Command
{
    protected $signature = 'craftdb:local-user';

    protected $description = 'Ensure the CraftDB Local user exists and print its credentials';

    public function handle(EnsureLocalUser $ensureLocalUser): int
    {
        $email = (string) config('craftdb.local_user.email');
        $password = (string) config('craftdb.local_user.password');

        $result = $ensureLocalUser->exec($email, $password);

        $this->newLine();
        $this->info('CraftDB Local está pronto');
        $this->line('  URL:    '.config('app.url'));
        $this->line('  E-mail: '.$email);
        $this->line('  Senha:  '.($result['created'] ? $password : '(inalterada — definida no primeiro boot)'));
        $this->newLine();

        return self::SUCCESS;
    }
}

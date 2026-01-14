<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\ActiveDirectory\AdUserService;

class AdUserTestPwReset extends Command
{
    protected $signature = 'ad:reset {user} {pw}';

    protected $description = 'AD Passwort Reset';

    public function handle()
    {
        $svc = new AdUserService();

        $user = $svc->find($this->argument('user'));

        if (!$user) {
            $this->error("User nicht gefunden");
            return self::FAILURE;
        }

        $r = $svc->resetPassword($user, $this->argument('pw'));

        if ($r === true) {
            $this->info("OK");
            return self::SUCCESS;
        }

        $this->error("Fehler: $r");
        return self::FAILURE;
    }
}

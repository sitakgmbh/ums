<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use LdapRecord\Container;
use LdapRecord\Models\ActiveDirectory\User as AdUser;
use Exception;

class LdapTest extends Command
{
    protected $signature = 'ldap:test 
                            {--user=test : Test-User fuer Query}';

    protected $description = 'Testet LDAP Bind + einfache Query via ldaprecord';

    public function handle(): int
    {
        $testUser = $this->option('user');

        $this->info("=== LDAP Test ===");

        try {
            // Verbindung holen
            $connection = Container::getDefaultConnection();

            $this->info("[1] Versuche zu binden...");
            $connection->connect();
            $this->info("    -> Bind OK");

        } catch (Exception $e) {
            $this->error("    -> Bind FEHLER: " . $e->getMessage());
            return self::FAILURE;
        }

        try {
            $this->info("[2] Versuche einfache Query...");
            $u = AdUser::query()
                ->whereEquals('samaccountname', $testUser)
                ->first();

            if ($u) {
                $this->info("    -> Query OK (User gefunden)");
                $this->line("    CN: " . ($u->cn[0] ?? 'unknown'));
            } else {
                $this->warn("    -> Query OK (User NICHT gefunden)");
            }

        } catch (Exception $e) {
            $this->error("    -> Query FEHLER: " . $e->getMessage());
            return self::FAILURE;
        }

        $this->info("[3] Fertig");
        return self::SUCCESS;
    }
}

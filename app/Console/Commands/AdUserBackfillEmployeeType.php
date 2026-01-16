<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\AdUser;
use App\Enums\AdUserEmployeeType;

class AdUserBackfillEmployeeType extends Command
{
    protected $signature = 'ad-users:backfill-employee-type {--dry-run}';
    protected $description = 'Befuellt employee_type basierend auf alten initials Regeln';

    public function handle()
    {
        $dryRun = $this->option('dry-run');

        $this->info('Starte Backfill fuer employee_type...');
        if ($dryRun) {
            $this->warn('DRY RUN aktiv – es werden keine Daten geschrieben');
        }

        $count = 0;

        AdUser::chunk(1000, function ($users) use (&$count, $dryRun) {
            foreach ($users as $user) {

                $initials = trim((string)$user->initials);
                $type = AdUserEmployeeType::Unknown;

                if ($initials === '00000') {
                    $type = AdUserEmployeeType::External;
                } elseif ($initials === '11111') {
                    $type = AdUserEmployeeType::Test;
                } elseif ($initials === '99999') {
                    $type = AdUserEmployeeType::InternalPending;
                } elseif (preg_match('/^[67][0-9]{4}$/', $initials)) {
                    $type = AdUserEmployeeType::Internal;
                }

                if ($user->employee_type !== $type) {
                    $count++;

                    if ($dryRun) {
                        $this->line("{$user->username}: {$user->employee_type?->value} → {$type->value}");
                    } else {
                        $user->employee_type = $type;
                        $user->save();
                    }
                }
            }
        });

        if ($dryRun) {
            $this->info("DRY RUN abgeschlossen – {$count} Benutzer wuerden aktualisiert");
        } else {
            $this->info("Backfill abgeschlossen – {$count} Benutzer aktualisiert");
        }

        return Command::SUCCESS;
    }
}

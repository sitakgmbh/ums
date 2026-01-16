<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\AdUser;
use App\Models\EmployeeLifecycle;
use App\Enums\EmployeeLifecycleEvent;

class GenerateAdCreationLifecycle extends Command
{
    protected $signature = 'employee-lifecycle:generate-ad-created';
    protected $description = 'Erstellt Lifecycle-Events fuer bestehende AD-Benutzer die bereits existieren.';

	public function handle(): int
	{
		$users = AdUser::query()
			->where('is_existing', true)
			->get();

		$count = 0;

		foreach ($users as $user) {

			$exists = EmployeeLifecycle::query()
				->where('ad_user_id', $user->id)
				->where('event', EmployeeLifecycleEvent::AdUserCreated->value)
				->exists();

			if ($exists) {
				$this->line("Skip: {$user->username} (Event existiert bereits)");
				continue;
			}

			EmployeeLifecycle::create([
				'ad_user_id'  => $user->id,
				'event'       => EmployeeLifecycleEvent::AdUserCreated->value,
				'description' => "Der AD-Benutzer '{$user->username}' wurde erstellt.",
				'context'     => [
					'info' => 'Dieser Eintrag wurde nachträglich erstellt.',
				],
				'event_at'    => $user->created,
			]);

			$this->info("Added: {$user->username}");
			$count++;
		}

		$this->info("Fertig. {$count} Eintraege erzeugt.");

		return self::SUCCESS;
	}

}

<?php

namespace App\Observers;

use Illuminate\Support\Facades\Auth;
use App\Models\AdUser;
use App\Utils\Logging\Logger;
use App\Models\EmployeeLifecycle;
use App\Enums\EmployeeLifecycleEvent;

class AdUserObserver
{
    private const IGNORED_FIELDS = [
        'modified',
		'created_at',
        'updated_at',
        'last_synced_at',
        'logon_count',
        'last_logon_date',
        'last_bad_password_attempt',
        'profile_photo_base64',
    ];

    public function created(AdUser $user): void
    {
        $actor = Auth::user();

        Logger::debug(
            "AD-Benutzer {$user->username} erstellt",
            [
                'actor_id'   => $actor?->id,
                'actor_user' => $actor?->username ?? 'system',
                'fullname'   => $actor
                    ? ($actor->name ?? trim(($actor->firstname ?? '') . ' ' . ($actor->lastname ?? '')))
                    : 'System',
                'username'   => $user->username,
                'data'       => $this->filterIgnoredFields(
                    $user->getAttributes()
                ),
            ]
        );

        EmployeeLifecycle::create([
            'ad_user_id'  => $user->id,
            'event'       => EmployeeLifecycleEvent::AdUserCreated->value,
            'description' => "Der AD-Benutzer '{$user->username}' wurde erstellt.",
            'context'     => [
                'source' => 'AdUserObserver',
            ],
            'event_at' => $user->created,
        ]);
    }

    public function updated(AdUser $user): void
    {
        $changes = $this->filterIgnoredFields(
            $user->getChanges()
        );

        if (empty($changes)) 
		{
            return;
        }

        $actor = Auth::user();

        Logger::debug(
            "AD-Benutzer {$user->username} geändert",
            [
                'actor_id'   => $actor?->id,
                'actor_user' => $actor?->username ?? 'system',
                'fullname'   => $actor
                    ? ($actor->name ?? trim(($actor->firstname ?? '') . ' ' . ($actor->lastname ?? '')))
                    : 'System',
                'username'   => $user->username,
                'changes'    => $changes,
                'original'   => array_intersect_key(
                    $user->getOriginal(),
                    $changes
                ),
            ]
        );
    }

    public function deleted(AdUser $user): void
    {
        $actor = Auth::user();

        Logger::debug(
            "AD-Benutzer {$user->username} gelöscht",
            [
                'actor_id'   => $actor?->id,
                'actor_user' => $actor?->username ?? 'system',
                'fullname'   => $actor
                    ? ($actor->name ?? trim(($actor->firstname ?? '') . ' ' . ($actor->lastname ?? '')))
                    : 'System',
                'username'   => $user->username,
                'data'       => $this->filterIgnoredFields(
                    $user->getOriginal()
                ),
            ]
        );
    }

    /**
     * Entfernt alle ignorierten Felder aus einem Aenderungs- oder Datenarray.
     */
    private function filterIgnoredFields(array $data): array
    {
        return array_diff_key(
            $data,
            array_flip(self::IGNORED_FIELDS)
        );
    }
}

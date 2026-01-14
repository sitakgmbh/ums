<?php

namespace App\Livewire\Pages\Admin\AdUsers;

use App\Models\AdUser;
use Livewire\Component;
use App\Services\Orbis\OrbisHelper;
use App\Utils\UserHelper;

class PwResetOrbis extends Component
{
    public AdUser $adUser;

    public string $orbisUsername = '';
    public string $orbisPassword = '';

    public bool $orbisFound = false;
    public bool $orbisLocked = false;
    public bool $orbisMustChange = false;

    public ?string $orbisError = null;

    public function mount(AdUser $adUser): void
    {
        $this->adUser = $adUser;
        $this->orbisUsername = strtoupper($adUser->username);
        $this->searchOrbisUser();
    }

    public function searchOrbisUser(): void
    {
        $this->orbisError = null;
        $this->orbisFound = false;

        if (trim($this->orbisUsername) === '') {
            $this->orbisError = 'Bitte ORBIS Benutzername eingeben.';
            return;
        }

        try {
            $helper = app(OrbisHelper::class);
            $user = $helper->getUserByUsername(strtoupper($this->orbisUsername));

            if (!$user || empty($user['id'])) {
                $this->orbisError = 'Benutzer in ORBIS nicht gefunden.';
                return;
            }

            $this->orbisFound = true;
            $this->orbisLocked = (bool)($user['locked'] ?? false);
            $this->orbisMustChange = (bool)($user['mustchangepassword'] ?? false);

        } catch (\Throwable $e) {
            $this->orbisError = 'Exception: ' . $e->getMessage();
        }
    }

    public function generateOrbisPassword(): void
    {
        $this->orbisPassword = UserHelper::generatePassword(12);
    }

    public function saveOrbis(): void
    {
        $this->orbisError = null;

        if (!$this->orbisFound) {
            $this->orbisError = 'Bitte zuerst Benutzer suchen.';
            return;
        }

        try {
            $helper = app(OrbisHelper::class);
            $username = strtoupper($this->orbisUsername);
            $user = $helper->getUserByUsername($username);

            if (!$user || empty($user['id'])) {
                $this->orbisError = 'Benutzer in ORBIS nicht gefunden.';
                return;
            }

            $userId = (int)$user['id'];
            $changed = false;

            // Lock/Unlock
            if ((bool)$user['locked'] !== $this->orbisLocked) {
                if ($this->orbisLocked) {
                    $helper->lockUser($userId);
                } else {
                    $helper->unlockUser($userId);
                }
                $changed = true;
            }

            // Passwort + MustChange
            if (trim($this->orbisPassword) !== '') {
                $helper->resetUserPassword(
                    $userId,
                    $username,
                    $this->orbisPassword,
                    $this->orbisMustChange
                );

                $this->orbisPassword = '';
                $changed = true;
            }

            if ($changed) {
                // Status neu laden für UI Konsistenz
                $this->searchOrbisUser();
            }

        } catch (\Throwable $e) {
            $this->orbisError = 'Exception: ' . $e->getMessage();
        }
    }

    public function render()
    {
        return view('livewire.pages.admin.ad-users.pw-reset-orbis');
    }
}

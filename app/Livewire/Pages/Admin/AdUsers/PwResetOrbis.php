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

    // API Current State
    public bool $orbisLockedCurrent = false;
    public bool $orbisMustChangeCurrent = false;

    // Radio Pending State
    public bool $orbisLockedPending = false;
    public bool $orbisMustChangePending = false;

    public ?array $orbisUser = null;

    public ?string $orbisError = null;
    public ?string $orbisSuccess = null;

    public function mount(AdUser $adUser)
    {
        $this->adUser = $adUser;
        $this->orbisUsername = strtoupper($adUser->username);
        $this->searchOrbisUser();
    }

	public function searchOrbisUser(): void
	{
		// Erfolg/Fehler hier NICHT mehr löschen!
		// $this->orbisError = null;
		// $this->orbisSuccess = null;

		$this->orbisFound = false;

		$username = strtoupper(trim($this->orbisUsername));
		if ($username === '') {
			$this->orbisError = 'Bitte ORBIS Benutzername eingeben.';
			return;
		}

		try {
			$helper = app(OrbisHelper::class);
			$result = $helper->getUserByUsername($username);

			if (!$result || empty($result['id'])) {
				$this->orbisError = 'Benutzer in ORBIS nicht gefunden.';
				return;
			}

			$this->orbisFound = true;
			$this->orbisUser = $result;

			$this->orbisLockedCurrent = (bool)($result['locked'] ?? false);
			$this->orbisMustChangeCurrent = (bool)($result['mustchangepassword'] ?? false);

			$this->orbisLockedPending = $this->orbisLockedCurrent;
			$this->orbisMustChangePending = $this->orbisMustChangeCurrent;

		} catch (\Throwable $e) {
			$this->orbisError = "Exception: {$e->getMessage()}";
		}
	}


    public function generateOrbisPassword(): void
    {
        $this->orbisPassword = UserHelper::generatePassword(12);
    }

    public function saveOrbis(): void
    {
        $this->orbisError = null;
        $this->orbisSuccess = null;

        if (!$this->orbisFound || !$this->orbisUser) {
            $this->orbisError = 'Bitte zuerst Benutzer suchen.';
            return;
        }

        if (trim($this->orbisPassword) === '') {
            $this->orbisError = 'Bitte neues Passwort eingeben.';
            return;
        }

        try {
            $helper = app(OrbisHelper::class);

            $ok = $helper->resetUserPw(
                $this->orbisUser['id'],
                $this->orbisPassword,
                $this->orbisLockedPending,
                $this->orbisMustChangePending
            );

            if (!$ok) {
                $this->orbisError = 'Fehler beim Speichern in ORBIS.';
                return;
            }

            $this->orbisSuccess = 'Einstellungen in ORBIS gespeichert.';
            $this->orbisPassword = '';

            $this->searchOrbisUser();

        } catch (\Throwable $e) {
            $this->orbisError = "Exception: {$e->getMessage()}";
        }
    }

    public function render()
    {
        return view('livewire.pages.admin.ad-users.pw-reset-orbis');
    }
}

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

    public ?array $orbisUser = null;
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
        $this->orbisUser = null;

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

            $this->orbisUser = $result;
            $this->orbisFound = true;

            $this->orbisLocked = (bool)($result['locked'] ?? false);
            $this->orbisMustChange = (bool)($result['mustchangepassword'] ?? false);

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

            $payload = [
                'id' => $this->orbisUser['id'],
                'name' => $this->orbisUser['name'],
                'validityperiod' => $this->orbisUser['validityperiod'],
                'canceled' => $this->orbisUser['canceled'] ?? false,
                'locked' => $this->orbisLocked,
                'mustchangepassword' => $this->orbisMustChange,
                'password' => base64_encode($this->orbisPassword)
            ];

            $resp = $helper->updateUserPayload($payload);

            if (!$resp) {
                $this->orbisError = 'Fehler beim Speichern in ORBIS.';
                return;
            }

            // UI refresh
            $this->orbisPassword = '';
            $this->searchOrbisUser();

        } catch (\Throwable $e) {
            $this->orbisError = 'Exception: ' . $e->getMessage();
        }
    }

    public function render()
    {
        return view('livewire.pages.admin.ad-users.pw-reset-orbis');
    }
}

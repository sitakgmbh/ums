<?php

namespace App\Livewire\Pages\Admin\AdUsers;

use App\Models\AdUser;
use Livewire\Component;
use Livewire\Attributes\Layout;
use App\Services\ActiveDirectory\AdUserService;
use App\Utils\UserHelper;

#[Layout("layouts.app")]
class PwReset extends Component
{
    public AdUser $adUser;

    // AD
    public string $adUsername = '';
	public string $adPassword = '';
    public bool $adUnlock = false;
    public bool $adToggleActive = false;
    public bool $adTogglePwdChange = false;
    public bool $adIsLocked = false;
    public bool $adIsDisabled = false;
    public bool $adRequiresPwdChange = false;
    public ?string $adError = null;
    public ?string $adSuccess = null;

    // ORBIS / KIS
    public string $orbisUsername = '';
    public string $orbisPassword = '';
	public bool $orbisFound = false;
    public bool $orbisMustChange = false;
    public ?string $orbisError = null;
    public ?string $orbisSuccess = null;

    public function mount(AdUser $adUser): void
    {
        $this->adUser = $adUser;
        $this->loadAdStatus();
		$this->adUsername = strtoupper($this->adUser->username);
        $this->orbisUsername = strtoupper($this->adUser->username);
		$this->loadOrbisStatus();
    }

    private function loadAdStatus(): void
    {
        $svc = new AdUserService();
        $ldap = $svc->findByGuid($this->adUser->guid);

        if (!$ldap) return;

        $uac = (int) ($ldap->userAccountControl[0] ?? 0);

        $this->adIsLocked = ($ldap->lockouttime > 0);
        $this->adIsDisabled = (($uac & 2) === 2);

        $pwdLastSet = $ldap->pwdlastset instanceof \Carbon\Carbon
            ? $ldap->pwdlastset->getTimestamp()
            : (int)$ldap->pwdlastset;

        $this->adRequiresPwdChange = ($pwdLastSet === 0);

        $this->adToggleActive = false;
        $this->adUnlock = $this->adIsLocked;
        $this->adTogglePwdChange = false;
    }

	private function loadOrbisStatus(): void
	{
		$this->orbisError = null;
		$this->orbisSuccess = null;

		try {
			$helper = app(\App\Services\Orbis\OrbisHelper::class);
			$username = strtoupper($this->orbisUsername);

			$response = $helper->getUserByUsername($username);

			if (!$response || empty($response['id'])) {
				$this->orbisError = "Benutzer in ORBIS nicht gefunden.";
				return;
			}

			$this->orbisMustChange = (bool)($response['mustchangepassword'] ?? false);

		} catch (\Throwable $e) {
			$this->orbisError = "Exception: " . $e->getMessage();
		}
	}

public function searchOrbisUser(): void
{
    $this->orbisError = null;
    $this->orbisSuccess = null;

    $username = strtoupper(trim($this->orbisUsername));

    if ($username === '') {
        $this->orbisError = 'Bitte zuerst einen Benutzernamen eingeben.';
        return;
    }

    try {
        $helper = app(\App\Services\Orbis\OrbisHelper::class);

        $user = $helper->getUserByUsername($username);

        if (!$user || empty($user['id'])) {
            $this->orbisError = 'Benutzer in ORBIS nicht gefunden.';
            $this->orbisFound = false;
            return;
        }

        $this->orbisFound = true;

        // optional Status laden
        $this->orbisMustChange = (bool)($user['mustchangepassword'] ?? false);

        $this->orbisSuccess = "Benutzer gefunden (ID {$user['id']})";

    } catch (\Throwable $e) {
        $this->orbisError = 'Exception: ' . $e->getMessage();
        $this->orbisFound = false;
    }
}


    public function generateAdPassword(): void
    {
        $this->adPassword = UserHelper::generatePassword(12);
    }

    public function generateOrbisPassword(): void
    {
        $this->orbisPassword = UserHelper::generatePassword(12);
    }

    public function saveAd(): void
    {
        $this->adError = null;
        $this->adSuccess = null;

        $svc = new AdUserService();
        $ldap = $svc->findByGuid($this->adUser->guid);

        if (!$ldap) {
            $this->adError = "Benutzer im AD nicht gefunden.";
            return;
        }

        $changed = false;

        if ($this->adUnlock && $this->adIsLocked) {
            $r = $svc->unlock($ldap);
            if ($r !== true) { $this->adError = $r; return; }
            $changed = true;
        }

        if ($this->adToggleActive) {
            if ($this->adIsDisabled) {
                $r = $svc->enable($ldap);
            } else {
                $r = $svc->disable($ldap);
            }
            if ($r !== true) { $this->adError = $r; return; }
            $changed = true;
        }

        if ($this->adTogglePwdChange) {
            if ($this->adRequiresPwdChange) {
                $r = $svc->clearForceChange($ldap);
            } else {
                $r = $svc->forceChangeOnNextLogin($ldap);
            }
            if ($r !== true) { $this->adError = $r; return; }
            $changed = true;
        }

        if (trim($this->adPassword) !== '') {

            $this->validate([
                'adPassword' => 'min:8',
            ], [
                'adPassword.min' => 'Das Passwort muss mindestens 8 Zeichen lang sein.'
            ]);

            $r = $svc->resetPassword($ldap, $this->adPassword);
            if ($r !== true) { $this->adError = $r; return; }

            $this->adPassword = '';
            $changed = true;
        }

        $this->adSuccess = $changed
            ? "Einstellungen erfolgreich aktualisiert."
            : "Keine Aenderungen vorgenommen.";

        $this->loadAdStatus();
    }

    public function saveOrbis(): void
    {
        $this->orbisError = null;
        $this->orbisSuccess = null;

        if (trim($this->orbisPassword) === '') {
            $this->orbisError = 'Bitte ein Passwort eingeben.';
            return;
        }

        if (trim($this->orbisUsername) === '') {
            $this->orbisError = 'Bitte einen Benutzername angeben.';
            return;
        }

        try {
            $helper = app(\App\Services\Orbis\OrbisHelper::class);

            $username = strtoupper($this->orbisUsername);
            $user = $helper->getUserByUsername($username);

            if (!$user || empty($user['id'])) {
                $this->orbisError = 'Benutzer in ORBIS nicht gefunden.';
                return;
            }

            $userId = (int)$user['id'];

            $r = $helper->resetUserPassword(
                $userId,
                $username,
                $this->orbisPassword,
                $this->orbisMustChange
            );

            if ($r !== true) {
                $this->orbisError = 'Fehler beim Passwort-Reset.';
                return;
            }

            $this->orbisSuccess = 'Passwort in ORBIS aktualisiert.';

        } catch (\Throwable $e) {
            $this->orbisError = 'Exception: ' . $e->getMessage();
        }
    }

    public function render()
    {
        $ad = $this->adUser;
        $sap = $ad->sapExport ?? null;

        return view('livewire.pages.admin.ad-users.pw-reset', compact('ad', 'sap'))
            ->layoutData([
                'pageTitle' => 'Account ' . ($ad->display_name ?? $ad->username)
            ]);
    }
}

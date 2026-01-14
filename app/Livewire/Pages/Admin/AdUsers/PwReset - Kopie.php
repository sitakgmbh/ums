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

    public string $password = '';

    public bool $unlock = false;
    public bool $toggleActive = false;
    public bool $togglePwdChange = false;

    public bool $isLocked = false;
    public bool $isDisabled = false;
    public bool $requiresPwdChange = false;

    public ?string $error = null;
    public ?string $success = null;

    public function mount(AdUser $adUser): void
    {
        $this->adUser = $adUser;
        $this->loadStatus();
    }

    private function loadStatus(): void
    {
        $svc = new AdUserService();
        $ldap = $svc->findByGuid($this->adUser->guid);

        if (!$ldap) {
            return;
        }

        $uac = (int) ($ldap->userAccountControl[0] ?? 0);

        $this->isLocked = ($ldap->lockouttime > 0);
        $this->isDisabled = (($uac & 2) === 2);

        $pwdLastSet = $ldap->pwdlastset instanceof \Carbon\Carbon
            ? $ldap->pwdlastset->getTimestamp()
            : (int) $ldap->pwdlastset;

        $this->requiresPwdChange = ($pwdLastSet === 0);

        $this->toggleActive = false;
        $this->unlock = $this->isLocked;
        $this->togglePwdChange = false;
    }

    public function generatePassword(): void
    {
        $this->password = UserHelper::generatePassword(12);
    }

    public function save(): void
    {
        $this->resetMessages();

        $svc = new AdUserService();
        $ldap = $svc->findByGuid($this->adUser->guid);

        if (!$ldap) {
            $this->error = "Benutzer im AD nicht gefunden.";
            return;
        }

        $changed = false;

        // Unlock
        if ($this->unlock && $this->isLocked) {
            $r = $svc->unlock($ldap);
            if ($r !== true) { $this->error = $r; return; }
            $changed = true;
        }

        // Aktivieren/Deaktivieren
        if ($this->toggleActive) {
            if ($this->isDisabled) {
                $r = $svc->enable($ldap);
            } else {
                $r = $svc->disable($ldap);
            }
            if ($r !== true) { $this->error = $r; return; }
            $changed = true;
        }

        // Passwort beim nächsten Login ändern
        if ($this->togglePwdChange) {
            if ($this->requiresPwdChange) {
                $r = $svc->clearForceChange($ldap);
            } else {
                $r = $svc->forceChangeOnNextLogin($ldap);
            }
            if ($r !== true) { $this->error = $r; return; }
            $changed = true;
        }

        // Passwort setzen nur wenn nicht leer
        if (trim($this->password) !== '') {

            $this->validate([
                'password' => 'min:8'
            ], [
                'password.min' => 'Das Passwort muss mindestens 8 Zeichen lang sein.'
            ]);

            $r = $svc->resetPassword($ldap, $this->password);
            if ($r !== true) {
                $this->error = $r;
                return;
            }

            $this->password = '';
            $changed = true;
        }

        if ($changed) {
            $this->success = "Einstellungen erfolgreich aktualisiert.";
            $this->loadStatus();
        } else {
            $this->success = "Keine Änderungen vorgenommen.";
        }
    }

    private function resetMessages(): void
    {
        $this->resetErrorBag();
        $this->error = null;
        $this->success = null;
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

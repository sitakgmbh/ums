<?php

namespace App\Livewire\Pages\Admin\AdUsers;

use App\Models\AdUser;
use Livewire\Component;
use App\Services\ActiveDirectory\AdUserService;
use App\Utils\UserHelper;

class PwResetAd extends Component
{
    public AdUser $adUser;

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

    public function mount(AdUser $adUser): void
    {
        $this->adUser = $adUser;
        $this->adUsername = strtoupper($adUser->username);

        $this->loadAdStatus();
    }

    private function loadAdStatus(): void
    {
        $svc = new AdUserService();
        $ldap = $svc->findByGuid($this->adUser->guid);

        if (!$ldap) {
            return;
        }

        $uac = (int) ($ldap->userAccountControl[0] ?? 0);

        $this->adIsLocked = ($ldap->lockouttime > 0);
        $this->adIsDisabled = (($uac & 2) === 2);

        $pwdLastSet = $ldap->pwdlastset instanceof \Carbon\Carbon
            ? $ldap->pwdlastset->getTimestamp()
            : (int) ($ldap->pwdlastset ?? 0);

        $this->adRequiresPwdChange = ($pwdLastSet === 0);

        $this->adToggleActive = false;
        $this->adUnlock = $this->adIsLocked;
        $this->adTogglePwdChange = false;
    }

    public function generateAdPassword(): void
    {
        $this->adPassword = UserHelper::generatePassword(12);
    }

    public function saveAd(): void
    {
        $this->resetMessages();

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
                'adPassword' => 'min:8'
            ], [
                'adPassword.min' => 'Das Passwort muss mindestens 8 Zeichen lang sein.'
            ]);

            $r = $svc->resetPassword($ldap, $this->adPassword);
            if ($r !== true) {
                $this->adError = $r;
                return;
            }

            $this->adPassword = '';
            $changed = true;
        }

        if ($changed) {
            $this->adSuccess = "Einstellungen erfolgreich aktualisiert.";
            $this->loadAdStatus();
        } else {
            $this->adSuccess = "Keine Aenderungen vorgenommen.";
        }
    }

    private function resetMessages(): void
    {
        $this->resetErrorBag();
        $this->adError = null;
        $this->adSuccess = null;
    }

    public function render()
    {
        return view('livewire.pages.admin.ad-users.pw-reset-ad');
    }
}

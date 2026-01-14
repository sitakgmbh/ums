<?php

namespace App\Livewire\Pages\Admin\AdUsers;

use App\Models\AdUser;
use Livewire\Component;
use App\Services\ActiveDirectory\AdUserService;
use App\Utils\UserHelper;
use App\Utils\Logging\Logger;

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

        if (!$ldap) 
		{
            return;
        }

        $uac = (int)($ldap->userAccountControl[0] ?? 0);

        $this->adIsLocked = ($ldap->lockouttime > 0);
        $this->adIsDisabled = (($uac & 2) === 2);

        $pwdLastSet = $ldap->pwdlastset instanceof \Carbon\Carbon ? $ldap->pwdlastset->getTimestamp() : (int)($ldap->pwdlastset ?? 0);
        $this->adRequiresPwdChange = ($pwdLastSet === 0);

        $this->adToggleActive = false;
        $this->adUnlock = $this->adIsLocked;
        $this->adTogglePwdChange = false;
    }

    public function generateAdPassword(): void
    {
        $this->adPassword = UserHelper::generatePassword();
    }

    private function logAdFailure(string $error): void
    {
        Logger::db("ad", "error", "Passwort-Änderung '{$this->adUsername}' fehlgeschlagen", [
            "unlock"   => $this->adUnlock       ? true : null,
            "active"   => $this->adToggleActive ? true : null,
            "must_pwd" => $this->adTogglePwdChange ? true : null,
            "pw_set"   => trim($this->adPassword) !== '' ? true : null,
            "actor"    => auth()->user()?->username ?? null,
            "ip"       => request()->ip(),
            "agent"    => request()->userAgent() ?: null,
            "error"    => $error,
        ]);
    }

    public function saveAd(): void
    {
        $this->resetMessages();

        $svc = new AdUserService();
        $ldap = $svc->findByGuid($this->adUser->guid);

        if (!$ldap) 
		{
            $this->logAdFailure("Benutzer nicht im AD gefunden");
            $this->adError = "Benutzer nicht im AD gefunden.";
            return;
        }

        $changed = false;

        if ($this->adUnlock && $this->adIsLocked) 
		{
            $r = $svc->unlock($ldap);
			
            if ($r !== true) 
			{
                $this->logAdFailure($r);
                $this->adError = $r;
                return;
            }
			
            $changed = true;
        }

        if ($this->adToggleActive) 
		{
            $r = $this->adIsDisabled ? $svc->enable($ldap) : $svc->disable($ldap);
			
            if ($r !== true) 
			{
                $this->logAdFailure($r);
                $this->adError = $r;
                return;
            }
			
            $changed = true;
        }

        if ($this->adTogglePwdChange) 
		{
            $r = $this->adRequiresPwdChange ? $svc->clearForceChange($ldap) : $svc->forceChangeOnNextLogin($ldap);
			
            if ($r !== true) 
			{
                $this->logAdFailure($r);
                $this->adError = $r;
                return;
            }
			
            $changed = true;
        }

        if (trim($this->adPassword) !== '') 
		{
            $this->validate([
                'adPassword' => 'min:8'
            ], [
                'adPassword.min' => 'Das Passwort muss mindestens 8 Zeichen lang sein.'
            ]);

            $r = $svc->resetPassword($ldap, $this->adPassword);
			
            if ($r !== true) 
			{
                $this->logAdFailure($r);
                $this->adError = $r;
                return;
            }

            $this->adPassword = '';
            $changed = true;
        }

        if ($changed) 
		{
            Logger::db("ad", "info", "Passwort-Änderung '{$this->adUsername}' erfolgreich", [
                "unlock"   => $this->adUnlock       ? true : null,
                "active"   => $this->adToggleActive ? true : null,
                "must_pwd" => $this->adTogglePwdChange ? true : null,
                "pw_set"   => trim($this->adPassword) !== '' ? true : null,
                "actor"    => auth()->user()?->username ?? null,
                "ip"       => request()->ip(),
                "agent"   => request()->userAgent() ?: null,
            ]);

            $this->adSuccess = "Änderungen erfolgreich gespeichert.";
            $this->loadAdStatus();
        }
		else 
		{
            $this->adSuccess = "Keine Änderungen vorgenommen.";
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

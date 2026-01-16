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

    // UI Toggles
    public bool $adUnlock = false;
    public bool $adChangePassword = false;
    public bool $adForcePwdChange = false;

    // AD Status
	public bool $adIsDisabled = false;
    public bool $adIsLocked = false;
    public bool $adRequiresPwdChange = false;

    // Feedback
    public ?string $adError = null;
    public ?string $adSuccess = null;

	public function mount(AdUser $adUser): void
	{
		$this->adUser = $adUser;
		$this->adUsername = strtoupper($adUser->username);

		try {
			$this->loadAdStatus();
		} catch (\Throwable $e) {

			$this->adError = "Verbindung zu Active Directory fehlgeschlagen.";
			return;
		}
	}

	private function loadAdStatus(): void
	{
		$svc = new AdUserService();

		try {
			$ldap = $svc->findByGuid($this->adUser->guid);
		} catch (\Throwable $e) {
			$this->adError = "Verbindung zu Active Directory fehlgeschlagen.";
			return;
		}

		if (!$ldap) {
			$this->adError = "Benutzer wurde im Active Directory nicht gefunden.";
			return;
		}

		$uac = (int) $ldap->getFirstAttribute('userAccountControl');
		$this->adIsDisabled = ($uac & 0x0002) === 0x0002;

		$lock = $ldap->lockouttime ?? null;
		$pwdLastSet = $ldap->pwdlastset instanceof \Carbon\Carbon ? $ldap->pwdlastset->getTimestamp() : (int) ($ldap->pwdlastset ?? 0);

		$this->adRequiresPwdChange = ($pwdLastSet === 0);
	}

    public function generateAdPassword(): void
    {
        $this->adPassword = UserHelper::generatePassword();
    }

    private function logAdFailure(string $error): void
    {
        Logger::db("ad", "error", "Änderung AD-Passwort '{$this->adUsername}' fehlgeschlagen", [
            "unlock"     => $this->adUnlock ?: null,
            "pw_change"  => $this->adChangePassword ?: null,
            "force_pw"   => $this->adForcePwdChange ?: null,
            "actor"      => auth()->user()?->username,
            "ip"         => request()->ip(),
            "agent"      => request()->userAgent(),
            "error"      => $error,
        ]);
    }

    private function logAdSuccess(): void
    {
        Logger::db("ad", "info", "Änderung AD-Passwort '{$this->adUsername}' erfolgreich", [
            "unlock"     => $this->adUnlock ?: null,
            "pw_change"  => $this->adChangePassword ?: null,
            "force_pw"   => $this->adForcePwdChange ?: null,
            "actor"      => auth()->user()?->username,
            "ip"         => request()->ip(),
            "agent"      => request()->userAgent(),
        ]);
    }

    public function saveAd(): void
    {
        $this->adError = null;
        $this->adSuccess = null;

        $svc = new AdUserService();
        $ldap = $svc->findByGuid($this->adUser->guid);

        if (!$ldap) {
            $this->logAdFailure("Benutzer nicht im AD gefunden");
            $this->adError = "Benutzer nicht im AD gefunden.";
            return;
        }

        $changed = false;

        // Unlock
        if ($this->adUnlock && $this->adIsLocked) {
            $r = $svc->unlock($ldap);

            if ($r !== true) {
                $this->logAdFailure($r);
                $this->adError = $r;
                return;
            }

            $changed = true;
            $this->adUnlock = false;
        }

		// 2) Passwort ändern
		if ($this->adChangePassword) {
			$pw = trim($this->adPassword);

			if ($pw === '') {
				$this->adError = "Bitte Passwort eingeben.";
				return;
			}

			if (strlen($pw) < 8) {
				$this->adError = "Das Passwort muss mindestens 8 Zeichen lang sein.";
				return;
			}

			$r = $svc->resetPassword($ldap, $pw);

			if ($r !== true) {
				$this->logAdFailure($r);
				$this->adError = $r;
				return;
			}

			if ($this->adForcePwdChange) {
				$svc->forceChangeOnNextLogin($ldap);
			} else {
				$svc->clearForceChange($ldap);
			}

			$this->adPassword = '';
			$changed = true;
		}


        if ($changed) {
            $this->logAdSuccess();
            $this->adSuccess = "Änderungen erfolgreich gespeichert.";
            $this->loadAdStatus();
        } else {
            $this->adSuccess = "Keine Änderungen vorgenommen.";
        }
    }

    public function render()
    {
        return view('livewire.pages.admin.ad-users.pw-reset-ad');
    }
}

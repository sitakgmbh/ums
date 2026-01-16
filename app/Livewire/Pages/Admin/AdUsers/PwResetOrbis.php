<?php

namespace App\Livewire\Pages\Admin\AdUsers;

use App\Models\AdUser;
use Livewire\Component;
use App\Services\Orbis\OrbisHelper;
use App\Utils\UserHelper;
use App\Utils\Logging\Logger;

class PwResetOrbis extends Component
{
    public AdUser $adUser;

    public string $orbisUsername = '';
    public string $orbisPassword = '';

    public bool $orbisFound = false;
    public bool $orbisIsLocked = false;

    // UI toggles
    public bool $orbisUnlock = false;
    public bool $orbisChangePassword = false;
    public bool $orbisForcePwdChange = false;

    public ?array $orbisUser = null;

    public ?string $orbisError = null;
    public ?string $orbisSuccess = null;

    public function mount(AdUser $adUser)
    {
        $this->adUser = $adUser;
        $this->orbisUsername = strtoupper($adUser->username);
        $this->searchOrbisUser();
    }

    private function logOrbisFailure(string $error): void
    {
        Logger::db("orbis", "error", "Änderung Orbis-Passwort '{$this->orbisUsername}' fehlgeschlagen", [
            "user_id"   => $this->orbisUser['id'] ?? null,
            "unlock"    => $this->orbisUnlock ?: null,
            "pw_change" => $this->orbisChangePassword ?: null,
            "must_pwd"  => $this->orbisForcePwdChange ?: null,
            "actor"     => auth()->user()?->username,
            "ip"        => request()->ip(),
            "agent"     => substr(request()->userAgent() ?: '', 0, 200),
            "error"     => $error,
        ]);
    }

    private function logOrbisSuccess(): void
    {
        Logger::db("orbis", "info", "Änderung Orbis-Passwort '{$this->orbisUsername}' erfolgreich", [
            "user_id"   => $this->orbisUser['id'] ?? null,
            "unlock"    => $this->orbisUnlock ?: null,
            "pw_change" => $this->orbisChangePassword ?: null,
            "must_pwd"  => $this->orbisForcePwdChange ?: null,
            "actor"     => auth()->user()?->username,
            "ip"        => request()->ip(),
            "agent"     => substr(request()->userAgent() ?: '', 0, 200),
        ]);
    }

	public function searchOrbisUser(): void
	{
		$this->orbisError = null;
		$this->orbisSuccess = null;
		$this->orbisFound = false;
		$this->orbisUser = null;

		$username = strtoupper(trim($this->orbisUsername));
		if ($username === '') {
			$this->orbisError = 'Bitte ORBIS Kürzel eingeben.';
			return;
		}

		try {
			$helper = app(OrbisHelper::class);

			// STEP 1: User via Name suchen
			$user = $helper->getUserByUsername($username);
			if (!$user || empty($user['id'])) {
				$this->orbisError = 'Benutzer nicht in ORBIS gefunden.';
				return;
			}

			// STEP 2: User Details via ID holen
			$full = $helper->getUserById((int)$user['id']);
			if (!$full) {
				$this->orbisError = 'Benutzer konnte nicht geladen werden.';
				return;
			}

			// Update State
			$this->orbisFound = true;
			$this->orbisUser = $full;

			$this->orbisIsLocked = (bool)($full['locked'] ?? false);
			$this->orbisLockedCurrent = $this->orbisIsLocked;

			$this->orbisUnlock = false;
			$this->orbisChangePassword = false;
			// $this->orbisForcePwdChange = (bool)($full['mustchangepassword'] ?? false);
			$this->orbisForcePwdChange = false;

			$this->orbisPassword = '';

		} catch (\Throwable $e) {
			$this->orbisError = "Exception: ".$e->getMessage();
			return;
		}
	}

    public function generateOrbisPassword(): void
    {
        $this->orbisPassword = UserHelper::generatePassword();
    }

    public function saveOrbis(): void
    {
        $this->orbisError = null;
        $this->orbisSuccess = null;

        if (!$this->orbisFound || !$this->orbisUser) {
            $this->orbisError = 'Bitte zuerst Benutzer suchen.';
            return;
        }

        $helper = app(OrbisHelper::class);
        $changed = false;

        // 1) Unlock
        if ($this->orbisUnlock && $this->orbisIsLocked) {
            if (!$helper->unlockUser($this->orbisUser['id'])) {
                $this->logOrbisFailure("Entsperren fehlgeschlagen");
                $this->orbisError = "Entsperren fehlgeschlagen.";
                return;
            }
            $changed = true;
            $this->orbisUnlock = false;
        }

        // 2) Password
        if ($this->orbisChangePassword) {
            $pw = trim($this->orbisPassword);

            if ($pw === '') {
                $this->orbisError = "Bitte Passwort eingeben.";
                return;
            }

            if (strlen($pw) < 8) {
                $this->orbisError = "Das Passwort muss mindestens 8 Zeichen lang sein.";
                return;
            }

            if (!$helper->resetUserPw($this->orbisUser['id'], $pw, $this->orbisForcePwdChange)) {
                $this->logOrbisFailure("Änderung Passwort fehlgeschlagen");
                $this->orbisError = "Änderung Passwort fehlgeschlagen.";
                return;
            }

            $this->orbisPassword = '';
            $changed = true;
        }

        // Final
        if ($changed) {
            $this->logOrbisSuccess();
            $this->searchOrbisUser();
            $this->orbisSuccess = "Änderungen erfolgreich gespeichert.";
        } else {
            $this->orbisSuccess = "Keine Änderungen vorgenommen.";
        }
    }

    public function render()
    {
        return view('livewire.pages.admin.ad-users.pw-reset-orbis');
    }
}

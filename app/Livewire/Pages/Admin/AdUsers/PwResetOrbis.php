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
            $result = $helper->getUserByUsername($username);

            if (!$result || empty($result['id'])) {
                $this->orbisError = 'Benutzer nicht in ORBIS gefunden.';
                return;
            }

            $this->orbisFound = true;
            $this->orbisUser = $result;

            $this->orbisIsLocked = (bool)($result['locked'] ?? false);

            $this->orbisUnlock = false;
            $this->orbisChangePassword = false;
            $this->orbisForcePwdChange = false;
            $this->orbisPassword = '';

        } catch (\Throwable $e) {
            $this->orbisError = "Exception: ".$e->getMessage();
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

        // Unlock
        if ($this->orbisUnlock && $this->orbisIsLocked) {
            $ok = $helper->unlockUser($this->orbisUser['id']);

            if (!$ok) {
                $this->orbisError = "Fehler beim Entsperren.";
                return;
            }

            $changed = true;
            $this->orbisUnlock = false;
        }

        // Password
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

            $mustChange = $this->orbisForcePwdChange;

            $ok = $helper->resetUserPw($this->orbisUser['id'], $pw, $mustChange);

            if (!$ok) {
                Logger::db("orbis", "error", "Passwort-Änderung '{$this->orbisUsername}' fehlgeschlagen", [
                    "user_id" => $this->orbisUser['id'],
                    "actor" => auth()->user()?->username,
                    "ip" => request()->ip(),
                    "agent" => request()->userAgent(),
                ]);

                $this->orbisError = "Passwortänderung fehlgeschlagen.";
                return;
            }

            $this->orbisPassword = '';
            $changed = true;
        }

        if ($changed) {

            Logger::db("orbis", "info", "Passwort-Änderung '{$this->orbisUsername}' erfolgreich", [
                "user_id" => $this->orbisUser['id'],
                "actor" => auth()->user()?->username,
                "ip" => request()->ip(),
                "agent" => request()->userAgent(),
            ]);

            $this->orbisSuccess = "Änderungen erfolgreich gespeichert.";
            $this->searchOrbisUser();

        } else {
            $this->orbisSuccess = "Keine Änderungen vorgenommen.";
        }
    }

    public function render()
    {
        return view('livewire.pages.admin.ad-users.pw-reset-orbis');
    }
}

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

    public bool $orbisLockedCurrent = false;
    public bool $orbisMustChangeCurrent = false;

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
		$this->orbisFound = false;

		$username = strtoupper(trim($this->orbisUsername));
		
		if ($username === '') {
			$this->orbisError = 'Bitte ORBIS Kürzel eingeben.';
			return;
		}

		try 
		{
			$helper = app(OrbisHelper::class);
			$result = $helper->getUserByUsername($username);

			if (!$result || empty($result['id'])) 
			{
				$this->orbisError = 'Benutzer nicht in ORBIS gefunden.';
				return;
			}

			$this->orbisFound = true;
			$this->orbisUser = $result;

			$this->orbisLockedCurrent = (bool)($result['locked'] ?? false);
			$this->orbisMustChangeCurrent = (bool)($result['mustchangepassword'] ?? false);

			$this->orbisLockedPending = $this->orbisLockedCurrent;
			$this->orbisMustChangePending = $this->orbisMustChangeCurrent;

		} 
		catch (\Throwable $e) 
		{
			$this->orbisError = "Exception: {$e->getMessage()}";
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

        if (!$this->orbisFound || !$this->orbisUser) 
		{
            $this->orbisError = 'Bitte zuerst Benutzer suchen.';
            return;
        }

        if (trim($this->orbisPassword) === '') 
		{
            $this->orbisError = 'Bitte neues Passwort eingeben.';
            return;
        }

		try 
		{
			$helper = app(OrbisHelper::class);

			$ok = $helper->resetUserPw(
				$this->orbisUser['id'],
				$this->orbisPassword,
				$this->orbisLockedPending,
				$this->orbisMustChangePending
			);

			if (!$ok) 
			{
				$this->orbisError = 'Fehler beim Speichern der Änderungen.';

				Logger::db("orbis", "error", "Passwort-Änderung '{$this->orbisUsername}' fehlgeschlagen", [
					"user_id" => $this->orbisUser['id'],
					"pw_set"  => trim($this->orbisPassword) !== '' ? true : null,
					"locked"  => $this->orbisLockedPending ? true : null,
					"must_pwd"=> $this->orbisMustChangePending ? true : null,
					"actor"   => auth()->user()?->username ?? null,
					"ip"      => request()->ip(),
					"agent"   => request()->userAgent() ?: null,
				]);

				return;
			}

			$payload = [
				"user_id" => $this->orbisUser['id'],
				"pw_set"  => trim($this->orbisPassword) !== '' ? true : null,
				"locked"  => $this->orbisLockedPending ? true : null,
				"must_pwd"=> $this->orbisMustChangePending ? true : null,
				"actor"   => auth()->user()?->username ?? null,
				"ip"      => request()->ip(),
			];

			$agent = request()->userAgent();
			
			if ($agent) 
			{
				$payload["agent"] = substr($agent, 0, 200);
			}

			Logger::db("orbis", "info", "Passwort-Änderung '{$this->orbisUsername}' erfolgreich", $payload);

			$this->orbisSuccess = 'Änderungen erfolgreich gespeichert.';
			$this->orbisPassword = '';

			$this->searchOrbisUser();

		} 
		catch (\Throwable $e) 
		{

			Logger::db("orbis", "error", "Passwort-Änderung '{$this->orbisUsername}' fehlgeschlagen", [
				"user_id" => $this->orbisUser['id'] ?? null,
				"actor"   => auth()->user()?->username ?? null,
				"ip"      => request()->ip(),
				"agent"   => request()->userAgent() ?: null,
				"error"   => $e->getMessage(),
			]);

			$this->orbisError = "Exception: {$e->getMessage()}";
		}
    }

    public function render()
    {
        return view('livewire.pages.admin.ad-users.pw-reset-orbis');
    }
}

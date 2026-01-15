<?php

namespace App\Services\ActiveDirectory;

use LdapRecord\Models\ActiveDirectory\User as LdapUser;
use LdapRecord\Exceptions\LdapRecordException;
use LdapRecord\Exceptions\ConstraintException;
use LdapRecord\Exceptions\InsufficientAccessException;

class AdUserService
{
	public function isReachable(): bool
	{
		try {
			// einfacher Bind-Test
			LdapUser::query()->first();
			return true;
		} catch (\Throwable $e) {
			return false;
		}
	}

    public function findByGuid(string $guid): ?LdapUser
    {
        return LdapUser::findByGuid($guid);
    }

    public function resetPassword(LdapUser $user, string $newPassword): true|string
    {
        try {
            $user->unicodepwd = $newPassword;
            $user->save();
            return true;
        }
        catch (InsufficientAccessException) {
            return "Bind-User hat keine Rechte fuer Passwort Reset";
        }
        catch (ConstraintException) {
            return "Passwort verletzt Domain-Policy";
        }
        catch (LdapRecordException $e) {
            return "LDAP Fehler: ".$e->getDetailedError()->getDiagnosticMessage();
        }
        catch (\Exception $e) {
            return $e->getMessage();
        }
    }

    public function unlock(LdapUser $user): true|string
    {
        try {
            $user->update(['lockouttime' => 0]);
            return true;
        }
        catch (\Exception $e) {
            return $e->getMessage();
        }
    }

    public function enable(LdapUser $user): true|string
    {
        try {
            // 512 = "Normal account" laut Microsoft UAC Tabelle
            $user->userAccountControl = 512;
            $user->save();
            return true;
        }
        catch (\Exception $e) {
            return $e->getMessage();
        }
    }

    public function disable(LdapUser $user): true|string
    {
        try {
            // 514 = 512 + Disabled (2)
            $user->userAccountControl = 514;
            $user->save();
            return true;
        }
        catch (\Exception $e) {
            return $e->getMessage();
        }
    }

    public function forceChangeOnNextLogin(LdapUser $user): true|string
    {
        try {
            $user->update(['pwdlastset' => 0]);
            return true;
        }
        catch (\Exception $e) {
            return $e->getMessage();
        }
    }

	public function clearForceChange(LdapUser $user): true|string
	{
		try {
			$user->update(['pwdlastset' => -1]);
			return true;
		}
		catch (\Exception $e) {
			return $e->getMessage();
		}
	}

}

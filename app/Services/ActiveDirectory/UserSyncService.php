<?php

namespace App\Services\ActiveDirectory;

use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Log;
use LdapRecord\Models\ActiveDirectory\User as LdapUser;
use App\Utils\Logging\Logger;
use App\Models\AdUser;
use App\Enums\AdUserEmployeeType;

class UserSyncService
{
    public function sync(): void
    {
        $seenSids = [];

        $ldapUsers = LdapUser::query()
			->in(config("ums.ldap.ad_users_to_sync"))
            ->select([
                "objectsid",
                "objectguid",
                "samaccountname",
                "givenname",
                "sn",
                "displayname",
                "mail",
                "useraccountcontrol",
                "whencreated",
                "whenchanged",
                "lastlogon",
                "badpasswordtime",
                "pwdlastset",
                "accountexpires",
                "logoncount",
                "distinguishedname",
                "userprincipalname",
                "company",
                "department",
                "description",
                "division",
                "facsimiletelephonenumber",
                "homedirectory",
                "wWWHomePage",
                "homephone",
                "initials",
                "physicaldeliveryofficename",
                "telephonenumber",
                "postalcode",
                "profilepath",
                "st",
                "streetaddress",
                "title",
                "l",
                "c",
                "proxyaddresses",
                "memberof",
                "manager",
				"thumbnailphoto",
                "extensionattribute1",
                "extensionattribute2",
                "extensionattribute3",
                "extensionattribute4",
                "extensionattribute5",
                "extensionattribute6",
                "extensionattribute7",
                "extensionattribute8",
                "extensionattribute9",
                "extensionattribute10",
                "extensionattribute11",
                "extensionattribute12",
                "extensionattribute13",
                "extensionattribute14",
                "extensionattribute15",
            ])
            ->paginate(500);

        foreach ($ldapUsers as $ldapUser) 
		{
            $sid = $ldapUser->getConvertedSid();
            $guid = $ldapUser->getConvertedGuid();
            $username = $ldapUser->samaccountname[0] ?? null;

            if (!$sid || !$username) 
			{
                continue;
            }

            $seenSids[] = $sid;

            $data = [
				"employee_type"  => $this->deriveEmployeeType($ldapUser->initials[0] ?? null),
				"sid"                   => $sid,
                "guid"                  => $guid,
                "username"              => $username,
                "firstname"             => $ldapUser->givenname[0] ?? null,
                "lastname"              => $ldapUser->sn[0] ?? null,
                "display_name"          => $ldapUser->displayname[0] ?? null,
                "email"                 => $ldapUser->mail[0] ?? null,

                "is_enabled"            => $this->isEnabled($ldapUser),
                "is_existing"           => true,
                "password_never_expires"=> $this->isPasswordNeverExpires($ldapUser),

                "account_expiration_date"   => $this->toCarbon($ldapUser->accountexpires),
                "created"                   => $this->toCarbon($ldapUser->whencreated),
                "modified"                  => $this->toCarbon($ldapUser->whenchanged),
                "last_bad_password_attempt" => $this->toCarbon($ldapUser->badpasswordtime),
                "last_logon_date"           => $this->toCarbon($ldapUser->lastlogon),
                "password_last_set"         => $this->toCarbon($ldapUser->pwdlastset),

                "logon_count"           => $ldapUser->logoncount[0] ?? null,

                "city"                  => $ldapUser->l[0] ?? null,
                "company"               => $ldapUser->company[0] ?? null,
                "country"               => $ldapUser->c[0] ?? null,
                "department"            => $ldapUser->department[0] ?? null,
                "description"           => $ldapUser->description[0] ?? null,
                "division"              => $ldapUser->division[0] ?? null,
                "fax"                   => $ldapUser->facsimiletelephonenumber[0] ?? null,
                "home_directory"        => $ldapUser->homedirectory[0] ?? null,
                "home_page"             => $ldapUser->wWWHomePage[0] ?? null,
                "home_phone"            => $ldapUser->homephone[0] ?? null,
                "initials"              => $ldapUser->initials[0] ?? null,
                "office"                => $ldapUser->physicaldeliveryofficename[0] ?? null,
                "office_phone"          => $ldapUser->telephonenumber[0] ?? null,
                "postal_code"           => $ldapUser->postalcode[0] ?? null,
                "profile_path"          => $ldapUser->profilepath[0] ?? null,
                "state"                 => $ldapUser->st[0] ?? null,
                "street_address"        => $ldapUser->streetaddress[0] ?? null,
                "title"                 => $ldapUser->title[0] ?? null,
                "manager_dn"            => $ldapUser->manager[0] ?? null,
				"profile_photo_base64" => $this->getBase64Image($ldapUser),

                "distinguished_name"    => $ldapUser->distinguishedname[0] ?? null,
                "user_principal_name"   => $ldapUser->userprincipalname[0] ?? null,

                "proxy_addresses"       => $ldapUser->proxyaddresses ? array_values($ldapUser->proxyaddresses) : [],
				"member_of" => $ldapUser->memberof ? array_map(fn($dn) => preg_replace("/^CN=([^,]+).*/i", "$1", $dn), $ldapUser->memberof) : [],

                "extensionattribute1"   => $ldapUser->extensionattribute1[0] ?? null,
                "extensionattribute2"   => $ldapUser->extensionattribute2[0] ?? null,
                "extensionattribute3"   => $ldapUser->extensionattribute3[0] ?? null,
                "extensionattribute4"   => $ldapUser->extensionattribute4[0] ?? null,
                "extensionattribute5"   => $ldapUser->extensionattribute5[0] ?? null,
                "extensionattribute6"   => $ldapUser->extensionattribute6[0] ?? null,
                "extensionattribute7"   => $ldapUser->extensionattribute7[0] ?? null,
                "extensionattribute8"   => $ldapUser->extensionattribute8[0] ?? null,
                "extensionattribute9"   => $ldapUser->extensionattribute9[0] ?? null,
                "extensionattribute10"  => $ldapUser->extensionattribute10[0] ?? null,
                "extensionattribute11"  => $ldapUser->extensionattribute11[0] ?? null,
                "extensionattribute12"  => $ldapUser->extensionattribute12[0] ?? null,
                "extensionattribute13"  => $ldapUser->extensionattribute13[0] ?? null,
                "extensionattribute14"  => $ldapUser->extensionattribute14[0] ?? null,
                "extensionattribute15"  => $ldapUser->extensionattribute15[0] ?? null,

                "last_synced_at"        => Carbon::now(),
            ];

            AdUser::updateOrCreate(
                ["sid" => $sid],
                $data
            );
        }

		AdUser::whereNotIn("sid", $seenSids)
			->where("is_existing", true)
			->update([
				"is_existing"    => false,
				"last_synced_at" => Carbon::now(),
			]);


		Logger::Debug("Synchronisation Actice Directory abgeschlossen", [
            "found"   => count($seenSids),
            "missing" => AdUser::where("is_existing", false)->count(),
		]);
    }

	protected function toCarbon($value): ?Carbon
	{
		if (!$value) return null;
		if ($value instanceof Carbon) return $value;

		// Unix Timestamp
		if (is_numeric($value)) 
		{
			// Sonderwerte für "nie"
			if ($value == 0 || $value == 9223372036854775807) 
			{
				return null;
			}

			$unixTime = ($value / 10000000) - 11644473600;
			
			if ($unixTime > 0) 
			{
				// return Carbon::createFromTimestampUTC($unixTime);
				return Carbon::createFromTimestampUTC($unixTime)->toDateTimeString();
			}
			return null;
		}

		try 
		{
			return Carbon::parse($value);
		} 
		catch (\Exception $e) 
		{
			return null;
		}
	}

    protected function isEnabled(LdapUser $ldapUser): bool
    {
        $uac = $ldapUser->useraccountcontrol[0] ?? null;
        if ($uac === null) return true;
        return !(($uac & 0x2) === 0x2); // 0x2 = disabled
    }

    protected function isPasswordNeverExpires(LdapUser $ldapUser): bool
    {
        $uac = $ldapUser->useraccountcontrol[0] ?? null;
        if ($uac === null) return false;
        return (($uac & 0x10000) === 0x10000);
    }

	protected function getBase64Image(LdapUser $ldapUser): ?string
	{
		$photo = $ldapUser->thumbnailphoto[0] ?? null;

		if (!$photo) {
			return null;
		}

		// thumbnailPhoto ist binär/string
		return base64_encode($photo);
	}

	private function deriveEmployeeType(?string $initials): AdUserEmployeeType
	{
		$initials = trim((string) $initials);

		return match (true) 
		{
			$initials === '00000'                => AdUserEmployeeType::External,
			$initials === '11111'                => AdUserEmployeeType::Test,
			$initials === '99999'                => AdUserEmployeeType::InternalPending,
			preg_match('/^[67][0-9]{4}$/', $initials) === 1
												=> AdUserEmployeeType::Internal,
			default                              => AdUserEmployeeType::Unknown,
		};
	}


}

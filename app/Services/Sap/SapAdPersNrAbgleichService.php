<?php

namespace App\Services\Sap;

use App\Models\AdUser;
use App\Models\SapExport;
use App\Utils\Logging\Logger;
use LdapRecord\Models\ActiveDirectory\User as LdapUser;
use App\Models\EmployeeLifecycle;
use App\Enums\EmployeeLifecycleEvent;

class SapAdPersNrAbgleichService
{
    public function syncMissingInitials(): void
    {
        Logger::debug("SapAdPersNrAbgleichService: Starte Abgleich fehlender Personalnummern");

        $adUsers = AdUser::where('initials', '99999')->get();

        if ($adUsers->isEmpty()) 
		{
            Logger::debug("SapAdPersNrAbgleichService: Keine Benutzer mit fehlender Personalnummer gefunden");
            return;
        }

        $sapRows = SapExport::all();

        foreach ($adUsers as $adUser) 
		{
            $ldapUser = LdapUser::where('samaccountname', '=', $adUser->username)->first();

            if (!$ldapUser) 
			{				
                continue;
            }

            // Attribute aus LDAP
            $vornameAD  = trim($ldapUser->getFirstAttribute('givenName'));
            $nachnameAD = trim($ldapUser->getFirstAttribute('sn'));
            $descAD     = trim($ldapUser->getFirstAttribute('description'));
            $username   = $ldapUser->getFirstAttribute('sAMAccountName');

            foreach ($sapRows as $row) 
			{
                $vornameSAP  = trim($row->d_rufnm ?: $row->d_vname);
                $nachnameSAP = trim($row->d_name);
                $batchbezSAP = trim($row->d_0032_batchbez);

                if ($vornameAD  !== $vornameSAP || $nachnameAD !== $nachnameSAP || $descAD     !== $batchbezSAP) 
				{
                    continue;
                }

                // Personalnummer aus SAP
                $pn = ltrim(trim($row->d_pernr), '0');
				
                if ($pn === '') 
				{
                    continue;
                }

                try 
				{
                    // Im Active Directory speichern
                    $ldapUser->setFirstAttribute('initials', $pn);
                    $ldapUser->save();

                    // In der lokalen Datenbank speichern
                    $adUser->update([
                        'initials'    => $pn,
                    ]);

                    Logger::db("sap", "info", "Personalnummer erfolgreich gesetzt", [
                        "username"      => $username,
                        "alte_initials" => "99999",
                        "neue_initials" => $pn,
                        "matched_by"    => [
                            "vorname"      => $vornameSAP,
                            "nachname"     => $nachnameSAP,
                            "beschreibung" => $batchbezSAP,
                        ],
                    ]);

					$dbUser = AdUser::where('username', $username)->first();

					if ($dbUser) {
						EmployeeLifecycle::create([
							'ad_user_id'  => $dbUser->id,
							'event'       => EmployeeLifecycleEvent::AdUserChange->value,
							'description' => "Personalnummer wurde gesetzt ({$pn}).",
							'context'     => [
								"username"      => $username,
								"alte_initials" => "99999",
								"neue_initials" => $pn,
								"matched_by"    => [
									"vorname"      => $vornameSAP,
									"nachname"     => $nachnameSAP,
									"beschreibung" => $batchbezSAP,
								],
								"source" => "SapAdPersNrAbgleichService",
							],
							'event_at'    => now(),
						]);
					}

                    break;

                } 
				catch (\Exception $e) 
				{

                    Logger::db("sap", "error", "Fehler beim Setzen der Personalnummer", [
                        "username"      => $username,
                        "personalnummer"=> $pn,
                        "error"         => $e->getMessage(),
                    ]);
                }
            }
        }

        Logger::debug("SapAdPersNrAbgleichService: Abgleich abgeschlossen");
    }
}

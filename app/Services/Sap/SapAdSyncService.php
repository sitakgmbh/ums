<?php

namespace App\Services\Sap;

use LdapRecord\Models\ActiveDirectory\User as LdapUser;
use App\Utils\Logging\Logger;
use App\Utils\UserHelper;
use App\Models\Setting;
use App\Models\Mutation;
use App\Models\AdUser;
use App\Models\SapExport;
use App\Models\AdUser as LocalAdUser;
use App\Models\EmployeeLifecycle;
use App\Enums\EmployeeLifecycleEvent;

class SapAdSyncService
{
    protected array $stats = [];
    protected array $changes = [];

    protected array $attributeMap = [
        "title"					=> ["sap" => "d_0032_batchbez"],
        "description"			=> ["sap" => "d_0032_batchbez"],
        "department"			=> ["sap" => "d_abt_txt"],
        "extensionAttribute2"	=> ["sap" => "d_gbdat"],
        "extensionAttribute5"	=> ["sap" => "d_0032_batchbez"],
        "extensionAttribute6"	=> ["sap" => "d_pers_txt"],
        "extensionAttribute7"	=> ["sap" => "d_zzkader"],
        "extensionAttribute8"	=> ["sap" => "d_zzbereit"],
        "extensionAttribute9"	=> ["sap" => "d_einri"],
        "extensionAttribute11"	=> ["sap" => "d_einda"],
		"extensionAttribute12"	=> ["sap" => "d_endda"],
        "extensionAttribute13"	=> ["sap" => "d_titel"],
        "extensionAttribute15"	=> ["sap" => "d_abt_nr"],
    ];

    public function sync(): void
    {
        $this->stats = [
            "found" => 0,
            "not_found" => 0,
            "updated" => 0,
            "no_changes" => 0,
            "mutations_created" => 0,
        ];

		Logger::debug("SapAdSyncService: Start");

		$this->changes = [];
     
		$sapRows = SapExport::all();

		if ($sapRows->isEmpty()) {
			throw new \RuntimeException("SapExport Tabelle ist leer – bitte zuerst SapImportService::import() ausführen.");
		}

		Logger::debug("SapAdSyncService: AD-Benutzer abfragen");

		$adUsers = LdapUser::select([
			'samaccountname',
			'initials', 
			'givenname', 
			'sn', 
			'displayname', 
			'cn', 
			'userprincipalname', 
			'distinguishedname', 
			'manager',
			'title',
			'description',
			'department',
			'extensionattribute2',
			'extensionattribute5',
			'extensionattribute6',
			'extensionattribute7',
			'extensionattribute8',
			'extensionattribute9',
			'extensionattribute11',
			'extensionattribute12',
			'extensionattribute13',
			'extensionattribute15',
		])
		->in(config("ums.ldap.ad_users_to_sync"))
		->get();
		
		Logger::debug("SapAdSyncService: Erstelle AD-User Map");
		
		$adUsersMap = [];
		
		foreach ($adUsers as $user) 
		{
			$initials = $user->getFirstAttribute("initials");
			
			if ($initials) 
			{
				$adUsersMap[$initials] = $user;
			}
		}
		
		Logger::debug("SapAdSyncService: Iteriere durch SAP-Export");

		foreach ($sapRows as $row) 
		{
			$personalnummer = ltrim(trim($row["d_pernr"] ?? ""), "0");
			
			if (empty($personalnummer)) continue;

			$adUser = $adUsersMap[$personalnummer] ?? null;

			if (!$adUser) 
			{
				$this->stats["not_found"]++;
				continue;
			}

			$username = $adUser->getFirstAttribute("samaccountname");

			$this->stats["found"]++;
			
			$this->syncNames($adUser, $row, $username, $personalnummer);
			$this->syncSimpleAttributes($adUser, $row, $username, $personalnummer);
			$this->syncManager($adUser, $row, $username, $personalnummer, $adUsersMap);

			if (!empty($this->changes)) 
			{
				$context = [
					"personalnummer" => $personalnummer,
					"username"       => $username,
					"changes"        => $this->changes,
					"source"         => "SapAdSyncService",
				];

				Logger::db("sap", "info", "Benutzer '{$username}' aktualisiert", $context);

				$dbUser = LocalAdUser::where('username', $username)->first();

				if ($dbUser) {
					EmployeeLifecycle::create([
						'ad_user_id'  => $dbUser->id,
						'event'       => EmployeeLifecycleEvent::AdUserChange->value,
						'description' => "Der AD-Benutzer '{$username}' wurde aktualisiert.",
						'context'     => $context,
						'event_at'    => now(),
					]);
				}

				$this->stats["updated"]++;
				$this->changes = [];
			}
			else 
			{
				$this->stats["no_changes"]++;
			}
		}
		
		Logger::debug("SapAdSyncService: Ende");
	}

	protected function syncNames($adUser, $row, $username, $personalnummer): void
	{
		$vornameSAP  = !empty($row["d_rufnm"]) ? trim($row["d_rufnm"]) : trim($row["d_vname"] ?? "");
		$nachnameSAP = trim($row["d_name"] ?? "");
		
		$vornameAD      = $adUser->getFirstAttribute("givenname");
		$nachnameAD     = $adUser->getFirstAttribute("sn");
		$displayNameAD  = $adUser->getFirstAttribute("displayname");
		$nameAD         = $adUser->getFirstAttribute("cn");
		$currentUpn     = $adUser->getFirstAttribute("userprincipalname");

		$newDisplayName = trim($nachnameSAP . " " . $vornameSAP);
		$newCN          = trim($nachnameSAP . " " . $vornameSAP . " / " . $username);

		$vornameChanged = false;
		$nachnameChanged = false;
		$changesDetected = false;

		if ($vornameAD !== $vornameSAP && !empty($vornameSAP)) 
		{
			$adUser->setFirstAttribute("givenname", $vornameSAP);

			$this->changes[] = [
				"attribute" => "givenname",
				"old"       => $vornameAD,
				"new"       => $vornameSAP,
			];

			$vornameChanged = true;
			$changesDetected = true;
		}

		if ($nachnameAD !== $nachnameSAP && !empty($nachnameSAP)) 
		{
			$adUser->setFirstAttribute("sn", $nachnameSAP);

			$this->changes[] = [
				"attribute" => "sn",
				"old"       => $nachnameAD,
				"new"       => $nachnameSAP,
			];

			$nachnameChanged = true;
			$changesDetected = true;
		}

		if ($displayNameAD !== $newDisplayName && !empty($newDisplayName)) 
		{
			$adUser->setFirstAttribute("displayname", $newDisplayName);

			$this->changes[] = [
				"attribute" => "displayname",
				"old"       => $displayNameAD,
				"new"       => $newDisplayName,
			];

			$changesDetected = true;
		}

		if ($nameAD !== $newCN && !empty($newCN)) 
		{
			try 
			{
				$parts = explode("@", $currentUpn);
				$upnDomain = $parts[1];

				$cleanFirstName = UserHelper::normalize($vornameSAP);
				$cleanLastName  = UserHelper::normalize($nachnameSAP);
				$newUpn         = strtolower("{$cleanFirstName}.{$cleanLastName}@{$upnDomain}");

				// Rename direkt, Attribute danach ändern
				$adUser->rename($newCN);
				$adUser->setFirstAttribute("userprincipalname", $newUpn);

				$this->changes[] = [
					"attribute" => "cn",
					"old"       => $nameAD,
					"new"       => $newCN,
				];

				$this->changes[] = [
					"attribute" => "userprincipalname",
					"old"       => $currentUpn,
					"new"       => $newUpn,
				];

				$changesDetected = true;

			} 
			catch (\Exception $e) 
			{
				Logger::db("sap", "error",
					"Fehler beim Umbenennen oder Aktualisieren des UPNs des Benutzers '{$username}'",
					["username" => $username, "error" => $e->getMessage()]
				);
			}
		}

		if ($changesDetected) 
		{
			try 
			{
				$adUser->save();
			} 
			catch (\Exception $e) 
			{
				Logger::db("sap", "error",
					"Fehler beim Aktualisieren des AD-Benutzers '{$username}'",
					["username" => $username, "error" => $e->getMessage()]
				);
			}
		}

		if ($vornameChanged || $nachnameChanged) 
		{
			$this->createMutation(
				$username,
				$vornameChanged ? $vornameSAP : null,
				$nachnameChanged ? $nachnameSAP : null
			);
		}
	}

    protected function syncSimpleAttributes($adUser, $row, $username, $personalnummer): void
    {
		$attributeChanges = 0;
		
		foreach ($this->attributeMap as $adAttr => $config) 
		{
			$sapField = $config["sap"];
			$sapValue = trim($row[$sapField] ?? "");

			// Ohne Austrittsdatum enthält der SAP-Export '00000000' und das wollen wir nicht. Wir nullen daher das Attribut im AD.
			if ($adAttr === "extensionAttribute12" && $sapValue === "00000000") $sapValue = null;

			$sapValue = $sapValue === "" ? null : $sapValue;
			$adValue = $adUser->getFirstAttribute($adAttr);

			if ($adValue !== $sapValue) 
			{
				try 
				{
					if (empty($sapValue)) 
					{
						$adUser->setFirstAttribute($adAttr, null);
					} 
					else 
					{
						$adUser->setFirstAttribute($adAttr, $sapValue);
					}
					
					$adUser->save();
					
					$this->changes[] = [
						"attribute" => $adAttr,
						"old" => $adValue,
						"new" => $sapValue ?: null,
					];
					
					$attributeChanges++;
                } 
				catch (\Exception $e) 
				{
					Logger::db("sap", "error", "Fehler bei beim Aktualisieren des Attributs '{$adAttr}' des Benutzers '{$username}'", [
                        "username" => $username,
                        "error" => $e->getMessage(),
                    ]);
                }
			}
        }
    }

	protected function syncManager($adUser, $row, $username, $personalnummer, $adUsersMap): void
	{
		$rawManager = trim($row["d_leader"] ?? "");
		$managerPersNr = ltrim($rawManager, "0");

		$currentManagerDn = $adUser->getFirstAttribute("manager");

		if ($managerPersNr === "") 
		{
			Logger::debug("SAP-AD-Sync: Benutzer '{$username}' hat keinen Manager (d_leader leer oder 000000)", [
				"username" => $username,
				"personalnummer" => $personalnummer,
				"sap_d_leader" => $row["d_leader"] ?? null,
			]);

			if (!empty($currentManagerDn)) 
			{
				try 
				{
					$adUser->setFirstAttribute("manager", null);
					$adUser->save();

					$this->changes[] = [
						"attribute" => "manager",
						"old" => $currentManagerDn,
						"new" => null,
					];
				} 
				catch (\Exception $e) 
				{
					Logger::db("sap", "error",
						"Fehler beim Nullen des Managers von '{$username}'",
						["username" => $username, "error" => $e->getMessage()]
					);
				}
			}

			return;
		}

		$managerUser = $adUsersMap[$managerPersNr] ?? null;
		
		if (!$managerUser) 
		{
			return;
		}

		$newManagerDn = $managerUser->getFirstAttribute("distinguishedname");

		if ($currentManagerDn !== $newManagerDn) 
		{
			try 
			{
				$adUser->setFirstAttribute("manager", $newManagerDn);
				$adUser->save();

				$this->changes[] = [
					"attribute" => "manager",
					"old" => $currentManagerDn,
					"new" => $newManagerDn,
				];
			} 
			catch (\Exception $e) 
			{
				Logger::db("sap", "error",
					"Fehler beim Aktualisieren des Managers von '{$username}'",
					["username" => $username, "error" => $e->getMessage()]
				);
			}
		}
	}

	protected function createMutation(string $username, ?string $vorname, ?string $nachname): void
	{
		try 
		{
			$adUser = AdUser::where("username", $username)->first();

			if (!$adUser) 
			{
				Logger::warning("AdUser '{$username}' nicht in Datenbank gefunden, Mutation konnte nicht erstellt werden");
				return;
			}

			$mailDomain = null;

			if ($adUser->email) 
			{
				$parts = explode("@", $adUser->email);
				$mailDomain = isset($parts[1]) ? "@" . $parts[1] : null;
			}

			$old = [
				"vorname_old"                => $adUser->firstname,
				"nachname_old"               => $adUser->lastname,
				"anrede_id_old"              => $adUser->anrede_id,
				"titel_id_old"               => $adUser->titel_id,
				"arbeitsort_id_old"          => $adUser->arbeitsort_id,
				"unternehmenseinheit_id_old" => $adUser->unternehmenseinheit_id,
				"abteilung_id_old"           => $adUser->abteilung_id,
				"funktion_id_old"            => $adUser->funktion_id,
			];

			$data = array_merge([
				"vertragsbeginn" => today(),
				"ad_user_id"     => $adUser->id,
				"mailendung"     => $mailDomain,
				"kommentar"      => "Dieser Antrag wurde automatisch erstellt.",
				"status_mail"    => 1,
				"status_kis"     => 1,
			], $old);

			if ($vorname !== null) 
			{
				$data["vorname"] = $vorname;
			}

			if ($nachname !== null) 
			{
				$data["nachname"] = $nachname;
			}

			Mutation::create($data);

			$this->stats["mutations_created"]++;

		} 
		catch (\Exception $e) 
		{
			Logger::db("sap", "error", "Fehler beim Erstellen der Mutation von '{$username}'", [
				"username" => $username,
				"error" => $e->getMessage(),
			]);
		}
	}
}
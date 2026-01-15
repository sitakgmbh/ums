<?php

namespace App\Services\MyPdgr;

use App\Utils\Logging\Logger;
use Illuminate\Support\Facades\DB;
use LdapRecord\Models\ActiveDirectory\User as LdapUser;

class MyPdgrAdSyncService
{
    protected string $actor;
    protected array $stats;
    protected array $changes;
    protected int $minExpectedRows = 1000;

	protected array $attributeMap = [
		"physicaldeliveryofficename" => "per_adresszusatz", // Office
		"streetaddress" => "per_adresse", // StreetAddress  
		"postalcode" => "per_plz",// PostalCode
		"l" => "per_ort", // City (l = locality)
	];

    public function __construct()
    {
        $this->actor = auth()->user()->username ?? "cli";
        $this->stats = [
            "found" => 0,
            "not_found" => 0,
            "disabled" => 0,
            "updated" => 0,
            "no_changes" => 0,
        ];
    }

    public function sync(): void
    {
		Logger::debug("MyPdgrAdSyncService: Start");

        $MyPdgrUsers = $this->getMyPdgrEntries();
		
        if (!$MyPdgrUsers || count($MyPdgrUsers) === 0) 
		{
			throw new \RuntimeException("Unerwarteter Fehler beim Abfragen der Daten aus MyPdgr");
		}
		
		Logger::debug("MyPdgrAdSyncService: MyPdgr-Einträge geladen: " . count($MyPdgrUsers));
		
		$myPdgrMap = [];
		
		foreach ($MyPdgrUsers as $entry) 
		{
			$pdgrNummer = $entry['per_pdgrnummer'] ?? null;
			
			if ($pdgrNummer) 
			{
				$myPdgrMap[$pdgrNummer] = $entry;
			}
		}
		
		Logger::debug("MyPdgrAdSyncService: MyPdgr-Map erstellt mit " . count($myPdgrMap) . " Einträgen");
		Logger::debug("MyPdgrAdSyncService: AD-Benutzer abfragen");

        $adUsers = LdapUser::select([
			'samaccountname',
			'displayname',
			'initials',
			'useraccountcontrol',
			'physicaldeliveryofficename',
			'streetaddress',
			'postalcode',
			'l',
		])
		->in(config("ums.ldap.ad_users_to_sync"))
		->get();

		$adUsers = $adUsers->filter(function ($user) {
			$initials = $user->getFirstAttribute("initials");
			return $initials !== "00000" && $initials !== "11111" && $initials !== "99999";
		});

		Logger::debug("MyPdgrAdSyncService: AD-Benutzer geladen: " . $adUsers->count());
		Logger::debug("MyPdgrAdSyncService: Iteriere durch AD-Benutzer");

        foreach ($adUsers as $adUser) 
		{
			$this->changes = [];

            $username = $adUser->getFirstAttribute("samaccountname");
            $displayName = $adUser->getFirstAttribute("displayname");
            $initials = $adUser->getFirstAttribute("initials");
            $userAccountControl = $adUser->getFirstAttribute("useraccountcontrol");

            if (empty($initials)) 
			{
                $this->stats["not_found"]++;
                continue;
            }

            $MyPdgrEntry = $myPdgrMap[$initials] ?? null;

            if (!$MyPdgrEntry) 
			{
                // Logger::warning("Kein MyPdgr-Eintrag für {$displayName} ({$username}) mit Personalnummer {$initials} gefunden");
                $this->stats["not_found"]++;
                continue;
            }

            // Prüfen ob Benutzer deaktiviert ist (userAccountControl & 2 = deaktiviert)
            if ($userAccountControl && ($userAccountControl & 2)) 
			{
                $this->stats["disabled"]++;
                continue;
            }

            $this->stats["found"]++;
            $this->syncAddressAttributes($adUser, $MyPdgrEntry, $username, $initials);

            if (!empty($this->changes)) 
			{
                Logger::db("mypdgr", "info", "Benutzer '{$username}' aktualisiert", [
                    "personalnummer" => $initials,
                    "username" => $username,
                    "displayname" => $displayName,
                    "changes" => $this->changes,
                    "actor" => $this->actor,
                ]);

                $this->stats["updated"]++;
            } 
			else 
			{
                $this->stats["no_changes"]++;
            }
        }

		Logger::debug("MyPdgrAdSyncService: Ende", $this->stats);
    }

    protected function getMyPdgrEntries(): array
    {
        try 
		{
            $host = env("MyPdgr_DB_HOST");
            $port = env("MyPdgr_DB_PORT", 3306);
            $database = env("MyPdgr_DB_DATABASE");
            $username = env("MyPdgr_DB_USERNAME");
            $password = env("MyPdgr_DB_PASSWORD");
            $table = env("MyPdgr_DB_TABLE", "avs_personen");

            if (!$host || !$database || !$username || !$password) 
			{
				throw new \RuntimeException("MyPdgr Datenbank-Konfiguration in .env nicht vollständig");
			}

            config([
                "database.connections.MyPdgr_temp" => [
                    "driver" => "mysql",
                    "host" => $host,
                    "port" => $port,
                    "database" => $database,
                    "username" => $username,
                    "password" => $password,
                    "charset" => "utf8mb4",
                    "collation" => "utf8mb4_unicode_ci",
                    "prefix" => "",
                    "strict" => false,
                    "engine" => null,
                ]
            ]);

            $tableExists = DB::connection("MyPdgr_temp")
                ->select("SELECT COUNT(*) as count FROM information_schema.tables WHERE table_schema = ? AND table_name = ?", [$database, $table]);

            if ($tableExists[0]->count == 0) 
			{
                $msg = "Die Tabelle '{$table}' existiert nicht in der MyPdgr-Datenbank '{$database}'.";
                Logger::db("MyPdgr", "error", $msg, ["actor" => $this->actor]);
                throw new \RuntimeException($msg);
            }

            $rowCount = DB::connection("MyPdgr_temp")
                ->table($table)
                ->count();

            if ($rowCount < $this->minExpectedRows) 
			{
                $msg = "Die Tabelle '{$table}' enthält nur {$rowCount} Einträge (erwartet: mindestens {$this->minExpectedRows}).";
                throw new \RuntimeException($msg);
            }

            $results = DB::connection("MyPdgr_temp")
                ->table($table)
				->select([
					'per_pdgrnummer',
					'per_adresszusatz',
					'per_adresse',
					'per_plz',
					'per_ort'
				])
                ->get()
                ->map(function ($row) {
                    return (array) $row;
                })
                ->toArray();

            return $results;
        } 
		catch (\Exception $e) 
		{
            Logger::db("MyPdgr", "error", "Fehler beim Abrufen der MyPdgr-Daten", [
                "error" => $e->getMessage(),
                "actor" => $this->actor,
            ]);
			
            throw $e;
        }
    }

    protected function syncAddressAttributes($adUser, array $MyPdgrEntry, string $username, string $personalnummer): void
    {
        foreach ($this->attributeMap as $adAttr => $MyPdgrField) 
		{
            $MyPdgrValue = trim($MyPdgrEntry[$MyPdgrField] ?? "");
            $adValue = $adUser->getFirstAttribute(strtolower($adAttr));

            if (!empty($MyPdgrValue) && $adValue !== $MyPdgrValue) 
			{
                try 
				{
                    $adUser->setFirstAttribute(strtolower($adAttr), $MyPdgrValue);
                    $adUser->save();

                    $this->changes[] = [
                        "attribute" => $adAttr,
                        "old" => $adValue,
                        "new" => $MyPdgrValue,
                    ];
                } 
				catch (\Exception $e) 
				{
                    Logger::db("mypdgr", "error", "Fehler beim Aktualisieren des Attributs '{$adAttr}' des Benutzers '{$username}'", [
                        "username" => $username,
                        "personalnummer" => $personalnummer,
                        "error" => $e->getMessage(),
                        "actor" => $this->actor,
                    ]);
                }
            }
        }
    }
}
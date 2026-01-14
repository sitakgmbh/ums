<?php

namespace App\Services\Orbis;

use App\Utils\Logging\Logger;

class OrbisHelper
{
    public function __construct(
        private OrbisClient $client
    ) {}

    public function employeeExists(string $shortname): bool
    {
        $url = $this->client->getBaseUrl() . "/resources/external/employees?shortname=" . urlencode($shortname) . "&maxresults=1";
        $result = $this->client->send($url, "GET");

        return isset($result["employee"]) && count($result["employee"]) > 0;
    }
	
    public function userExists(string $username): bool
    {
        $url = $this->client->getBaseUrl() . "/resources/external/users?name=" . urlencode($username) . "&maxresults=1";
        $result = $this->client->send($url, "GET");

        return isset($result["user"]) && count($result["user"]) > 0;
    }

	public function getUserDetails(string $username): array
	{
		$username = strtoupper($username);
		$today = date("Y-m-d");
		$user = $this->getUserByUsername($username);
		
		if (!$user || !isset($user["id"])) 
		{
			throw new \RuntimeException("Benutzer nicht gefunden.", 404);
		}

		$userId = $user["id"];
		$employee = $this->getEmployeeByUserId($userId, $today);
		
		if (!$employee || !isset($employee["id"])) 
		{
			throw new \RuntimeException("Mitarbeiter nicht gefunden.");
		}

		$employeeId = $employee["id"];
		$employeeData = $this->getEmployeeDetails($employee, $today);
		$orgUnits  = $this->getEmployeeOrganizationalUnits($employeeId, $today);
		$orgGroups = $this->getEmployeeOrganizationalUnitGroups($employeeId, $today);
		$users     = $this->getUsersByEmployeeId($employeeId, $today);
		$facility = $employeeData["facilities"][0] ?? null;
		$functionAssignment = $this->getEmployeeFunctionAssignment($employeeData["id"], $today);

		return [
			"user" => $user,
			"employee" => [
				"id" => $employeeData["id"],
				"salutation" => $employeeData["salutation"],
				"title" => $employeeData["title"] ?? null,
				"surname" => $employeeData["surname"],
				"firstname" => $employeeData["firstname"],
				"sex" => $employeeData["sex"],
				"facility" => $facility,
				"state" => $employeeData["state"],
				"signinglevel" => $employeeData["signinglevel"],
				"validfrom" => $employeeData["validfrom"],
				"validthru" => $employeeData["validthru"],
				"organizationalunits" => $orgUnits,
				"organizationalunitgroups" => $orgGroups,
				"users" => $users,
				"employeefunction" => [
					"id" => $functionAssignment["employeefunction_id"] ?? null,
					"assignment_id" => $functionAssignment["assignment_id"] ?? null
				]
			]
		];

	}

    public function getUserByUsername(string $username): ?array
    {
        $url = $this->client->getBaseUrl() . "/resources/external/users?name=" . urlencode($username);
        $users = $this->client->send($url)["user"] ?? [];
        $today = date("Y-m-d");

        foreach ($users as $user) {
            $from = $user["validityperiod"]["from"]["date"] ?? null;
            $thru = $user["validityperiod"]["to"]["date"] ?? null;

            if ((!$from || $from <= $today) && (!$thru || $thru >= $today)) {
                return $user;
            }
        }

        return null;
    }

    public function getEmployeeByUserId($userId, string $today)
    {
        $url = $this->client->getBaseUrl() . "/resources/external/users/{$userId}/employees?referencedate={$today}&includecatalogtranslations=true";
        return $this->client->send($url)["employee"][0] ?? null;
    }

    public function getEmployeeDetails(array $employee, string $today): array
    {
        $id = $employee["id"];
        $human = $employee["humanbeing"] ?? [];

        return [
            "id" => $id,
            "firstname" => $human["firstname"] ?? null,
            "surname" => $human["surname"] ?? null,
            "sex" => $this->getCatalogTranslation("SEX", $human["sex"]["catalogcoding"]["code"] ?? ""),
            "salutation" => $this->getCatalogTranslation("SALUTATIONS", $human["salutation"]["catalogcoding"]["code"] ?? ""),
            "title" => $this->getCatalogTranslation("TITLES", $human["title"]["catalogcoding"]["code"] ?? ""),
            "state" => $this->getCatalogTranslation("STATEOFEMPLOYEE", $employee["state"]["catalogcoding"]["code"] ?? ""),
            "signinglevel" => $employee["signinglevel"] ?? null,
            "validfrom" => $employee["validityperiod"]["from"]["date"] ?? null,
            "validthru" => $employee["validityperiod"]["to"]["date"] ?? null,
            "facilities" => $this->getEmployeeFacilities($id, $today)
        ];
    }

	public function getEmployeeFunctionAssignment(int $employeeId, string $today): ?array
	{
		$url = $this->client->getBaseUrl()
			. "/resources/external/employees/{$employeeId}/employeefunctionassignments?referencedate={$today}";

		$response = $this->client->send($url);

		$list = $response['employeeemployeefunctionassignment'] ?? [];

		if (empty($list)) 
		{
			return null;
		}

		$entry = $list[0];

		return [
			'assignment_id'       => $entry['id'] ?? null,
			'employeefunction_id' => $entry['employeefunction']['id'] ?? null
		];
	}

    public function getEmployeeFacilities(int $employeeId, string $today): array
    {
        $result = [];
        $url = $this->client->getBaseUrl() . "/resources/external/employees/{$employeeId}/facilityassignments?referencedate={$today}";
        $response = $this->client->send($url);

        foreach ($response["employeefacilityassignment"] ?? [] as $entry) 
		{
            $fid = $entry["facility"]["id"] ?? null;

            if ($fid) 
			{
                $details = $this->client->send($this->client->getBaseUrl() . "/resources/external/facilities/{$fid}");
                $result[] = [
                    "id" => $fid,
                    "name" => $details["name"] ?? null,
                    "shortname" => $details["shortname"] ?? null
                ];
            }
        }

        return $result;
    }

    public function getEmployeeOrganizationalUnits(int $employeeId, string $today): array
    {
        $result = [];
        $url = $this->client->getBaseUrl() . "/resources/external/employees/{$employeeId}/organizationalunitassignments?referencedate={$today}";
        $response = $this->client->send($url);

        foreach ($response["employeeorganizationalunitassignment"] ?? [] as $a) 
		{
            $unitId = $a["organizationalunit"]["id"] ?? null;

            if ($unitId) 
			{
                $detail = $this->client->send(
                    $this->client->getBaseUrl() . "/resources/external/organizationalunits/{$unitId}"
                );

                $result[] = [
                    "id" => $unitId,
                    "name" => $detail["name"] ?? null,
                    "shortname" => $detail["shortname"] ?? null,
                    "type" => $detail["type"]["catalogcoding"]["code"] ?? null,
                    "rank" => $this->getRank($a["rank"] ?? null)
                ];
            }
        }

        return $result;
    }

    public function getEmployeeOrganizationalUnitGroups(int $employeeId, string $today): array
    {
        $result = [];
        $url = $this->client->getBaseUrl() . "/resources/external/employees/{$employeeId}/organizationalunitgroupassignments?referencedate={$today}";
        $response = $this->client->send($url);

        foreach ($response["employeeorganizationalunitgroupassignment"] ?? [] as $a) 
		{
            $id = $a["organizationalunitgroup"]["id"] ?? null;
            if (!$id) continue;

            $detail = $this->client->send(
                $this->client->getBaseUrl() . "/resources/external/organizationalunitgroups/{$id}"
            );

            $name = $detail["name"] ?? null;
            $shortname = $detail["shortname"] ?? null;
            $type = $detail["organizationalunitgrouptypeassignments"]["organizationalunitgrouptypeassignment"][0]["type"]["catalogcoding"]["code"] ?? null;

            if ($name && $shortname && $type) 
			{
                $result[] = [
                    "id" => $id,
                    "name" => $name,
                    "shortname" => $shortname,
                    "type" => $type
                ];
            }
        }

        return $result;
    }

    public function getUsersByEmployeeId(int $employeeId, string $today): array
    {
        $result = [];
        $url = $this->client->getBaseUrl() . "/resources/external/employees/{$employeeId}/users?referencedate={$today}";
        $users = $this->client->send($url)["user"] ?? [];

        foreach ($users as $user) {
            $result[] = [
                "id" => $user["id"] ?? null,
                "username" => $user["name"] ?? null,
                "description" => $user["description"] ?? null,
                "validfrom" => $user["validityperiod"]["from"]["date"] ?? null,
                "validthru" => $user["validityperiod"]["to"]["date"] ?? null,
                "locked" => $user["locked"] ?? null,
                "mustchangepassword" => $user["mustchangepassword"] ?? null,
                "passwordrefreshinterval" => $user["passwordrefreshinterval"] ?? null,
                "roles" => $this->getUserRoles($user["id"], $today)
            ];
        }

        return $result;
    }

    public function getUserRoles(int $userId, string $today): array
    {
        $url = $this->client->getBaseUrl() . "/resources/external/users/{$userId}/roleassignments?referencedate={$today}";
        $result = [];

        foreach ($this->client->send($url)["userroleassignment"] ?? [] as $a) {
            $rid = $a["role"]["id"] ?? null;
            if (!$rid) continue;

            $details = $this->client->send(
                $this->client->getBaseUrl() . "/resources/external/roles/{$rid}"
            );

            $result[] = [
                "id" => $rid,
                "name" => $details["name"] ?? "Unbekannt"
            ];
        }

        return $result;
    }

    public function getRank(?array $rank): ?array
    {
        if (!isset($rank["id"])) 
		{
            return null;
        }

        $details = $this->client->send(
            $this->client->getBaseUrl() . "/resources/external/catalogs/{$rank["id"]}"
        );

        return [
            "id" => $rank["id"],
            "code" => $details["catalogcoding"]["code"] ?? null
        ];
    }

	public function getCatalogNameById(int $id): ?string
	{
		$url = $this->client->getBaseUrl() . "/resources/external/catalogs/{$id}";
		$data = $this->client->send($url);

		if (!is_array($data)) 
		{
			return null;
		}

		return $data["name"] ?? ($data["catalogcoding"]["code"] ?? null) ?? null;
	}

    public function getCatalogTranslation(string $codesystem, string $code): array
    {
        if (!$code) 
		{
            return [];
        }

        $url = $this->client->getBaseUrl() . "/resources/external/catalogs?codesystem={$codesystem}&code=" . urlencode($code) . "&includecatalogtranslations=true";
        $data = $this->client->send($url);

        $result = [
            "id" => $data["id"] ?? null,
            "code" => $code,
            "id_language" => null,
            "shortname" => null,
            "longname" => null
        ];

        foreach ($data["catalogtranslations"]["catalogtranslation"] ?? [] as $trans) 
		{
            if (in_array($trans["languageoftranslation"]["id"] ?? "", ["de", "de_CH"])) 
			{
                $result["shortname"] = $trans["shortname"] ?? null;
                $result["longname"] = $trans["longname"] ?? null;
                $result["id_language"] = $trans["languageoftranslation"]["id"] ?? null;
                break;
            }
        }

        return $result;
    }

	public function createEmployee(array $payload): ?int
	{
		$result = $this->client->send(
			$this->client->getBaseUrl() . "/resources/external/employees",
			"POST",
			$payload,
			returnHeaders: true
		);

		$headers = $result['headers'] ?? [];

		if (!isset($headers['Location'][0])) 
		{
			Logger::error("Keine Location im Header erhalten");
			return null;
		}

		$location = $headers['Location'][0];   // z.B. /employees/45243

		// ID extrahieren
		$id = intval(basename($location));

		Logger::debug("Neue ORBIS Employee-ID: {$id}");

		return $id > 0 ? $id : null;
	}

	public function createUser(int $employeeId, array $payload): ?int
	{
		$result = $this->client->send(
			$this->client->getBaseUrl() . "/resources/external/employees/{$employeeId}/users",
			"POST",
			$payload,
			returnHeaders: true
		);

		$headers = $result['headers'] ?? [];

		if (!isset($headers['Location'][0])) 
		{
			Logger::error("Keine Location für User erhalten");
			return null;
		}

		$location = $headers['Location'][0];   // .../users/36412
		$id = intval(basename($location));     // 36412

		Logger::debug("Neue ORBIS User-ID: {$id}");

		return $id > 0 ? $id : null;
	}

    public function findAvailableUsername(string $base): array
    {
        $log = [];
        $prefix = $base;
        $counter = 0;

        if (preg_match('/^(.*?)(\d+)$/', $base, $matches)) 
		{
            $prefix = $matches[1];
            $counter = (int)$matches[2];
        }

        while (true) 
		{
            $testname = $counter === 0 ? $prefix : $prefix . $counter;

            $employeeExists = $this->employeeExists($testname);
            $userExists = $this->userExists($testname);

            if (!$employeeExists && !$userExists) 
			{
                $log[] = "Benutzername '{$testname}' ist verfübar.";
                return ["username" => $testname, "log" => $log];
            }

            $log[] = "Benutzername '{$testname}' ist bereits vergeben.";
            $counter++;
        }
    }

	public function validateInput(array $input): void
	{
		if (!isset($input['orgunits']) || !is_array($input['orgunits'])) 
		{
			$input['orgunits'] = [];
		}

		if (!isset($input['orggroups']) || !is_array($input['orggroups'])) 
		{
			$input['orggroups'] = [];
		}

		if (!isset($input['roles']) || !is_array($input['roles'])) 
		{
			$input['roles'] = [];
		}
	}

	private function cleanList($list): array
	{
		if (!is_array($list)) 
		{
			return [];
		}

		$clean = [];

		foreach ($list as $item) 
		{
			// Livewire Snapshot Marker entfernen
			if (is_array($item) && isset($item['s']) && $item['s'] === 'arr') 
			{
				continue;
			}

			// Nested Arrays flatten
			if (is_array($item)) 
			{
				foreach ($item as $id) 
				{
					if (is_numeric($id)) 
					{
						$clean[] = (int)$id;
					}
				}
			}

			// Einzelwerte
			elseif (is_numeric($item)) 
			{
				$clean[] = (int)$item;
			}
		}

		return array_values(array_unique($clean));
	}

	public function disableAllEmployeeOrganizationalUnits(int $employeeId): void
	{
		$today = date("Y-m-d");
		$url = $this->client->getBaseUrl()
			. "/resources/external/employees/{$employeeId}/organizationalunitassignments?referencedate={$today}";

		$list = $this->client->send($url);

		foreach ($list["employeeorganizationalunitassignment"] ?? [] as $entry) {

			if (empty($entry["id"])) continue;

			$detail = $this->client->send(
				$this->client->getBaseUrl() . "/resources/external/employeeorganizationalunitassignments/{$entry["id"]}"
			);

			$payload = $this->closeAssignment($detail);
			$this->putAssignment("employeeorganizationalunitassignments", (int)$entry["id"], $payload);
		}
	}

	public function disableAllEmployeeOrganizationalUnitGroups(int $employeeId): void
	{
		$today = date("Y-m-d");
		$url = $this->client->getBaseUrl()
			. "/resources/external/employees/{$employeeId}/organizationalunitgroupassignments?referencedate={$today}";

		$list = $this->client->send($url);

		foreach ($list["employeeorganizationalunitgroupassignment"] ?? [] as $entry) 
		{

			if (empty($entry["id"])) continue;

			$detail = $this->client->send(
				$this->client->getBaseUrl() . "/resources/external/employeeorganizationalunitgroupassignments/{$entry["id"]}"
			);

			$payload = $this->closeAssignment($detail);
			$this->putAssignment("employeeorganizationalunitgroupassignments", (int)$entry["id"], $payload);
		}
	}

	public function disableAllUserRoles(int $userId): void
	{
		$today = date("Y-m-d");
		$yesterday = date("Y-m-d", strtotime("-1 day"));

		$url = $this->client->getBaseUrl()
			. "/resources/external/users/{$userId}/roleassignments?referencedate={$today}";

		$response = $this->client->send($url);

		foreach ($response["userroleassignment"] ?? [] as $entry) 
		{

			$id = $entry["id"] ?? null;
			if (!$id) continue;

			$existing = $this->client->send(
				$this->client->getBaseUrl() . "/resources/external/userroleassignments/{$id}"
			);

			$this->removeLinks($existing);
			$from = $existing["validityperiod"]["from"] ?? ["date" => "2000-01-01", "handling" => "inclusive"];

			$existing["validityperiod"] = [
				"from" => $from,
				"to" => [
					"date" => $yesterday,
					"handling" => "inclusive"
				]
			];

			$existing["canceled"] = true;

			$this->client->send(
				$this->client->getBaseUrl() . "/resources/external/userroleassignments",
				"PUT",
				$existing
			);
		}
	}

	public function disableAllEmployeeFunctions(int $employeeId): void
	{
		$today = date("Y-m-d");
		$url = $this->client->getBaseUrl()
			. "/resources/external/employees/{$employeeId}/employeefunctionassignments?referencedate={$today}";

		$response = $this->client->send($url);

		foreach ($response["employeeemployeefunctionassignment"] ?? [] as $a) 
		{
			if (!empty($a["id"])) 
			{
				$this->deleteAssignment("employeeemployeefunctionassignments", (int)$a["id"]);
			}
		}
	}

	private function setAssignmentEndDate(string $resource, int $id): void
	{
		$url = $this->client->getBaseUrl() . "/resources/external/{$resource}/{$id}";
		$yesterday = date("Y-m-d", strtotime("-1 day"));

		$existing = $this->client->send($url);

		if (!is_array($existing) || empty($existing["id"])) 
		{
			return;
		}

		$from = $existing["validityperiod"]["from"] ?? ["date" => "2000-01-01"];

		$payload = $existing;
		$payload["canceled"] = true;

		$payload["validityperiod"] = [
			"from" => $from,
			"to" => ["date" => $yesterday]
		];

		$this->client->send(
			$this->client->getBaseUrl() . "/resources/external/{$resource}",
			"PUT",
			$payload
		);
	}

	private function deleteAssignment(string $resource, int $id): void
	{
		$url = $this->client->getBaseUrl()
			. "/resources/external/{$resource}/{$id}";

		$this->client->send($url, "DELETE");
	}

	private function closeAssignment(array $existing): array
	{
		$yesterday = date("Y-m-d", strtotime("-1 day"));
		$this->removeLinks($existing);
		$from = $existing["validityperiod"]["from"] ?? ["date" => "2000-01-01"];

		$existing["validityperiod"] = [
			"from" => $from,
			"to"   => ["date" => $yesterday]
		];

		$existing["canceled"] = true;

		return $existing;
	}

	private function putAssignment(string $resource, int $id, array $payload): void
	{
		$url = $this->client->getBaseUrl() . "/resources/external/{$resource}";
		$payload["id"] = $id;
		$this->removeLinks($payload);
		$this->client->send($url, "PUT", $payload);
	}

	public function createOrganizationalUnitAssignment(int $employeeId, int $unitId, int $rankId): void
	{
		$today = date("Y-m-d");

		$payload = [
			"employee" => ["id" => $employeeId],
			"organizationalunit" => ["id" => $unitId],
			"rank" => ["id" => $rankId],
			"validityperiod" => [
				"from" => ["date" => $today, "handling" => "inclusive"]
			]
		];

		$this->client->send(
			$this->client->getBaseUrl() . "/resources/external/employeeorganizationalunitassignments",
			"POST",
			$payload
		);
	}

	public function createOrganizationalUnitGroupAssignment(int $employeeId, int $groupId): void
	{
		$today = date("Y-m-d");

		$payload = [
			"employee" => ["id" => $employeeId],
			"organizationalunitgroup" => ["id" => $groupId],
			"validityperiod" => [
				"from" => ["date" => $today, "handling" => "inclusive"]
			]
		];

		$this->client->send(
			$this->client->getBaseUrl() . "/resources/external/employeeorganizationalunitgroupassignments",
			"POST",
			$payload
		);
	}

	private function removeLinks(array &$payload): void
	{
		foreach ($payload as $key => &$value) 
		{
			if ($key === 'link') 
			{
				unset($payload[$key]);
				continue;
			}

			if (is_array($value)) 
			{
				$this->removeLinks($value);
			}
		}
	}

	public function updateEmployeeState(int $employeeId, ?int $stateId): void
	{
		$url = $this->client->getBaseUrl() . "/resources/external/employees/{$employeeId}";
		$employee = $this->client->send($url);

		if (!is_array($employee) || empty($employee["id"])) 
		{
			Logger::error("Employee {$employeeId} nicht gefunden.");
			return;
		}

		$this->removeLinks($employee);

		if ($stateId) 
		{
			$employee["state"] = ["id" => (int)$stateId];
		} 
		else 
		{
			$employee["state"] = null;
		}

		$this->client->send(
			$this->client->getBaseUrl() . "/resources/external/employees",
			"PUT",
			$employee
		);
	}

	public function updateEmployeeSigningLevel(int $employeeId, ?int $signingLevelId): void
	{
		$url = $this->client->getBaseUrl() . "/resources/external/employees/{$employeeId}";
		$employee = $this->client->send($url);

		if (!is_array($employee) || empty($employee["id"])) 
		{
			Logger::error("Employee {$employeeId} nicht gefunden.");
			return;
		}

		$this->removeLinks($employee);

		if ($signingLevelId) 
		{
			$employee["signinglevel"] = ["id" => (int)$signingLevelId];
		} 
		else 
		{
			$employee["signinglevel"] = null;
		}

		$this->client->send(
			$this->client->getBaseUrl() . "/resources/external/employees",
			"PUT",
			$employee
		);
	}


	public function resetUserPassword(int $userId, string $username, string $newPassword, bool $mustChange = false): bool
	{
		$username = strtoupper($username);

		// 1. User lesen (Pflicht für vollständiges PUT-Payload)
		$existing = $this->client->send(
			$this->client->getBaseUrl() . "/resources/external/users?name=" . urlencode($username)
		);

		$user = $existing['user'][0] ?? null;

		if (!$user || empty($user['id'])) {
			Logger::error("ORBIS: resetUserPassword(): User {$username} nicht gefunden");
			return false;
		}

		// 2. Pflichtfelder aus vorhandener Ressource erzeugen
		$payload = [
			"id"   => $user['id'],
			"name" => $user['name'],
			"validityperiod" => $user["validityperiod"], // Muss vorhanden sein
			"canceled" => $user["canceled"] ?? false,
			"locked"   => $user["locked"] ?? false,
		];

		// 3. Passwort + mustChange setzen
		$payload["password"] = base64_encode($newPassword);
		$payload["mustchangepassword"] = $mustChange;

		// 4. PUT senden
		$response = $this->client->send(
			$this->client->getBaseUrl() . "/resources/external/users",
			"PUT",
			$payload
		);

		if (!is_array($response)) {
			Logger::error("ORBIS resetUserPassword() PUT fehlgeschlagen");
			return false;
		}

		return true;
	}

	public function lockUser(int $userId): bool
	{
		$existing = $this->client->send(
			$this->client->getBaseUrl() . "/resources/external/users/{$userId}"
		);

		if (!is_array($existing) || empty($existing['id'])) {
			Logger::error("ORBIS lockUser(): User {$userId} nicht gefunden");
			return false;
		}

		$payload = [
			'id'   => $existing['id'],
			'name' => $existing['name'],
			'validityperiod' => $existing['validityperiod'],
			'canceled' => $existing['canceled'] ?? false,
			'locked'   => true,
		];

		$response = $this->client->send(
			$this->client->getBaseUrl() . "/resources/external/users",
			"PUT",
			$payload
		);

		return is_array($response);
	}

	public function unlockUser(int $userId): bool
	{
		$existing = $this->client->send(
			$this->client->getBaseUrl() . "/resources/external/users/{$userId}"
		);

		if (!is_array($existing) || empty($existing['id'])) {
			Logger::error("ORBIS unlockUser(): User {$userId} nicht gefunden");
			return false;
		}

		$payload = [
			'id'   => $existing['id'],
			'name' => $existing['name'],
			'validityperiod' => $existing['validityperiod'],
			'canceled' => $existing['canceled'] ?? false,
			'locked'   => false,
		];

		$response = $this->client->send(
			$this->client->getBaseUrl() . "/resources/external/users",
			"PUT",
			$payload
		);

		return is_array($response);
	}

public function updateUserPayload(array $payload): bool
{
    $resp = $this->client->send(
        $this->client->getBaseUrl() . "/resources/external/users",
        "PUT",
        $payload
    );

    return is_array($resp);
}



}

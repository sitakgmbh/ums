<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Setting;

class SettingsSeeder extends Seeder
{
    public function run(): void
    {
        $settings = [
            [
                "key" => "debug_mode",
                "value" => false,
                "type" => "bool",
                "name" => "Debug-Modus",
                "description" => "Aktiviert erweiterte Protokollierung. Diese Option generiert viele Logs und sollte nicht permanent aktivert sein.",
            ],
			[
				"key" => "show_help",
				"value" => "admin",
				"type" => "enum",
				"name" => "Hilfe anzeigen",
				"description" => "Blendet unten rechts einen Link zur Hilfe-Funktion ein.",
				"options" => ["off", "admin", "all"],
			],
            [
                "key" => "sap_ad_abgleich_excludes_personalnummern",
                "value" => "",
                "type" => "string",
                "name" => "Excludes Personalnummern",
                "description" => "Hinterlegte Personalnummern erzeugen keinen Incident. Trenne mehrere Einträge mit einem Komma.",
            ],
            [
                "key" => "sap_ad_abgleich_excludes_benutzernamen",
                "value" => "",
                "type" => "string",
                "name" => "Excludes Benutzernamen",
                "description" => "Hinterlegte Benutzernamen erzeugen keinen Incident. Trenne mehrere Einträge mit einem Komma.",
            ],
        ];

		foreach ($settings as $data) 
		{
			Setting::updateOrCreate(
				["key" => $data["key"]],
				[
					"type" => $data["type"],
					"name" => $data["name"],
					"description" => $data["description"],
					"value" => Setting::where("key", $data["key"])->exists() ? Setting::where("key", $data["key"])->first()->value : $data["value"], // nur erstellen wenn noch nicht vorhanden
				]
			);
		}
    }
}

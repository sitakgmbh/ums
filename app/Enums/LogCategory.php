<?php

namespace App\Enums;

enum LogCategory: string
{
	case System = "system";
    case Database = "database";
    case Auth = "auth";
    case Api = "api";
	case Email = "email";
	case Ad = "ad";
	case Antraege = "antraege";
	case Sap = "sap";
	case Orbis = "orbis";
	case Otobo = "otobo";
	case MyPdgr = "mypdgr";
	case Graph = "graph";

    public function label(): string
    {
        return match($this) 
		{
            self::System => "System",
            self::Database => "Datenbank",
            self::Auth => "Authentifizierung",
            self::Api => "API",
			self::Email => "E-Mail",
			self::Sap => "SAP",
			self::Orbis => "KIS (ORBIS)",
			self::Antraege => "Anträge",
			self::Otobo => "Otobo",
			self::MyPdgr => "MyPDGR",
			self::Graph => "Microsoft Graph",
			self::Ad => "Active Directory",
        };
    }

    public static function labels(): array
    {
        return collect(self::cases())
            ->mapWithKeys(fn($case) => [$case->value => $case->label()])
            ->toArray();
    }

    public static function isValid(string $value): bool
    {
        return in_array(strtolower($value), array_column(self::cases(), "value"), true);
    }
}

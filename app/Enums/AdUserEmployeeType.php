<?php

namespace App\Enums;

enum AdUserEmployeeType: string
{
    case External         = 'external';
    case Test             = 'test';
    case Internal         = 'internal';
    case InternalPending  = 'internal-pending';
    case Unknown          = 'unknown';

    public function label(): string
    {
        return match ($this) {
            self::External        => 'Externer Mitarbeiter',
            self::Test            => 'Test-Benutzer',
            self::Internal        => 'PDGR Mitarbeiter',
            self::InternalPending => 'PDGR Mitarbeiter ohne Zuordnung',
            self::Unknown         => 'Unbekannt',
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
        return in_array(
            strtolower($value),
            array_map(fn($c) => strtolower($c->value), self::cases()),
            true
        );
    }

	public function badgeClass(): string
	{
		return match($this) {
			self::External        => 'secondary',
			self::Test            => 'info',
			self::Internal        => 'success',
			self::InternalPending => 'warning',
			self::Unknown         => 'danger',
		};
	}
}

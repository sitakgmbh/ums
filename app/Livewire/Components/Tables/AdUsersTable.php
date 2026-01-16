<?php

namespace App\Livewire\Components\Tables;

use App\Livewire\Components\Tables\BaseTable;
use App\Models\AdUser;
use App\Enums\AdUserEmployeeType;
use Illuminate\Database\Eloquent\Builder;

class AdUsersTable extends BaseTable
{
    public bool $showInactive = true;
    public bool $showDeleted = false;

    // Employee-Type-Filterflags (alle standardmässig aktiv)
    public bool $showEmplTypeInternal = true;
    public bool $showEmplTypeExternal = true;
    public bool $showEmplTypeTest = true;
    public bool $showEmplTypeUnknown = true;

    protected $queryString = [
        "showInactive"              => ["except" => true],
        "showDeleted"               => ["except" => false],

        "showEmplTypeInternal"      => ["except" => true],
        "showEmplTypeInternalPending"=> ["except" => true],
        "showEmplTypeExternal"      => ["except" => true],
        "showEmplTypeTest"          => ["except" => true],
        "showEmplTypeUnknown"       => ["except" => true],

        "search"                    => ["except" => ""],
        "perPage"                   => ["except" => 10],
        "sortField"                 => ["except" => null],
        "sortDirection"             => ["except" => null],
    ];

    private const TYPE_ICONS = [
        'external'         => 'mdi mdi-account-arrow-right',
        'test'             => 'mdi mdi-flask',
        'internal'         => 'mdi mdi-account-group',
        'internal-pending' => 'mdi mdi-account-clock',
        'unknown'          => 'mdi mdi-help-circle',
    ];

    public function toggleInactive(): void
    {
        $this->showInactive = !$this->showInactive;
        $this->resetPage();
    }

    public function toggleDeleted(): void
    {
        $this->showDeleted = !$this->showDeleted;
        $this->resetPage();
    }

    // neue Toggles
    public function toggleEmplType(string $type): void
    {
        match ($type) {
            'internal'         => $this->showEmplTypeInternal         = !$this->showEmplTypeInternal,
            'external'         => $this->showEmplTypeExternal          = !$this->showEmplTypeExternal,
            'test'             => $this->showEmplTypeTest              = !$this->showEmplTypeTest,
            'unknown'          => $this->showEmplTypeUnknown           = !$this->showEmplTypeUnknown,
            default => null
        };

        $this->resetPage();
    }

    protected function model(): string
    {
        return AdUser::class;
    }

    protected function getColumns(): array
    {
        return [
            "display_name"  => ["label" => "Anzeigename", "sortable" => true, "searchable" => true],
            "firstname"     => ["label" => "Vorname",      "sortable" => true, "searchable" => true],
            "lastname"      => ["label" => "Nachname",     "sortable" => true, "searchable" => true],
            "username"      => ["label" => "Benutzername", "sortable" => true, "searchable" => true],
            "title"         => ["label" => "Funktion",     "sortable" => true, "searchable" => true],
            "employee_type" => ["label" => "Typ",          "sortable" => true],
            "is_enabled"    => ["label" => "Account",      "sortable" => true],
            "is_existing"   => ["label" => "AD Status",    "sortable" => true],
            "actions"       => ["label" => "Aktionen",     "sortable" => false, "class" => "shrink"],
        ];
    }

    protected function getCustomSorts(): array
    {
        return [
            'employee_type' => function (Builder $query, string $direction) {
                $query->orderByRaw("
                    CASE employee_type
                        WHEN 'external'         THEN 1
                        WHEN 'test'             THEN 2
                        WHEN 'internal-pending' THEN 3
                        WHEN 'internal'         THEN 4
                        ELSE 5
                    END $direction
                ");
            },
        ];
    }

    protected function defaultSortField(): string
    {
        return "display_name";
    }

    protected function defaultSortDirection(): string
    {
        return "asc";
    }

    protected function applyFilters(Builder $query): void
    {
        if (!$this->showDeleted) {
            $query->where("is_existing", true);
        }

        if (!$this->showInactive) {
            $query->where("is_enabled", true);
        }

        if ($this->search) {
            $needle = '%' . strtolower($this->search) . '%';

            $query->where(function ($q) use ($needle) {
                $q->orWhereRaw("LOWER(display_name) LIKE ?", [$needle])
                  ->orWhereRaw("LOWER(firstname) LIKE ?", [$needle])
                  ->orWhereRaw("LOWER(lastname) LIKE ?", [$needle])
                  ->orWhereRaw("LOWER(username) LIKE ?", [$needle])
                  ->orWhereRaw("LOWER(title) LIKE ?", [$needle]);
            });
        }

		$allowed = [];

		if ($this->showEmplTypeExternal) $allowed[] = 'external';
		if ($this->showEmplTypeTest)     $allowed[] = 'test';
		if ($this->showEmplTypeInternal) {
			$allowed[] = 'internal';
			$allowed[] = 'internal-pending'; // wird mitgefiltert
		}
		if ($this->showEmplTypeUnknown)  $allowed[] = 'unknown';

		if (!empty($allowed)) {
			$query->whereIn('employee_type', $allowed);
		}

    }

    protected function getColumnBadges(): array
    {
        return [
            "is_enabled" => [
                true  => ["label" => "Aktiviert", "class" => "success", "icon" => "mdi mdi-check"],
                false => ["label" => "Deaktiviert", "class" => "secondary", "icon" => "mdi mdi-block-helper"],
            ],
            "is_existing" => [
                true  => ["label" => "Vorhanden", "class" => "success", "icon" => "mdi mdi-check"],
                false => ["label" => "Gelöscht", "class" => "secondary", "icon" => "mdi mdi-trash-can"],
            ],
            "employee_type" => collect(AdUserEmployeeType::cases())
                ->mapWithKeys(fn(AdUserEmployeeType $c) => [
                    $c->value => [
                        "label" => $c->label(),
                        "class" => $c->badgeClass(),
                        "icon"  => self::TYPE_ICONS[$c->value] ?? null,
                    ]
                ])
                ->toArray(),
        ];
    }

    protected function getColumnButtons(): array
    {
        return [
            "actions" => [
                [
                    "url"   => fn($row) => route("admin.ad-users.show", $row->id),
                    "icon"  => "mdi mdi-eye",
                    "title" => "Details",
                ],
            ],
        ];
    }

    protected function getTableActions(): array
    {
        return [

            // INTERNAL
            [
                "method" => "toggleEmplType('internal')",
                "icon"   => self::TYPE_ICONS['internal'],
                "class"  => $this->showEmplTypeInternal ? "btn-light" : "btn-outline-light",
                "title"  => $this->showEmplTypeInternal
                    ? "PDGR Mitarbeiter ausblenden"
                    : "PDGR Mitarbeiter anzeigen",
            ],

            // EXTERNAL
            [
                "method" => "toggleEmplType('external')",
                "icon"   => self::TYPE_ICONS['external'],
                "class"  => $this->showEmplTypeExternal ? "btn-light" : "btn-outline-light",
                "title"  => $this->showEmplTypeExternal
                    ? "Externe Mitarbeiter ausblenden"
                    : "Externe Mitarbeiter anzeigen",
            ],

            // TEST
            [
                "method" => "toggleEmplType('test')",
                "icon"   => self::TYPE_ICONS['test'],
                "class"  => $this->showEmplTypeTest ? "btn-light" : "btn-outline-light",
                "title"  => $this->showEmplTypeTest
                    ? "Test-Benutzer ausblenden"
                    : "Test-Benutzer anzeigen",
            ],

            // UNKNOWN
            [
                "method" => "toggleEmplType('unknown')",
                "icon"   => self::TYPE_ICONS['unknown'],
                "class"  => $this->showEmplTypeUnknown ? "btn-light" : "btn-outline-light",
                "title"  => $this->showEmplTypeUnknown
                    ? "Unbekannte ausblenden"
                    : "Unbekannte anzeigen",
            ],

            // disabled
            [
                "method" => "toggleInactive",
                "icon"   => "mdi mdi-block-helper",
                "class"  => $this->showInactive ? "btn-light" : "btn-outline-light",
                "title"  => $this->showInactive
                    ? "Deaktivierte ausblenden"
                    : "Deaktivierte anzeigen",
            ],

            // deleted
            [
                "method" => "toggleDeleted",
                "icon"   => "mdi mdi-trash-can",
                "class"  => $this->showDeleted ? "btn-light" : "btn-outline-light",
                "title"  => $this->showDeleted
                    ? "Gelöschte ausblenden"
                    : "Gelöschte anzeigen",
            ],

            [
                "method" => "exportCsv",
                "icon"   => "mdi mdi-tray-arrow-down",
                "class"  => "btn-outline-light",
                "title"  => "Tabelle als CSV exportieren",
            ],
        ];
    }

    protected function query(): Builder
    {
        return AdUser::query()->select("ad_users.*");
    }
}

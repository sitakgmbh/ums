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

    protected $queryString = [
        "showInactive" => ["except" => true],
        "showDeleted" => ["except" => false],
        "search" => ["except" => ""],
        "perPage" => ["except" => 10],
        "sortField" => ["except" => null],
        "sortDirection" => ["except" => null],
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

    protected function model(): string
    {
        return AdUser::class;
    }

    protected function getColumns(): array
    {
        return [
            "display_name"   => ["label" => "Anzeigename", "sortable" => true, "searchable" => true],
            "firstname"      => ["label" => "Vorname", "sortable" => true, "searchable" => true],
            "lastname"       => ["label" => "Nachname", "sortable" => true, "searchable" => true],
            "username"       => ["label" => "Benutzername", "sortable" => true, "searchable" => true],
            "employee_type_value"     => ["label" => "Typ", "sortable" => true, "searchable" => true],
			"is_enabled"     => ["label" => "Account-Status", "sortable" => true, "searchable" => false],
            "is_existing"    => ["label" => "AD-Status", "sortable" => true, "searchable" => false],
            "actions"        => ["label" => "Aktionen", "sortable" => false, "searchable" => false, "class" => "shrink"],
        ];
    }

	protected function getCustomSorts(): array
	{
		return [
			'employee_type_value' => function ($query, $direction) {
				$query->orderByRaw("
					CASE
						WHEN TRIM(initials) = '00000' THEN 1
						WHEN TRIM(initials) = '11111' THEN 2
						WHEN TRIM(initials) = '99999' THEN 3
						WHEN TRIM(initials) REGEXP '^[67][0-9]{4}$' THEN 4
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
		if (!$this->showDeleted) 
		{
			$query->where("is_existing", true);
		}

		if (!$this->showInactive) 
		{
			$query->where("is_enabled", true);
		}

		if ($this->search) 
		{
			$search = strtolower($this->search);

			$query->where(function ($q) use ($search) {
				$q->orWhereRaw("LOWER(display_name) LIKE ?", ["%{$search}%"])
				  ->orWhereRaw("LOWER(firstname) LIKE ?", ["%{$search}%"])
				  ->orWhereRaw("LOWER(lastname) LIKE ?", ["%{$search}%"])
				  ->orWhereRaw("LOWER(username) LIKE ?", ["%{$search}%"])
				->orWhere(function ($sub) use ($search) {
					$sub->whereRaw("
						CASE
							WHEN TRIM(initials) = '00000' THEN 'extern'
							WHEN TRIM(initials) = '11111' THEN 'test'
							WHEN TRIM(initials) = '99999' THEN 'internal-pending'
							WHEN TRIM(initials) REGEXP '^[67][0-9]{4}$' THEN 'intern'
							ELSE 'unbekannt'
						END LIKE ?
					", ["%{$search}%"]);
				});
			});
		}
	}

    protected function getColumnBadges(): array
    {
        return [
            "is_enabled" => [
                true  => ["label" => "Aktiviert", "class" => "success"],
                false => ["label" => "Deaktiviert", "class" => "secondary"],
            ],
            "is_existing" => [
                true  => ["label" => "Vorhanden", "class" => "success"],
                false => ["label" => "Gelöscht", "class" => "secondary"],
            ],
			'employee_type_value' => [
				'external'         => ['label' => AdUserEmployeeType::External->label(),        'class' => 'secondary'],
				'test'             => ['label' => AdUserEmployeeType::Test->label(),            'class' => 'info'],
				'internal'         => ['label' => AdUserEmployeeType::Internal->label(),        'class' => 'success'],
				'internal-pending' => ['label' => AdUserEmployeeType::InternalPending->label(), 'class' => 'success'],
				'unknown'          => ['label' => AdUserEmployeeType::Unknown->label(),         'class' => 'danger'],
			],
        ];
    }

    protected function getColumnButtons(): array
    {
        return [
            "actions" => [
                [
                    "url"  => fn($row) => route("admin.ad-users.show", $row->id),
                    "icon" => "mdi mdi-eye",
                    "title" => "Details",
                ],
            ],
        ];
    }

    protected function getTableActions(): array
    {
        return [
            [
                "method" => "toggleInactive",
                "icon"   => $this->showInactive ? "mdi mdi-lock" : "mdi mdi-lock",
                "iconClass" => "text-secondary",
                "class"  => $this->showInactive ? "btn-light" : "btn-outline-light",
                "title"  => $this->showInactive ? "Deaktivierte Benutzer ausblenden" : "Deaktivierte Benutzer anzeigen",
            ],
            [
                "method" => "toggleDeleted",
                "icon"   => $this->showDeleted ? "mdi mdi-trash-can" : "mdi mdi-trash-can",
                "iconClass" => "text-secondary",
                "class"  => $this->showDeleted ? "btn-light" : "btn-outline-light",
                "title"  => $this->showDeleted ? "Gelöschte Benutzer ausblenden" : "Gelöschte Benutzer anzeigen",
            ],
            [
                "method" => "exportCsv",
                "icon"   => "mdi mdi-tray-arrow-down",
                "iconClass" => "text-secondary",
                "class"  => "btn-outline-light",
                "title"  => "Tabelle als CSV-Datei exportieren",
            ],
        ];
    }

	protected function query(): Builder
	{
		return AdUser::query()
			->select('ad_users.*')
			->selectRaw("
				CASE
					WHEN TRIM(initials) = '00000' THEN 'external'
					WHEN TRIM(initials) = '11111' THEN 'test'
					WHEN TRIM(initials) = '99999' THEN 'internal-pending'
					WHEN TRIM(initials) REGEXP '^[67][0-9]{4}$' THEN 'internal'
					ELSE 'unknown'
				END AS employee_type_value
			");
	}
}
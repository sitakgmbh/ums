<?php

namespace App\Livewire\Components\Tables;

use App\Livewire\Components\Tables\BaseTable;
use App\Models\SapExport;
use Illuminate\Database\Eloquent\Builder;

class SapExportTable extends BaseTable
{
    protected $queryString = [
        "search" => ["except" => ""],
        "perPage" => ["except" => 10],
        "sortField" => ["except" => null],
        "sortDirection" => ["except" => null],
    ];

    protected function model(): string
    {
        return SapExport::class;
    }

    protected function getColumns(): array
    {
        return [
            "d_name"            => ["label" => "Nachname",        "sortable" => true, "searchable" => true],
            "d_vname"           => ["label" => "Vorname",         "sortable" => true, "searchable" => true],
            "d_rufnm"           => ["label" => "Rufname",         "sortable" => true, "searchable" => true],
            "d_pernr"           => ["label" => "Personalnummer", "sortable" => true, "searchable" => true],
            "d_anrlt"           => ["label" => "Anrede",          "sortable" => true, "searchable" => true],
            "d_titel"           => ["label" => "Titel",           "sortable" => true, "searchable" => true],
            "d_arbortx"         => ["label" => "Arbeitsort", "sortable" => true, "searchable" => true],
            "d_0032_batchbez"   => ["label" => "Funktion", "sortable" => true, "searchable" => true],
            "actions"           => ["label" => "",                "sortable" => false, "searchable" => false, "class" => "shrink"],
        ];
    }

    protected function defaultSortField(): string
    {
        return "d_name";
    }

    protected function defaultSortDirection(): string
    {
        return "asc";
    }

    protected function applyFilters(Builder $query): void
    {
        if ($this->search) {
            $search = strtolower($this->search);

            $query->where(function ($q) use ($search) {
                $q->orWhereRaw("LOWER(d_pernr) LIKE ?", ["%{$search}%"])
                  ->orWhereRaw("LOWER(d_anrlt) LIKE ?", ["%{$search}%"])
                  ->orWhereRaw("LOWER(d_titel) LIKE ?", ["%{$search}%"])
                  ->orWhereRaw("LOWER(d_name) LIKE ?", ["%{$search}%"])
                  ->orWhereRaw("LOWER(d_vname) LIKE ?", ["%{$search}%"])
                  ->orWhereRaw("LOWER(d_rufnm) LIKE ?", ["%{$search}%"])
                  ->orWhereRaw("LOWER(d_arbortx) LIKE ?", ["%{$search}%"])
                  ->orWhereRaw("LOWER(d_0032_batchbez) LIKE ?", ["%{$search}%"]);
            });
        }
    }

	protected function getColumnButtons(): array
	{
		return [
			"actions" => [
				[
					"url"     => fn($row) => route('admin.ad-users.show', ['adUser' => $row->ad_user_id]),
					"icon"    => "mdi mdi-account",
					"title"   => "AD Benutzer anzeigen",
					"showIf"  => fn($row) => $row->hasAdUser(),
					"attrs"   => [
						"class" => "action-icon text-primary"
					]
				],
			],
		];
	}



    protected function getTableActions(): array
    {
        return [
            [
                "method" => "exportCsv",
                "icon"   => "mdi mdi-tray-arrow-down",
                "iconClass" => "text-secondary",
                "class"  => "btn-outline-light",
                "title"  => "CSV Exportieren",
            ],
        ];
    }
}

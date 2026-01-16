<?php

namespace App\Livewire\Components\Tables;

use Illuminate\Database\Eloquent\Builder;
use Livewire\Component;
use Livewire\WithPagination;
use Symfony\Component\HttpFoundation\StreamedResponse;
use ReflectionClass;

abstract class BaseTable extends Component
{
    use WithPagination;

    public string $search = "";
    public int $perPage = 10;

	public string $sortField = "";
	public string $sortDirection = "";

    protected array $customSorts = [];
    public bool $responsiveCollapse = false;

    protected $queryString = [
        "search"        => ["except" => ""],
        "perPage"       => ["except" => 10],
        "sortField"     => ["except" => null],
        "sortDirection" => ["except" => null],
    ];

    abstract protected function model(): string;
    abstract protected function getColumns(): array;

	public function mount(): void
	{
		$this->sortField     = $this->sortField     ?: $this->defaultSortField();
		$this->sortDirection = $this->sortDirection ?: $this->defaultSortDirection();
	}

    protected function defaultSortField(): string
    {
        return "id";
    }

    protected function defaultSortDirection(): string
    {
        return "asc";
    }

    protected function getColumnBadges(): array
    {
        return [];
    }

    protected function getColumnButtons(): array
    {
        return [];
    }

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function sortBy(string $field): void
    {
        $columns = $this->getColumns();
        $col = $columns[$field] ?? null;

        if (is_array($col) && ($col["sortable"] ?? true) === false) 
		{
            return;
        }

        if ($this->sortField === $field) 
		{
            $this->sortDirection = $this->sortDirection === "asc" ? "desc" : "asc";
        } 
		else 
		{
            $this->sortField = $field;
            $this->sortDirection = "asc";
        }

        $this->resetPage();
    }

    public function getRecordsProperty()
    {

		$query = method_exists($this, 'query') ? $this->query() : $this->model()::query();

        $this->applyFilters($query);

        if ($this->usesDefaultApplyFilters() && $this->search && $this->getSearchableColumns()) 
		{
            $search = strtolower($this->search);

            $query->where(function ($q) use ($search) {
                foreach ($this->getSearchableColumns() as $field) 
				{
                    if (str_contains($field, ".")) 
					{
                        [$relation, $col] = explode(".", $field, 2);
                        $q->orWhereHas($relation, function ($sub) use ($col, $search) {
                            $sub->whereRaw("LOWER($col) LIKE ?", ["%{$search}%"]);
                        });
                    } 
					else 
					{
                        $q->orWhereRaw("LOWER($field) LIKE ?", ["%{$search}%"]);
                    }
                }
            });
        }

        $this->applySorting($query);

        return $query->paginate($this->perPage);
    }

    protected function applySorting(Builder $query): void
    {
        $customSorts = method_exists($this, "getCustomSorts") ? $this->getCustomSorts() : [];

		if (isset($customSorts[$this->sortField])) 
		{
			($customSorts[$this->sortField])($query, $this->sortDirection);
		} 
		else 
		{
			$query->orderBy($this->sortField, $this->sortDirection);
		}
    }

    protected function usesDefaultApplyFilters(): bool
    {
        $base = (new ReflectionClass(self::class))->getMethod("applyFilters");
        $actual = (new ReflectionClass(static::class))->getMethod("applyFilters");

        return $base->getDeclaringClass()->getName() === $actual->getDeclaringClass()->getName();
    }

    protected function getSearchableColumns(): array
    {
        return collect($this->getColumns())
            ->filter(fn($col) => is_array($col) ? ($col["searchable"] ?? false) : false)
            ->keys()
            ->toArray();
    }

    public function exportCsv(): StreamedResponse
    {
        $columns = $this->getColumns();
        $visibleColumns = collect($columns)
            ->reject(fn($col) => is_array($col) ? ($col["hidden"] ?? false) : false);

        $filename = "export_" . now()->format("Ymd_His") . ".csv";

        return response()->stream(function () use ($visibleColumns) {
            $handle = fopen("php://output", "w");

            fputcsv($handle, $visibleColumns->map(fn($col) => is_array($col) ? $col["label"] : $col)->toArray());

            $this->model()::chunk(500, function ($rows) use ($handle, $visibleColumns) {
                foreach ($rows as $item) 
				{
                    $row = [];
					
                    foreach ($visibleColumns as $field => $col) 
					{
                        $row[] = data_get($item, $field);
                    }
					
                    fputcsv($handle, $row);
                }
            });

            fclose($handle);
        }, 200, [
            "Content-Type"        => "text/csv",
            "Content-Disposition" => "attachment; filename=\"$filename\"",
        ]);
    }

    public function renderCell(string $field, $row)
    {
        $columns = $this->getColumns();
        $col = $columns[$field] ?? null;
        $value = data_get($row, $field);

        if (method_exists($this, "getColumnFormatters")) 
		{
            $formatters = $this->getColumnFormatters();
			
            if (isset($formatters[$field])) 
			{
                return $formatters[$field]($row);
            }
        }

		// normalize value for badge lookup
		if (is_bool($value)) {
			$value = (int) $value;
		}

		if ($value instanceof \BackedEnum) {
			$value = $value->value;
		}

		// Display normalization: show "." for null/empty (but keep 0/false intact)
		if ($value === null || $value === '') {
			$value = '';
		}

        // Badges
        if ($badge = $this->getColumnBadges()[$field][$value] ?? null) 
		{
            return view("livewire.components.tables.base-table-badge", [
                "label" => $badge["label"] ?? $value,
                "class" => $badge["class"] ?? "secondary",
                "icon"  => $badge["icon"] ?? null,
            ]);
        }

        // Buttons
        if ($buttons = $this->getColumnButtons()[$field] ?? null) 
		{
            return view("livewire.components.tables.base-table-button", [
                "buttons" => $buttons,
                "row"     => $row,
            ]);
        }

        // Raw HTML
        if (is_array($col) && ($col["raw"] ?? false) === true) 
		{
            return $value;
        }

        return e($value);
    }

    protected function applyFilters(Builder $query): void
    {
        // kann überschrieben werden
    }

    public function render()
    {
        return view("livewire.components.tables.base-table", [
            "columns" => $this->getColumns(),
            "records" => $this->records,
        ]);
    }
}

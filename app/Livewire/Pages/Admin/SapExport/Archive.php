<?php

namespace App\Livewire\Pages\Admin\SapExport;

use Livewire\Component;
use Illuminate\Support\Facades\Storage;

class Archive extends Component
{
    public array $versions = [];
    public string $selectedVersion = '';
    public array $rows = [];

    protected array $visibleColumns = [
        'd_name',
        'd_vname',
        'd_rufnm',
		'd_pernr',
		'd_anrlt',
        'd_titel',
		'd_arbortx',      
        'd_0032_batchbez',
    ];

    public function mount(): void
    {
        $this->loadVersions();
    }

	protected function loadVersions(): void
	{
		$files = Storage::disk('private')->files('sap_export');

		$versions = [];

		foreach ($files as $file) {
			$versions[] = [
				'name' => basename($file),
				'mtime' => Storage::disk('private')->lastModified($file),
			];
		}

		usort($versions, fn($a, $b) => $b['mtime'] <=> $a['mtime']);

		$this->versions = array_column($versions, 'name');
	}

    public function loadVersion(): void
    {
        $this->parseCsv($this->selectedVersion);
    }

    protected function parseCsv(string $filename): void
    {
        $this->rows = [];

        $path = storage_path("app/private/sap_export/{$filename}");

        if (!file_exists($path)) {
            return;
        }

        $content = file_get_contents($path);
        $content = preg_replace('/^\xEF\xBB\xBF/', '', $content);

        $encoding = mb_detect_encoding($content, ['UTF-8', 'ISO-8859-1', 'Windows-1252'], true);
        if ($encoding && $encoding !== 'UTF-8') {
            $content = mb_convert_encoding($content, 'UTF-8', $encoding);
        }

        $stream = fopen('php://memory', 'r+');
        fwrite($stream, $content);
        rewind($stream);

        $header = null;
        $rows = [];

        while (($data = fgetcsv($stream, 20000, ';')) !== false) {
            if (!$header) {
                $header = $data;
                continue;
            }

            if (count($data) !== count($header)) {
                $data = array_pad($data, count($header), '');
            }

            $rows[] = array_combine($header, $data);
        }

        fclose($stream);

        $this->rows = $rows;
    }

    public function export()
    {
        if (!$this->selectedVersion) {
            return;
        }

        $path = "sap_export/{$this->selectedVersion}";

        if (!Storage::disk('private')->exists($path)) {
            return;
        }

        return Storage::disk('private')->download($path);
    }

    public function render()
    {
        return view('livewire.pages.admin.sap-export.archive', [
            'visibleColumns' => $this->visibleColumns,
        ])->layout('layouts.app', [
            'pageTitle' => 'SAP Export Archiv',
        ]);
    }
}

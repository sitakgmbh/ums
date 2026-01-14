<?php

namespace App\Livewire\Pages\Admin\Tools;

use App\Models\Eroeffnung;
use App\Models\Mutation;
use Livewire\Component;
use Livewire\Attributes\Layout;
use Illuminate\Support\Facades\Mail;
use App\Mail\{
    TestMail,
    Eroeffnungen\Bestaetigung as EroeffnungBestaetigung,
    Eroeffnungen\AuftragBerufskleider as EroeffnungAuftragBerufskleider,
    Eroeffnungen\AuftragGarderobe as EroeffnungAuftragGarderobe,
    Eroeffnungen\AuftragRaumbeschriftung as EroeffnungAuftragRaumbeschriftung,
    Eroeffnungen\AuftragSap as EroeffnungAuftragSap,
    Eroeffnungen\AuftragLei as EroeffnungAuftragLei,
    Eroeffnungen\AuftragZutrittsrechte as EroeffnungAuftragZutrittsrechte,
    Eroeffnungen\InfoMail as EroeffnungInfoMail,
    Mutationen\Bestaetigung as MutationBestaetigung,
    Mutationen\AuftragBerufskleider as MutationAuftragBerufskleider,
    Mutationen\AuftragGarderobe as MutationAuftragGarderobe,
    Mutationen\AuftragRaumbeschriftung as MutationAuftragRaumbeschriftung,
    Mutationen\AuftragSap as MutationAuftragSap,
    Mutationen\AuftragLei as MutationAuftragLei,
    Mutationen\AuftragZutrittsrechte as MutationAuftragZutrittsrechte,
    Mutationen\InfoMail as MutationInfoMail
};

#[Layout('layouts.app')]
class MailTool extends Component
{
    public string $selectedMailable = '';
    public string $selectedModelId = '';
    public string $recipient = '';
    public ?string $status = null;
    public string $statusType = 'info';

    public array $mailables = [];
    public array $flatMailables = [];
    public array $models = [];

    public function mount(): void
    {
        $this->mailables = [
            'eroeffnungen' => [
                'label' => 'Eroeffnungen',
                'items' => [
                    'eroeffnungen.bestaetigung' => ['label' => 'Bestaetigung', 'model' => Eroeffnung::class, 'modelType' => 'eroeffnungen'],
                    'eroeffnungen.AuftragBerufskleider' => ['label' => 'Auftrag Berufskleider', 'model' => Eroeffnung::class, 'modelType' => 'eroeffnungen'],
                    'eroeffnungen.AuftragGarderobe' => ['label' => 'Auftrag Garderobe', 'model' => Eroeffnung::class, 'modelType' => 'eroeffnungen'],
                    'eroeffnungen.AuftragRaumbeschriftung' => ['label' => 'Auftrag Raumbeschriftung', 'model' => Eroeffnung::class, 'modelType' => 'eroeffnungen'],
                    'eroeffnungen.AuftragSap' => ['label' => 'Auftrag SAP', 'model' => Eroeffnung::class, 'modelType' => 'eroeffnungen'],
                    'eroeffnungen.AuftragLei' => ['label' => 'Auftrag LEI', 'model' => Eroeffnung::class, 'modelType' => 'eroeffnungen'],
                    'eroeffnungen.AuftragZutrittsrechte' => ['label' => 'Auftrag Zutrittsrechte', 'model' => Eroeffnung::class, 'modelType' => 'eroeffnungen'],
                    'eroeffnungen.InfoMail' => ['label' => 'Info-Mail', 'model' => Eroeffnung::class, 'modelType' => 'eroeffnungen'],
                ],
            ],
            'mutationen' => [
                'label' => 'Mutationen',
                'items' => [
                    'mutationen.bestaetigung' => ['label' => 'Bestaetigung', 'model' => Mutation::class, 'modelType' => 'mutationen'],
                    'mutationen.AuftragBerufskleider' => ['label' => 'Auftrag Berufskleider', 'model' => Mutation::class, 'modelType' => 'mutationen'],
                    'mutationen.AuftragGarderobe' => ['label' => 'Auftrag Garderobe', 'model' => Mutation::class, 'modelType' => 'mutationen'],
                    'mutationen.AuftragRaumbeschriftung' => ['label' => 'Auftrag Raumbeschriftung', 'model' => Mutation::class, 'modelType' => 'mutationen'],
                    'mutationen.AuftragSap' => ['label' => 'Auftrag SAP', 'model' => Mutation::class, 'modelType' => 'mutationen'],
                    'mutationen.AuftragLei' => ['label' => 'Auftrag LEI', 'model' => Mutation::class, 'modelType' => 'mutationen'],
                    'mutationen.AuftragZutrittsrechte' => ['label' => 'Auftrag Zutrittsrechte', 'model' => Mutation::class, 'modelType' => 'mutationen'],
                    'mutationen.InfoMail' => ['label' => 'Info-Mail', 'model' => Mutation::class, 'modelType' => 'mutationen'],
                ],
            ],
            'sonstige' => [
                'label' => 'Sonstige',
                'items' => [
                    'testmail' => ['label' => 'Test-Mail', 'model' => null, 'modelType' => null],
                ],
            ],
        ];

        // flach bauen ohne Fehler
        $this->flatMailables = [];
        foreach ($this->mailables as $group) {
            foreach ($group['items'] as $key => $item) {
                $this->flatMailables[$key] = $item;
            }
        }

        $this->models = [
            'eroeffnungen' => Eroeffnung::where('archiviert', false)->get()
                ->map(fn ($e) => [
                    'id' => $e->id,
                    'label' => "Eroeffnung {$e->id} ({$e->nachname} {$e->vorname})"
                ])->toArray(),

            'mutationen' => Mutation::where('archiviert', false)->get()
                ->map(fn ($m) => [
                    'id' => $m->id,
                    'label' => "Mutation {$m->id} (" . ($m->adUser->display_name ?? 'Unbekannt') . ")",
                ])->toArray(),
        ];
    }

	public function getShowTestSectionProperty(): bool
	{
		if (!$this->selectedMailable) {
			return false;
		}

		$item = $this->flatMailables[$this->selectedMailable] ?? null;

		if (!$item) {
			return false;
		}

		$requiresModel = $item['model'] !== null;

		if ($requiresModel && !$this->selectedModelId) {
			return false;
		}

		return true;
	}

	public function getPreviewUrlProperty(): ?string
	{
		if (!$this->selectedMailable) {
			return null;
		}

		$item = $this->flatMailables[$this->selectedMailable] ?? null;
		if (!$item) {
			return null;
		}

		$requiresModel = $item['model'] !== null;

		if ($requiresModel && !$this->selectedModelId) {
			return null;
		}

		// URL bauen
		$url = "/admin/tools/mail-preview/render?mailable={$this->selectedMailable}";

		if ($requiresModel) {
			$url .= "&model_id={$this->selectedModelId}";
		}

		return $url;
	}


    public function send(): void
    {
        $this->validate([
            'recipient' => ['required', 'email'],
            'selectedMailable' => ['required'],
        ]);

        try {
            $factoryMap = [
                'eroeffnungen.bestaetigung' => fn($id) => new EroeffnungBestaetigung(Eroeffnung::findOrFail($id)),
                'eroeffnungen.auftragberufskleider' => fn($id) => new EroeffnungAuftragBerufskleider(Eroeffnung::findOrFail($id)),
                'eroeffnungen.auftraggarderobe' => fn($id) => new EroeffnungAuftragGarderobe(Eroeffnung::findOrFail($id)),
                'eroeffnungen.auftragraumbeschriftung' => fn($id) => new EroeffnungAuftragRaumbeschriftung(Eroeffnung::findOrFail($id)),
                'eroeffnungen.auftragsap' => fn($id) => new EroeffnungAuftragSap(Eroeffnung::findOrFail($id)),
                'eroeffnungen.auftraglei' => fn($id) => new EroeffnungAuftragLei(Eroeffnung::findOrFail($id)),
                'eroeffnungen.auftragzutrittsrechte' => fn($id) => new EroeffnungAuftragZutrittsrechte(Eroeffnung::findOrFail($id)),
                'eroeffnungen.infomail' => fn($id) => new EroeffnungInfoMail(Eroeffnung::findOrFail($id)),

                'mutationen.bestaetigung' => fn($id) => new MutationBestaetigung(Mutation::findOrFail($id)),
                'mutationen.auftragberufskleider' => fn($id) => new MutationAuftragBerufskleider(Mutation::findOrFail($id)),
                'mutationen.auftraggarderobe' => fn($id) => new MutationAuftragGarderobe(Mutation::findOrFail($id)),
                'mutationen.auftragraumbeschriftung' => fn($id) => new MutationAuftragRaumbeschriftung(Mutation::findOrFail($id)),
                'mutationen.auftragsap' => fn($id) => new MutationAuftragSap(Mutation::findOrFail($id)),
                'mutationen.auftraglei' => fn($id) => new MutationAuftragLei(Mutation::findOrFail($id)),
                'mutationen.auftragzutrittsrechte' => fn($id) => new MutationAuftragZutrittsrechte(Mutation::findOrFail($id)),
                'mutationen.infomail' => fn($id) => new MutationInfoMail(Mutation::findOrFail($id)),

                'testmail' => fn() => new TestMail(
                    $this->recipient,
                    route('admin.tools.mail-preview.render', ['mailable' => 'testmail'])
                ),
            ];

            $key = strtolower($this->selectedMailable);
            $factory = $factoryMap[$key] ?? null;

            if (!$factory) {
                throw new \Exception("Mailable nicht gefunden");
            }

            $instance = str_contains($key, 'eroeffnungen.') || str_contains($key, 'mutationen.')
                ? $factory($this->selectedModelId)
                : $factory();

            Mail::to($this->recipient)->send($instance);

            $this->status = "Mail erfolgreich an {$this->recipient} gesendet.";
            $this->statusType = 'success';

        } catch (\Throwable $e) {
            $this->status = "Fehler beim Senden: " . $e->getMessage();
            $this->statusType = 'danger';
        }
    }

    public function render()
    {
        return view('livewire.pages.admin.tools.mail-tool', [
            'mailables' => $this->mailables,
            'flatMailables' => $this->flatMailables,
            'models' => $this->models,
        ])->layoutData([
            'pageTitle' => 'Mail-Tool',
        ]);
    }
}

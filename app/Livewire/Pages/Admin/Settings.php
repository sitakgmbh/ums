<?php

namespace App\Livewire\Pages\Admin;

use Livewire\Component;
use App\Models\Setting;
use Livewire\Attributes\Layout;

#[Layout("layouts.app")]
class Settings extends Component
{
    public array $settings = [];

    protected array $groupMap = [
		"sap_ad_abgleich_excludes_personalnummern" => "Abgleich SAP ↔ AD",
		"sap_ad_abgleich_excludes_benutzernamen" => "Abgleich SAP ↔ AD",
        "debug_mode"          => "System",
		"show_help"          => "System",
    ];

    public function mount(): void
    {
        $keys = array_keys($this->groupMap);

        $this->settings = Setting::query()
            ->whereIn("key", $keys)
            ->get()
            ->map(function ($s) {
                return [
                    "key"         => $s->key,
                    "value"       => $s->type === "password" ? ($s->value ?? "") : $s->value,
                    "type"        => $s->type,
                    "name"        => $s->name,
                    "description" => $s->description,
                    "group"       => $this->groupMap[$s->key] ?? "Sonstiges",
                ];
            })
            ->groupBy("group")
            ->toArray();
    }

    public function save(): void
    {
        foreach ($this->settings as $group => $items) 
		{
            foreach ($items as $s) {
                \Cache::forget("setting_{$s["key"]}");

                Setting::updateOrCreate(
                    ["key" => $s["key"]],
                    [
                        "value" => $s["value"],
                        "type"  => $s["type"],
                    ]
                );
            }
        }

        session()->flash("success", "Einstellungen gespeichert.");
    }

    public function render()
    {
        return view("livewire.pages.admin.settings")
            ->layoutData([
                "pageTitle" => "Einstellungen",
            ]);
    }
}

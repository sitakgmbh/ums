<?php
namespace App\Livewire\Pages\Admin\AdUsers;

use App\Models\AdUser;
use App\Models\SapExport;
use Livewire\Component;
use Livewire\Attributes\Layout;

#[Layout("layouts.app")]
class Show extends Component
{
    public AdUser $adUser;
    public ?SapExport $sapExport = null;

    public function mount(AdUser $adUser): void
    {
		$this->adUser = $adUser->load('lifecycle');
        $this->sapExport = $adUser->sapExport;
    }

    public function render()
    {
        return view("livewire.pages.admin.ad-users.show")
            ->layoutData([
                "pageTitle" => "AD-Benutzer " . ($this->adUser->display_name ?? $this->adUser->username),
            ]);
    }
}
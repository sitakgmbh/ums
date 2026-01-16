<?php

namespace App\Livewire\Pages\Admin\AdUsers;

use App\Models\AdUser;
use Livewire\Component;
use Livewire\Attributes\Layout;

#[Layout('layouts.app')]
class PwReset extends Component
{
    public AdUser $adUser;
    public bool $verified = false;

    public function mount(AdUser $adUser): void
    {
        $this->adUser = $adUser;
    }

    public function render()
    {
        return view('livewire.pages.admin.ad-users.pw-reset')
            ->layoutData([
                'pageTitle' => 'Änderung Passwort ' . ($this->adUser->display_name ?? $this->adUser->username)
            ]);
    }
}

<?php

namespace App\Livewire\Pages\Admin\AdUsers;

use App\Models\AdUser;
use Livewire\Component;
use Livewire\Attributes\Layout;

#[Layout('layouts.app')]
class PwReset extends Component
{
    public AdUser $adUser;

    public function mount(AdUser $adUser): void
    {
        $this->adUser = $adUser;
    }

    public function render()
    {
        $ad = $this->adUser;

        return view('livewire.pages.admin.ad-users.pw-reset', compact('ad'))
            ->layoutData([
                'pageTitle' => 'Account ' . ($ad->display_name ?? $ad->username)
            ]);
    }
}

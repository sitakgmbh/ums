<?php

namespace App\Livewire\Pages\Admin\SapExport;

use Livewire\Component;
use Livewire\Attributes\Layout;

#[Layout('layouts.app')]
class Index extends Component
{
    public function render()
    {
        return view('livewire.pages.admin.sap-export.index')
            ->layoutData([
                'pageTitle' => 'SAP Export',
            ]);
    }
}

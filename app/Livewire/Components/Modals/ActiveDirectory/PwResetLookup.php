<?php

namespace App\Livewire\Components\Modals\ActiveDirectory;

use App\Livewire\Components\Modals\BaseModal;
use App\Models\AdUser;

class PwResetLookup extends BaseModal
{
    public string $username = '';
    public ?string $error = null;

    protected function rules(): array
    {
        return [
            'username' => 'required|string|min:2',
        ];
    }

    protected function openWith(array $payload): bool
    {
        $this->title = "Passwort zurücksetzen";
        $this->size = "md";
        $this->position = "centered";

        $this->backdrop = false;
        $this->headerBg = "bg-primary";
        $this->headerText = "text-white";

        return true;
    }

    public function lookup(): void
    {
        $this->validate();
        $this->error = null;

        $user = AdUser::where('username', $this->username)
            ->where('is_existing', true)
            ->first();

        if (!$user) {
            $this->error = "Der Benutzername '$this->username' existiert nicht.";
            return;
        }

        $this->dispatch('modal-close');
        redirect()->route('admin.ad-users.pw-reset', $user->id);
    }

    public function render()
    {
        return view('livewire.components.modals.active-directory.pw-reset-lookup');
    }
}

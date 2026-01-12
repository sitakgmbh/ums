@extends('livewire.components.modals.base-modal')

@section('body')
    @if($entry)
        <div class="alert alert-info">
            {{ $infoText }}
        </div>

		@if($entry->wiedereintritt && !$adUserExists)
			<div class="alert alert-danger">
				Der AD-Benutzer existiert nicht.
			</div>
		@endif

        <div class="mb-2">
            <label class="form-label">Benutzername</label>
            <input type="text" class="form-control" wire:model.debounce="username" @if($usernameReadonly) readonly @endif>
        </div>

        <div class="mb-2">
            <label class="form-label">E-Mail</label>
            <input type="text" class="form-control" wire:model="email">
        </div>
    @endif
@endsection

@section('footer')
    <button type="button" class="btn btn-secondary" wire:click="closeModal">Abbrechen</button>
    <x-action-button action="confirm" type="button" class="btn btn-primary" wire:click="confirm">Benutzer erstellen</x-action-button>
@endsection

@extends("livewire.components.modals.base-modal")

@section("body")

<div>
    <div class="mb-1">
        <label class="form-label">Benutzername</label>

        <div class="input-group">
            <input type="text" class="form-control" wire:model.defer="username" wire:keydown.enter="lookup" />
            <button class="btn btn-primary" wire:click="lookup" wire:loading.attr="disabled" wire:target="lookup">
                <span wire:loading.remove wire:target="lookup">
                    <i class="mdi mdi-magnify"></i>
                </span>
                <span wire:loading wire:target="lookup">
                    <span class="spinner-border spinner-border-sm"></span>
                </span>
            </button>
        </div>
		    <div class="form-text">AD Benutzername, z. B. musmax</div>
    </div>

    @if($error)
        <div class="alert alert-danger py-2 my-2 mt-2 mb-0">
            {{ $error }}
        </div>
    @endif
</div>

@endsection

@section("footer")
    <button type="button" class="btn btn-secondary" wire:click="closeModal" wire:loading.attr="disabled">
        Schliessen
    </button>
@endsection

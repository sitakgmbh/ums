<div class="card">
    <div class="card-header bg-primary text-white py-1">
        <strong>KIS</strong>
    </div>

    <div class="card-body">

        @if($orbisError)
            <div class="alert alert-danger mb-3">{{ $orbisError }}</div>
        @endif

        @if($orbisSuccess)
            <div class="alert alert-success mb-3">{{ $orbisSuccess }}</div>
        @endif

        <label class="form-label mb-1">Benutzername</label>
        <div class="input-group mb-0">
            <input type="text" class="form-control" wire:model.defer="orbisUsername">
            <button class="btn btn-primary" wire:click="searchOrbisUser" wire:loading.attr="disabled">
                <i class="mdi mdi-magnify"></i>
            </button>
        </div>

        @if($orbisFound)

            <label class="form-label mt-2">Neues Passwort</label>
            <div class="input-group mb-2">
                <input type="text" class="form-control" wire:model.defer="orbisPassword">
                <button class="btn btn-primary" wire:click="generateOrbisPassword">
                    <i class="mdi mdi-autorenew"></i>
                </button>
            </div>

            <label class="form-label">Account</label>
            <div class="mb-2">
                <div class="form-check">
                    <input class="form-check-input"
                           type="radio"
                           wire:model="orbisLockedPending"
                           value="0"
                           id="orbisUnlocked">
                    <label class="form-check-label" for="orbisUnlocked">
                        Entsperrt
                        @if(!$orbisLockedCurrent)
                            <span class="text-muted small">(aktuell)</span>
                        @endif
                    </label>
                </div>

                <div class="form-check">
                    <input class="form-check-input"
                           type="radio"
                           wire:model="orbisLockedPending"
                           value="1"
                           id="orbisLocked">
                    <label class="form-check-label" for="orbisLocked">
                        Gesperrt
                        @if($orbisLockedCurrent)
                            <span class="text-muted small">(aktuell)</span>
                        @endif
                    </label>
                </div>
            </div>


            <label class="form-label">Passwort bei nächster Anmeldung ändern</label>
            <div class="mb-3">
                <div class="form-check">
                    <input class="form-check-input"
                           type="radio"
                           wire:model="orbisMustChangePending"
                           value="0"
                           id="pwNoChange">
                    <label class="form-check-label" for="pwNoChange">
                        Deaktiviert
                        @if(!$orbisMustChangeCurrent)
                            <span class="text-muted small">(aktuell)</span>
                        @endif
                    </label>
                </div>

                <div class="form-check">
                    <input class="form-check-input"
                           type="radio"
                           wire:model="orbisMustChangePending"
                           value="1"
                           id="pwMustChange">
                    <label class="form-check-label" for="pwMustChange">
                        Aktiviert
                        @if($orbisMustChangeCurrent)
                            <span class="text-muted small">(aktuell)</span>
                        @endif
                    </label>
                </div>
            </div>

            <button class="btn btn-primary" wire:click="saveOrbis" wire:loading.attr="disabled">
                <span wire:loading.remove wire:target="saveOrbis">Speichern</span>
                <span wire:loading wire:target="saveOrbis">
                    <span class="spinner-border spinner-border-sm me-1"></span>
                    Bitte warten…
                </span>
            </button>

        @endif

    </div>
</div>

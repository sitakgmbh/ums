<div>

    <div class="card mb-3">
        <div class="card-header text-white bg-primary py-1">
            <strong>ORBIT / KIS</strong>
        </div>

        <div class="card-body">

            @if($orbisError)
                <div class="alert alert-danger mb-3">{{ $orbisError }}</div>
            @endif

            <div class="mb-2">
                <label class="form-label mb-1">Benutzername</label>
                <div class="input-group">
                    <input type="text"
                           class="form-control"
                           wire:model.defer="orbisUsername">
                    <button class="btn btn-primary"
                            wire:click="searchOrbisUser"
                            wire:loading.attr="disabled"
                            wire:target="searchOrbisUser">
                        <i class="mdi mdi-magnify"></i>
                    </button>
                </div>
            </div>

            @if($orbisFound)

                <div class="form-check mb-2">
                    <input id="orbisLocked"
                           type="checkbox"
                           class="form-check-input"
                           wire:model="orbisLocked">

                    <label for="orbisLocked" class="form-check-label">
                        @if($orbisLocked)
                            Account gesperrt
                        @else
                            Account aktiv
                        @endif
                    </label>
                </div>

                <div class="form-check mb-3">
                    <input id="orbisMustChange"
                           type="checkbox"
                           class="form-check-input"
                           wire:model="orbisMustChange">

                    <label for="orbisMustChange" class="form-check-label">
                        @if($orbisMustChange)
                            'Passwort beim naechsten Login aendern' deaktivieren (aktiviert)
                        @else
                            'Passwort beim naechsten Login aendern' aktivieren (deaktiviert)
                        @endif
                    </label>
                </div>

                <div class="mb-3">
                    <label class="form-label mb-1">Neues Passwort</label>
                    <div class="input-group">
                        <input type="text"
                               class="form-control"
                               wire:model.defer="orbisPassword">
                        <button type="button"
                                class="btn btn-primary"
                                wire:click="generateOrbisPassword">
                            <i class="mdi mdi-autorenew"></i>
                        </button>
                    </div>
                </div>

                <button class="btn btn-primary"
                        wire:click="saveOrbis"
                        wire:loading.attr="disabled"
                        wire:target="saveOrbis">
                    <span wire:loading.remove wire:target="saveOrbis">
                        Speichern
                    </span>
                    <span wire:loading wire:target="saveOrbis">
                        <span class="spinner-border spinner-border-sm me-1"></span>
                        bitte warten…
                    </span>
                </button>

            @endif

        </div>
    </div>

</div>

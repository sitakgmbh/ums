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
        <div class="input-group mb-2">
            <input type="text" class="form-control" wire:model.defer="orbisUsername">
            <button class="btn btn-primary" wire:click="searchOrbisUser" wire:loading.attr="disabled">
                <i class="mdi mdi-magnify"></i>
            </button>
        </div>

        @if($orbisFound)

            {{-- Unlock --}}
            <div class="form-check mb-3">
                <input id="orbisUnlock"
                       type="checkbox"
                       class="form-check-input"
                       wire:model="orbisUnlock"
                       @disabled(!$orbisIsLocked)>

                <label class="form-check-label" for="orbisUnlock">
                    Account entsperren
                    @unless($orbisIsLocked)
                        <span class="text-muted small">(nicht gesperrt)</span>
                    @endunless
                </label>
            </div>

            {{-- Password change --}}
            <div class="form-check mb-2">
                <input id="orbisChangePassword"
                       type="checkbox"
                       class="form-check-input"
                       wire:model="orbisChangePassword">

                <label class="form-check-label" for="orbisChangePassword">
                    Passwort ändern
                </label>
            </div>

            @if($orbisChangePassword)
                <div class="mb-3">
                    <label class="form-label mb-1">Neues Passwort</label>
                    <div class="input-group">
                        <input type="text" class="form-control" wire:model.defer="orbisPassword">
                        <button class="btn btn-primary" type="button" wire:click="generateOrbisPassword">
                            <i class="mdi mdi-autorenew"></i>
                        </button>
                    </div>
                </div>

                <div class="form-check mb-3">
                    <input id="orbisForcePwdChange"
                           type="checkbox"
                           class="form-check-input"
                           wire:model="orbisForcePwdChange">

                    <label class="form-check-label" for="orbisForcePwdChange">
                        Passwort beim nächsten Login ändern
                    </label>
                </div>
            @endif

            {{-- Save --}}
            <button class="btn btn-primary"
                    wire:click="saveOrbis"
                    wire:loading.attr="disabled">
                <span wire:loading.remove>Speichern</span>
                <span wire:loading>
                    <span class="spinner-border spinner-border-sm me-1"></span>
                    Bitte warten…
                </span>
            </button>

        @endif

    </div>
</div>

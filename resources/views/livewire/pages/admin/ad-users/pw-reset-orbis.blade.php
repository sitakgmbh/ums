<div>
    <div class="card">
        <div class="card-header bg-primary text-white py-1">
            <strong>KIS (NICE)</strong>
        </div>

        <div class="card-body">

            {{-- Fehler --}}
            @if($orbisError)
                <div class="alert alert-danger mb-3">{{ $orbisError }}</div>
            @endif

            {{-- Erfolg --}}
            @if($orbisSuccess)
                <div class="alert alert-success mb-3">{{ $orbisSuccess }}</div>
            @endif

            {{-- Suche --}}
            <div class="mb-0">
                <label class="form-label mb-1">Benutzername</label>
                <div class="input-group">
                    <input type="text"
                           class="form-control"
                           wire:model.defer="orbisUsername">

                    <button class="btn btn-primary"
                            wire:click="searchOrbisUser"
                            wire:loading.attr="disabled">
                        <i class="mdi mdi-magnify"></i>
                    </button>
                </div>
            </div>

            {{-- NUR wenn gefunden --}}
            @if($orbisFound)

                {{-- Account entsperren --}}
                <div class="form-check mt-3 mb-1">
                    <input id="orbisUnlock"
                           type="checkbox"
                           class="form-check-input"
                           wire:model.live="orbisUnlock"
                           @disabled(!$orbisIsLocked)>

                    <label class="form-check-label" for="orbisUnlock">
                        Account entsperren
                        <span class="text-muted small">
                            ({{ $orbisIsLocked ? 'gesperrt' : 'nicht gesperrt' }})
                        </span>
                    </label>
                </div>

                {{-- Passwort ändern --}}
                <div class="form-check mb-2">
                    <input id="orbisChangePassword"
                           type="checkbox"
                           class="form-check-input"
                           wire:model.live="orbisChangePassword">

                    <label class="form-check-label" for="orbisChangePassword">
                        Passwort ändern
                    </label>
                </div>

                @if($orbisChangePassword)

                    <div class="mb-1">
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

                    <div class="form-check mb-2">
                        <input id="orbisForcePwdChange"
                               type="checkbox"
                               class="form-check-input"
                               wire:model.live="orbisForcePwdChange">

                        <label class="form-check-label" for="orbisForcePwdChange">
                            Passwort beim nächsten Login ändern
                        </label>
                    </div>

                @endif

                {{-- Speichern --}}
                <button class="btn btn-primary mt-2"
                        wire:click="saveOrbis"
                        wire:loading.attr="disabled"
                        wire:target="saveOrbis">

                    <span wire:loading.remove wire:target="saveOrbis">Speichern</span>

                    <span wire:loading wire:target="saveOrbis">
                        <span class="spinner-border spinner-border-sm me-1"></span>
                        Bitte warten…
                    </span>
                </button>

            @endif

        </div>
    </div>
</div>

<div>
    <div class="card">
        <div class="card-header bg-primary text-white py-1">
            <strong>Active Directory</strong>
        </div>

        <div class="card-body">

            @if($adError)
                <div class="alert alert-danger mb-3">{{ $adError }}</div>
            @endif

            @if($adSuccess)
                <div class="alert alert-success mb-3">{{ $adSuccess }}</div>
            @endif

            <div class="mb-3">
                <label class="form-label mb-1">Benutzername</label>
                <input type="text" class="form-control" value="{{ $adUsername }}" disabled>
            </div>

            {{-- Unlock --}}
            <div class="form-check mb-1">
                <input
                    id="adUnlock"
                    type="checkbox"
                    class="form-check-input"
                    wire:model.live="adUnlock"
                    @disabled(!$adIsLocked)
                >

                <label class="form-check-label" for="adUnlock">
                    Account entsperren
                    @unless($adIsLocked)
                        <span class="text-muted small">(nicht gesperrt)</span>
                    @endunless
                </label>
            </div>

            {{-- Passwort ändern --}}
            <div class="form-check mb-2">
                <input
                    id="adChangePassword"
                    type="checkbox"
                    class="form-check-input"
                    wire:model.live="adChangePassword"
                >

                <label class="form-check-label" for="adChangePassword">
                    Passwort ändern
                </label>
            </div>

            @if($adChangePassword)
                <div class="mb-1">
                    <label class="form-label mb-1">Neues Passwort</label>
                    <div class="input-group">
                        <input type="text"
                               class="form-control"
                               wire:model.defer="adPassword">

                        <button class="btn btn-primary"
                                type="button"
                                wire:click="generateAdPassword">
                            <i class="mdi mdi-autorenew"></i>
                        </button>
                    </div>
                </div>

                <div class="form-check mb-2">
                    <input
                        id="adForcePwdChange"
                        type="checkbox"
                        class="form-check-input"
                        wire:model.live="adForcePwdChange"
                    >
                    <label class="form-check-label" for="adForcePwdChange">
                        Passwort beim nächsten Login ändern
                    </label>
                </div>
            @endif

            <button class="btn btn-primary mt-2"
                    wire:click="saveAd"
                    wire:loading.attr="disabled"
                    wire:target="saveAd">

                <span wire:loading.remove wire:target="saveAd">
                    Speichern
                </span>

                <span wire:loading wire:target="saveAd">
                    <span class="spinner-border spinner-border-sm me-1"></span>
                    Bitte warten…
                </span>
            </button>
        </div>
    </div>
</div>

<div>

    <div class="card">
        <div class="card-header text-white bg-primary py-1">
            <strong>Active Directory</strong>
        </div>

        <div class="card-body">

            @if($adError)
                <div class="alert alert-danger mb-3">{{ $adError }}</div>
            @endif

            @if($adSuccess)
                <div class="alert alert-success mb-3">{{ $adSuccess }}</div>
            @endif

            <div class="mb-2">
                <label class="form-label mb-1">Benutzername</label>
                <input type="text"
                       class="form-control"
                       value="{{ $adUsername }}"
                       disabled>
            </div>

            <div class="mb-2">
                <label class="form-label mb-1">Neues Passwort</label>
                <div class="input-group">
                    <input type="text"
                           class="form-control"
                           wire:model.defer="adPassword">
                    <button type="button"
                            class="btn btn-primary"
                            wire:click="generateAdPassword">
                        <i class="mdi mdi-autorenew"></i>
                    </button>
                </div>
            </div>

            <div class="form-check mb-1">
                <input id="adUnlock"
                       type="checkbox"
                       class="form-check-input"
                       wire:model="adUnlock"
                       @disabled(!$adIsLocked)>
                <label for="adUnlock" class="form-check-label">
                    Account entsperren
                    @unless($adIsLocked)
                        <span class="text-muted small">(nicht gesperrt)</span>
                    @endunless
                </label>
            </div>

            <div class="form-check mb-1">
                <input id="adToggleActive"
                       type="checkbox"
                       class="form-check-input"
                       wire:model="adToggleActive">
                <label for="adToggleActive" class="form-check-label">
                    @if($adIsDisabled)
                        Account aktivieren <span class="text-muted small">(deaktiviert)</span>
                    @else
                        Account deaktivieren <span class="text-muted small">(aktiviert)</span>
                    @endif
                </label>
            </div>

            <div class="form-check mb-3">
                <input id="adTogglePwdChange"
                       type="checkbox"
                       class="form-check-input"
                       wire:model="adTogglePwdChange">
                <label for="adTogglePwdChange" class="form-check-label">
                    @if($adRequiresPwdChange)
                        'Passwort beim naechsten Login aendern' deaktivieren
                        <span class="text-muted small">(aktiviert)</span>
                    @else
                        'Passwort beim naechsten Login aendern' aktivieren
                        <span class="text-muted small">(deaktiviert)</span>
                    @endif
                </label>
            </div>

            <button class="btn btn-primary"
                    wire:click="saveAd"
                    wire:loading.attr="disabled"
                    wire:target="saveAd">

                <span wire:loading.remove wire:target="saveAd">
                    Speichern
                </span>

                <span wire:loading wire:target="saveAd">
                    <span class="spinner-border spinner-border-sm me-1"></span>
                    bitte warten…
                </span>
            </button>

        </div>
    </div>

</div>

<div>

    @if ($errors->any())
        <div class="alert alert-danger mb-3">
            <strong>Das eingegebene Passwort ist ungültig:</strong>
            <ul class="mb-0 mt-1">
                @foreach ($errors->all() as $msg)
                    <li>{{ $msg }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    @if($error)
        <div class="alert alert-danger mb-3">{{ $error }}</div>
    @endif

    @if($success)
        <div class="alert alert-success mb-3">{{ $success }}</div>
    @endif

    <div class="row">
        <div class="col-md-6">
            <div class="card mb-3">
                <div class="card-header text-white bg-primary py-1">
                    <strong>Active Directory</strong>
                </div>
                <div class="card-body">

                    <div class="input-group mb-2">
                        <input type="text" class="form-control"
                               wire:model.defer="password"
                               placeholder="Neues Passwort">

                        <button type="button" class="btn btn-primary" title="Passwort generieren"
                                wire:click="generatePassword">
                            <i class="mdi mdi-autorenew"></i>
                        </button>
                    </div>

                    <div class="form-check mb-1">
                        <input id="unlock" type="checkbox" class="form-check-input"
                               wire:model="unlock" @disabled(!$isLocked)>
                        <label for="unlock" class="form-check-label">
                            Account entsperren
                            @if(!$isLocked)
                                <span class="text-muted small">(nicht gesperrt)</span>
                            @endif
                        </label>
                    </div>

                    <div class="form-check mb-1">
                        <input id="toggleActive" type="checkbox" class="form-check-input"
                               wire:model="toggleActive">
                        <label for="toggleActive" class="form-check-label">
                            @if($isDisabled)
                                Account aktivieren <span class="text-muted small">(deaktiviert)</span>
                            @else
                                Account deaktivieren <span class="text-muted small">(aktiviert)</span>
                            @endif
                        </label>
                    </div>

                    <div class="form-check mb-3">
                        <input id="togglePwdChange" type="checkbox" class="form-check-input"
                               wire:model="togglePwdChange">
                        <label for="togglePwdChange" class="form-check-label">
                            @if($requiresPwdChange)
                                'Passwort beim nächsten Login ändern' deaktivieren <span class="text-muted small">(aktiviert)</span>
                            @else
                                'Passwort beim nächsten Login ändern' aktivieren <span class="text-muted small">(deaktiviert)</span>
                            @endif
                        </label>
                    </div>


                    <button wire:click.prevent="save"
                            class="btn btn-primary"
                            wire:loading.attr="disabled"
                            wire:target="save">

                        <span wire:loading.remove wire:target="save">
                            Speichern
                        </span>

                        <span wire:loading wire:target="save">
                            <span class="spinner-border spinner-border-sm me-1"></span>
                            Bitte warten…
                        </span>
                    </button>

                </div>
            </div>
        </div>
    </div>
</div>

<div class="col-md-6">
    <div class="card mb-3">
        <div class="card-header text-white bg-primary py-1">
            <strong>Verifikation</strong>
        </div>
        <div class="card-body">

            <dl class="row mb-2">
                <dt class="col-4">Vorname</dt>
                <dd class="col-8">{{ $ad->firstname ?? '—' }}</dd>

                <dt class="col-4">Nachname</dt>
                <dd class="col-8">{{ $ad->lastname ?? '—' }}</dd>

                <dt class="col-4">E-Mail</dt>
                <dd class="col-8">{{ $ad->email ?? '—' }}</dd>

				<dt class="col-4">Geburtsdatum</dt>
				<dd class="col-8">
					@if($ad->extensionattribute14)
						@php
							$geburt = \Carbon\Carbon::createFromFormat('Ymd', $ad->extensionattribute14);
							$alter = $geburt->age;
						@endphp

						{{ $geburt->format('d.m.Y') }} ({{ $alter }})
					@else
						—
					@endif
				</dd>
            </dl>

            @if ($sap)
                <dl class="row mb-0 mt-3">
                    <dt class="col-4">Private Adresse</dt>
                    <dd class="col-8">
                        {{ $sap->d_adr1_stras ?? '—' }}
                        <br>
                        {{ $sap->d_adr1_pstlz ?? '' }} {{ $sap->d_adr1_ort01 ?? '' }}
                    </dd>
                </dl>
            @endif

        </div>
    </div>
</div>

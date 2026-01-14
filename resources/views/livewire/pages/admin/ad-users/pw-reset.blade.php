<div class="row">

    <!-- AD -->
    <div class="col-md-6">
        <div class="card mb-3">
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
                <label class="form-label mb-1">Benutzername (AD)</label>
                <input type="text"
                       class="form-control"
                       value="{{ $adUsername }}"
                       disabled>
            </div>

            <div class="mb-2">
                <label class="form-label mb-1">Neues Passwort (AD)</label>
                <div class="input-group">
                    <input type="text" class="form-control"
                           wire:model.defer="adPassword">
                    <button type="button" class="btn btn-primary"
                            wire:click="generateAdPassword">
                        <i class="mdi mdi-autorenew"></i>
                    </button>
                </div>
            </div>

                <div class="form-check mb-1">
                    <input id="adUnlock" type="checkbox" class="form-check-input"
                           wire:model="adUnlock" @disabled(!$adIsLocked)>
                    <label for="adUnlock" class="form-check-label">
                        Account entsperren
                    </label>
                </div>

                <div class="form-check mb-1">
                    <input id="adToggleActive" type="checkbox" class="form-check-input"
                           wire:model="adToggleActive">
                    <label for="adToggleActive" class="form-check-label">
                        {{ $adIsDisabled ? 'Account aktivieren' : 'Account deaktivieren' }}
                    </label>
                </div>

                <div class="form-check mb-3">
                    <input id="adTogglePwdChange" type="checkbox" class="form-check-input"
                           wire:model="adTogglePwdChange">
                    <label for="adTogglePwdChange" class="form-check-label">
                        @if($adRequiresPwdChange)
                            'Passwort beim naechsten Login aendern' deaktivieren (aktiviert)
                        @else
                            'Passwort beim naechsten Login aendern' aktivieren (deaktiviert)
                        @endif
                    </label>
                </div>

                <button class="btn btn-primary"
                        wire:click="saveAd"
                        wire:loading.attr="disabled">
                    Speichern
                </button>

            </div>
        </div>
    </div>


    <!-- ORBIS -->
    <div class="col-md-6">
        <div class="card mb-3">
            <div class="card-header text-white bg-primary py-1">
                <strong>ORBIT/KIS</strong>
            </div>
            <div class="card-body">

                @if($orbisError)
                    <div class="alert alert-danger mb-3">{{ $orbisError }}</div>
                @endif

                @if($orbisSuccess)
                    <div class="alert alert-success mb-3">{{ $orbisSuccess }}</div>
                @endif

				<div class="mb-2">
					<label class="form-label mb-1">Kürzel</label>
					<input type="text"
						   class="form-control"
						   wire:model.defer="orbisUsername">
				</div>

				<div class="mb-2">
					<label class="form-label mb-1">Neues Passwort</label>
					<div class="input-group">
						<input type="text" class="form-control"
							   wire:model.defer="orbisPassword">
						<button type="button" class="btn btn-primary"
								wire:click="generateOrbisPassword">
							<i class="mdi mdi-autorenew"></i>
						</button>
					</div>
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


                <button class="btn btn-primary"
                        wire:click="saveOrbis"
                        wire:loading.attr="disabled">
                    Speichern
                </button>

            </div>
        </div>
    </div>

    <!-- VERIFIKATION -->
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

</div>

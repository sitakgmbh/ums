<div>

    <div class="alert alert-warning mb-3">

        <div class="mb-1">
            <strong>Bitte verifiziere die Benutzerinformationen, bevor du ein Passwort zurücksetzt!</strong>
        </div>

        @if($adUser->display_name)
            <div>Name: {{ $adUser->display_name }}</div>
        @endif

        @if($adUser->username)
            <div>Benutzername: {{ $adUser->username }}</div>
        @endif

        @if($adUser->email)
            <div>E-Mail: {{ $adUser->email }}</div>
        @endif

        @if($adUser->extensionattribute2)
            @php
                $geburt = \Carbon\Carbon::createFromFormat('Ymd', $adUser->extensionattribute2);
            @endphp
            <div>Geburtsdatum: {{ $geburt->format('d.m.Y') }} ({{ $geburt->age }})</div>
        @endif

        @if($adUser->last_logon_date)
            <div>
                Letzte Anmeldung: {{ $adUser->last_logon_date->format('d.m.Y H:i') }} Uhr
                <span class="text-muted small">({{ $adUser->last_logon_date->diffForHumans() }})</span>
            </div>
        @endif

        @if($adUser->sapExport)
            <div>
                Privat-Adresse:
                {{ $adUser->sapExport->d_adr1_stras ?? '' }},
                {{ $adUser->sapExport->d_adr1_pstlz ?? '' }}
                {{ $adUser->sapExport->d_adr1_ort01 ?? '' }}
            </div>
        @endif
    </div>

    {{-- Verifizierungs-Checkbox --}}
    <div class="form-check mb-3">
        <input type="checkbox"
               id="verify"
               class="form-check-input"
               wire:model.live="verified"
               @disabled($verified)>

        <label for="verify" class="form-check-label">
            Ich, {{ auth()->user()->adUser->display_name ?? auth()->user()->username ?? '-' }},
            bestätige, den Benutzer {{ $adUser->display_name ?? $adUser->username ?? '-' }} verifiziert zu haben.
        </label>
    </div>

    {{-- Nur anzeigen wenn verifiziert --}}
    @if($verified)
        <div class="row">
            <div class="col-12 col-md-6 mb-2">
                <livewire:pages.admin.ad-users.pw-reset-ad :adUser="$adUser" />
            </div>

            <div class="col-12 col-md-6 mb-2">
                <livewire:pages.admin.ad-users.pw-reset-orbis :adUser="$adUser" />
            </div>
        </div>
    @endif

</div>

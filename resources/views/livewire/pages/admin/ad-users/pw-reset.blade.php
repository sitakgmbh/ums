<div class="row">

    <!-- LINKS: AD -->
    <div class="col-md-6">
        <livewire:pages.admin.ad-users.pw-reset-ad :adUser="$adUser" />
    </div>

    <!-- RECHTS: ORBIS / KIS -->
    <div class="col-md-6">
        <livewire:pages.admin.ad-users.pw-reset-orbis :adUser="$adUser" />
    </div>

    <!-- VERIFIKATION darunter -->
    <div class="col-md-6 mt-3">
        <div class="card mb-3">
            <div class="card-header text-white bg-primary py-1">
                <strong>Verifikation</strong>
            </div>
            <div class="card-body">

                <dl class="row mb-2">
                    <dt class="col-4">Vorname</dt>
                    <dd class="col-8">{{ $adUser->firstname ?? '—' }}</dd>

                    <dt class="col-4">Nachname</dt>
                    <dd class="col-8">{{ $adUser->lastname ?? '—' }}</dd>

                    <dt class="col-4">E-Mail</dt>
                    <dd class="col-8">{{ $adUser->email ?? '—' }}</dd>

                    <dt class="col-4">Geburtsdatum</dt>
                    <dd class="col-8">
                        @if($adUser->extensionattribute14)
                            @php
                                $geburt = \Carbon\Carbon::createFromFormat('Ymd', $adUser->extensionattribute14);
                                $alter = $geburt->age;
                            @endphp
                            {{ $geburt->format('d.m.Y') }} ({{ $alter }})
                        @else
                            —
                        @endif
                    </dd>
                </dl>

                @if ($adUser->sapExport)
                    <dl class="row mb-0 mt-3">
                        <dt class="col-4">Private Adresse</dt>
                        <dd class="col-8">
                            {{ $adUser->sapExport->d_adr1_stras ?? '—' }}
                            <br>
                            {{ $adUser->sapExport->d_adr1_pstlz ?? '' }} {{ $adUser->sapExport->d_adr1_ort01 ?? '' }}
                        </dd>
                    </dl>
                @endif

            </div>
        </div>
    </div>

</div>

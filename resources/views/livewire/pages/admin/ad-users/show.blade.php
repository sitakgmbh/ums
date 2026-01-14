<div>

	@section("pageActions")
		<a href="{{ route('admin.ad-users.pw-reset', $adUser->id) }}" class="btn btn-primary" title="Passwort ändern">
			<i class="mdi mdi-lock"></i>
		</a>
	@endsection

	@if(!$adUser->is_existing)
		<div class="alert alert-danger alert-dismissible fade show mb-3" role="alert">
			<i class="mdi mdi-alert-circle-outline me-1"></i>
			<strong>Achtung:</strong> Dieser Benutzer existiert nicht im Active Directory.
			<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
		</div>
	@endif

	<div class="row">
		{{-- Linke Seite: Profilbild & Stammdaten --}}
		<div class="col-xl-4 col-lg-5">
			<div class="card">
				<div class="card-body text-center">

					{{-- Profilbild --}}
					@if ($adUser->profile_photo_base64)
						<img
							src="data:image/jpeg;base64,{{ $adUser->profile_photo_base64 }}"
							alt="Profilbild"
							width="150"
							height="150"
							class="rounded-circle avatar-xl img-thumbnail mb-2"
							style="object-fit: cover; object-position: top;">
					@else
						<img
							src="{{ asset('assets/images/users/avatar-1.jpg') }}"
							alt="Profilbild"
							width="150"
							height="150"
							class="rounded-circle mb-2"
							style="object-fit: cover; object-position: top;">
					@endif

					{{-- Name + Titel --}}
					<h4 class="mb-0 mt-2">{{ $adUser->display_name ?? $adUser->username }}</h4>
					<p class="text-muted font-14">{{ $adUser->funktion?->name ?? '-' }}</p>

					<div class="pt-3 text-start">
						<h6 class="text-uppercase text-muted fw-bold border-bottom pb-1 mb-2">Personalien</h6>
						<dl class="row mb-0">
							<dt class="col-3">Anrede</dt>
							<dd class="col-9">{{ $adUser->anrede?->name ?? '-' }}</dd>

							<dt class="col-3">Titel</dt>
							<dd class="col-9">{{ $adUser->titel?->name ?? '-' }}</dd>

							<dt class="col-3">Vorname</dt>
							<dd class="col-9">{{ $adUser->firstname }}</dd>

							<dt class="col-3">Nachname</dt>
							<dd class="col-9">{{ $adUser->lastname }}</dd>
						</dl>
					</div>

					<div class="pt-2 text-start">
						<h6 class="text-uppercase text-muted fw-bold border-bottom pb-1 mb-3">Intern</h6>
						<dl class="row mb-0">
							<dt class="col-3">Pers. Nr.</dt>
							<dd class="col-9">{{ $adUser->initials ?? '-' }}</dd>
							
							<dt class="col-3">Arbeitsort</dt>
							<dd class="col-9">{{ $adUser->arbeitsort?->name ?? '-' }}</dd>

							<dt class="col-3">UE</dt>
							<dd class="col-9">{{ $adUser->unternehmenseinheit?->name ?? '-' }}</dd>

							<dt class="col-3">Abteilung</dt>
							<dd class="col-9">{{ $adUser->abteilung?->name ?? $adUser->department ?? '-' }}</dd>

							<dt class="col-3">Funktion</dt>
							<dd class="col-9">{{ $adUser->funktion?->name ?? '-' }}</dd>
						</dl>
					</div>

					<div class="pt-2 text-start">
						<h6 class="text-uppercase text-muted fw-bold border-bottom pb-1 mb-3">Kontakt</h6>
						<dl class="row mb-0">
							<dt class="col-3">E-Mail</dt>
							<dd class="col-9">
								@if ($adUser->email)
									<a href="mailto:{{ $adUser->email }}">{{ $adUser->email }}</a>
								@else
									-
								@endif
							</dd>

							<dt class="col-3">Telefon</dt>
							<dd class="col-9 mb-0">
								@if ($adUser->office_phone)
									<a href="tel:{{ preg_replace('/[^0-9+]/', '', $adUser->office_phone) }}">{{ $adUser->office_phone }}</a>
								@else
									-
								@endif
							</dd>
						</dl>
					</div>

				</div>
			</div>

			<div class="mb-3">
				<a href="{{ route('admin.ad-users.index') }}" class="btn btn-primary">
					<i class="mdi mdi-arrow-left"></i> Zurück
				</a>
			</div>

		</div>


		{{-- Rechte Seite: Tabs --}}
		<div class="col-xl-8 col-lg-7">
			<div class="card">
				<div class="card-body">
					<ul class="nav nav-pills bg-nav-pills nav-justified mb-3">
						<li class="nav-item">
							<a href="#account" data-bs-toggle="tab" aria-expanded="true" class="nav-link rounded-0 active">
								Accountinformationen
							</a>
						</li>
						<li class="nav-item">
							<a href="#groups" data-bs-toggle="tab" aria-expanded="false" class="nav-link rounded-0">
								Gruppenmitgliedschaften
							</a>
						</li>
						<li class="nav-item">
							<a href="#extensions" data-bs-toggle="tab" aria-expanded="false" class="nav-link rounded-0">
								Extension Attributes
							</a>
						</li>
						<li class="nav-item">
							<a href="#sap" data-bs-toggle="tab" aria-expanded="false" class="nav-link rounded-0">
								SAP Stammdaten
							</a>
						</li>
					</ul>

					<div class="tab-content">

						{{-- Accountinformationen --}}
						<div class="tab-pane fade show active" id="account">
							<dl class="row mb-0">
								<dt class="col-sm-4">Benutzername</dt>
								<dd class="col-sm-6">{{ $adUser->username ?? '-' }}</dd>

								<dt class="col-sm-4">Letzte Anmeldung</dt>
								<dd class="col-sm-6">{{ $adUser->last_logon_date?->format('d.m.Y H:i') ?? '-' }}</dd>

								<dt class="col-sm-4">Ablaufdatum</dt>
								<dd class="col-sm-6">{{ $adUser->account_expiration_date?->format('d.m.Y H:i') ?? '-' }}</dd>

								<dt class="col-sm-4">Passwort zuletzt geändert</dt>
								<dd class="col-sm-6">{{ $adUser->password_last_set?->format('d.m.Y H:i') ?? '-' }}</dd>

								<dt class="col-sm-4">Letzte fehlgeschlagene Anmeldung</dt>
								<dd class="col-sm-6">{{ $adUser->last_bad_password_attempt?->format('d.m.Y H:i') ?? '-' }}</dd>

								<dt class="col-sm-4">Anmeldungen</dt>
								<dd class="col-sm-6">{{ $adUser->logon_count }}</dd>

								<dt class="col-sm-4">Status</dt>
								<dd class="col-sm-6">{!! $adUser->is_enabled ? '<span class="badge bg-success">Aktiviert</span>' : '<span class="badge bg-secondary">Deaktiviert</span>' !!}
								</dd>

								<dt class="col-sm-4">Passwort läuft nie ab</dt>
								<dd class="col-sm-6 mb-0">{!! $adUser->password_never_expires ? '<span class="badge bg-success">Ja</span>' : '<span class="badge bg-secondary">Nein</span>' !!}</dd>
							</dl>
						</div>

						{{-- Gruppenmitgliedschaften --}}
						<div class="tab-pane fade" id="groups">
							@if (!empty($adUser->member_of))
								<ul class="list-group list-group-flush mb-0">
									@foreach (collect($adUser->member_of)->sort()->values() as $group)
										<li class="list-group-item py-1 px-2">{{ $group }}</li>
									@endforeach
								</ul>
							@else
								<p class="text-muted mb-0">Keine Gruppenmitgliedschaften gefunden.</p>
							@endif
						</div>

						{{-- Erweiterte Attribute --}}
						<div class="tab-pane fade" id="extensions">
							<dl class="row mb-0">
								@foreach(range(1, 15) as $i)
									@php $key = "extensionattribute{$i}"; @endphp

									<dt class="col-sm-2 {{ $loop->last ? 'mb-0' : '' }}">extensionAttribute{{ $i }}</dt>
									<dd class="col-sm-10 {{ $loop->last ? 'mb-0' : 'mb-1' }}">
										{{ $adUser->$key ?? '-' }}
									</dd>
								@endforeach
							</dl>
						</div>

						{{-- SAP Stammdaten --}}
						<div class="tab-pane fade" id="sap">
							@if($sapExport)
								<dl class="row mb-0">
									@php
										$sapFields = ['d_pernr', 'd_anrlt', 'd_titel', 'd_name', 'd_vname', 'd_rufnm', 'd_gbdat', 'd_empct', 'd_bort', 'd_natio', 'd_arbortx', 'd_0032_batchbez', 'd_einri', 'd_ptext', 'd_email', 'd_pers_txt', 'd_abt_nr', 'd_abt_txt', 'd_0032_batchid', 'd_tel01', 'd_zzbereit', 'd_personid_ext', 'd_zzkader', 'd_adr1_name2', 'd_adr1_stras', 'd_adr1_pstlz', 'd_adr1_ort01', 'd_adr1_land1', 'd_adr1_telnr', 'd_adr5_name2', 'd_adr5_stras', 'd_adr5_pstlz', 'd_adr5_ort01', 'd_adr5_land1', 'd_email_privat', 'd_nebenamt', 'd_nebenbesch', 'd_einda', 'd_endda', 'd_fmht1', 'd_fmht1zus', 'd_fmht2', 'd_fmht2zus', 'd_fmht3', 'd_fmht3zus', 'd_fmht4', 'd_fmht4zus', 'd_kbcod', 'd_leader'];
									@endphp

									@foreach($sapFields as $field)
										<dt class="col-sm-2 {{ $loop->last ? 'mb-0' : '' }}">{{ $field }}</dt>
										<dd class="col-sm-10 {{ $loop->last ? 'mb-0' : 'mb-1' }}">{{ $sapExport->$field ?? '-' }}</dd>
									@endforeach
								</dl>
							@else
								<p class="text-muted mb-0">Keine SAP-Stammdaten gefunden.</p>
							@endif
						</div>
						
						
					</div> <!-- end tab-content -->
				</div>
			</div>
		</div>

	</div>



</div>

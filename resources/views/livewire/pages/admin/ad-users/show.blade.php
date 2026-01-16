<div>

	@section("pageActions")
		<a href="{{ route('admin.ad-users.pw-reset', $adUser->id) }}" class="btn btn-primary" title="Passwort ändern">
			<i class="mdi mdi-key"></i>
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
					@if($adUser->funktion?->name)
						<p class="text-muted font-14 mb-0">
							{{ $adUser->funktion->name }}
						</p>
					@endif

					@if($adUser->employeeType() !== \App\Enums\AdUserEmployeeType::Unknown)
						<p class="text-muted font-14 mb-0">
							{{ $adUser->employeeType()->label() }}
						</p>
					@endif

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
								Active Directory
							</a>
						</li>
						<li class="nav-item">
							<a href="#sap" data-bs-toggle="tab" aria-expanded="false" class="nav-link rounded-0">
								SAP Stammdaten
							</a>
						</li>
						<li class="nav-item">
							<a href="#lifecycle" data-bs-toggle="tab" aria-expanded="false" class="nav-link rounded-0">
								Lifecycle
							</a>
						</li>
					</ul>

					<div class="tab-content">

						{{-- AD-Benutzer --}}
						<div class="tab-pane fade show active" id="account">

							<h6 class="text-uppercase text-muted fw-bold border-bottom pb-1 mb-2"> Accountinformationen </h6>

							{{-- Basis --}}
							<dl class="row mb-3">
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
								<dd class="col-sm-6">
									{!! $adUser->is_enabled
										? '<span class="badge bg-success">Aktiviert</span>'
										: '<span class="badge bg-secondary">Deaktiviert</span>'
									!!}
								</dd>

								<dt class="col-sm-4">Passwort läuft nie ab</dt>
								<dd class="col-sm-6 mb-0">
									{!! $adUser->password_never_expires
										? '<span class="badge bg-success">Ja</span>'
										: '<span class="badge bg-secondary">Nein</span>'
									!!}
								</dd>
							</dl>


							<h6 class="text-uppercase text-muted fw-bold border-bottom pb-1 mb-2"> Details </h6>

							<div class="d-flex gap-2 mb-0">
								<button class="btn btn-sm btn-outline-light"
										data-bs-toggle="collapse"
										data-bs-target="#collapseGroups">
									Gruppenmitgliedschaften
								</button>

								<button class="btn btn-sm btn-outline-light"
										data-bs-toggle="collapse"
										data-bs-target="#collapseExtensions">
									Extension Attributes
								</button>
							</div>

							{{-- Gruppenmitgliedschaften --}}
							<div id="collapseGroups" class="collapse mt-3 mb-0">
								<h6 class="text-uppercase text-muted fw-bold border-bottom pb-1 mb-2">
									Gruppenmitgliedschaften
								</h6>

								@if (!empty($adUser->member_of))
									<ul class="list-group list-group-flush mb-0">
										@foreach (collect($adUser->member_of)->sort()->values() as $group)
											<li class="list-group-item py-1 px-2">{{ $group }}</li>
										@endforeach
									</ul>
								@else
									<p class="text-muted mb-0">Keine Gruppen gefunden.</p>
								@endif
							</div>


							{{-- Extension Attributes --}}
							<div id="collapseExtensions" class="collapse mt-3 mb-0">
								<h6 class="text-uppercase text-muted fw-bold border-bottom pb-1 mb-2">
									Extension Attributes
								</h6>

								@php
									$ext = collect(range(1,15))->map(fn($i) => "extensionattribute{$i}");
									$hasExt = $ext->contains(fn($k) => filled($adUser->$k));
								@endphp

								@if(!$hasExt)
									<p class="text-muted mb-0">Keine Extension Attributes gesetzt.</p>
								@else
									<dl class="row mb-0">
										@foreach($ext as $key)
											@php $val = $adUser->$key; @endphp
											@if(filled($val))
												<dt class="col-sm-3">{{ $key }}</dt>
												<dd class="col-sm-9 mb-1">{{ $val }}</dd>
											@endif
										@endforeach
									</dl>
								@endif
							</div>

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



						{{-- Lifecycle --}}
						@php
							$eventIcons = [
								'ad_user_created'          => ['icon' => 'mdi-account-plus',         'class' => 'bg-success-lighten text-success'],
								'personal_number_assigned' => ['icon' => 'mdi-card-account-details', 'class' => 'bg-info-lighten text-info'],
								'mutation_created'         => ['icon' => 'mdi-file-document-edit',   'class' => 'bg-primary-lighten text-primary'],
								'ad_user_change'           => ['icon' => 'mdi-pencil',               'class' => 'bg-warning-lighten text-warning'],
								'termination_registered'   => ['icon' => 'mdi-account-remove',       'class' => 'bg-danger-lighten text-danger'],
								'ad_user_disabled'         => ['icon' => 'mdi-account-off',          'class' => 'bg-secondary-lighten text-secondary'],
								'ad_user_deleted'          => ['icon' => 'mdi-account-cancel',       'class' => 'bg-dark text-light'],
							];

							$defaultIcon = ['icon' => 'mdi-information', 'class' => 'bg-light text-muted'];

							$events = $adUser->lifecycle->sortBy('event_at');
							$latest = $events->first();
						@endphp


						<div class="tab-pane fade" id="lifecycle">

							@if(!$latest)
								<p class="text-muted mb-0">Keine Lifecycle-Events gefunden.</p>
							@else

								<span class="text-muted small mb-3 d-inline-block">
									Letzte Änderung: {{ $latest->event_at->format('d.m.Y H:i') }}
								</span>

								<div class="timeline-alt py-0">

									@foreach($events as $event)

										@php
											$meta = $eventIcons[$event->event] ?? $defaultIcon;
											$hasContext = filled($event->context);
											$isLast = $loop->last;
										@endphp

										<div class="timeline-item {{ $isLast ? 'pb-0' : '' }}">
											<i class="mdi {{ $meta['icon'] }} {{ $meta['class'] }} timeline-icon"></i>

											<div class="timeline-item-info">

												<span class="fw-bold d-block mb-0">
													{{ $event->event_label }}
												</span>

												<p class="mb-2">
													<small class="text-muted">
														{{ $event->event_at->diffForHumans() }}
													</small>
												</p>

												@if($event->description)
													<small class="d-block text-muted mb-1">{{ $event->description }}</small>
												@endif

												@if($hasContext)
													<span
														class="text-info small d-inline-flex align-items-center mb-1"
														role="button"
														data-bs-toggle="collapse"
														data-bs-target="#ctx-{{ $event->id }}"
														style="cursor:pointer;"
													>
														<i class="mdi mdi-information-outline"></i>
													</span>

													<div id="ctx-{{ $event->id }}" class="collapse mt-2 mb-2">
														<pre class="small bg-light p-2 rounded mb-0" style="font-size:12px; white-space:pre;">{{ rtrim(json_encode($event->context, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)) }}</pre>
													</div>
												@else
													<span
														class="text-muted small d-inline-flex align-items-center mb-1"
														style="opacity:0.4; cursor:not-allowed;"
													>
														<i class="mdi mdi-information-outline"></i>
													</span>
												@endif

											</div>
										</div>

									@endforeach

								</div>

							@endif

						</div>









						
					</div> <!-- end tab-content -->
				</div>
			</div>
		</div>

	</div>



</div>

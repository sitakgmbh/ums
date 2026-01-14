<div>

	@role('admin')
	<div class="row">
		<div class="col-12">
			<div class="card">
                <div class="card-header text-white bg-primary py-1">
                    <strong>Wochenübersicht</strong>
                </div>
				<div class="card-body">
					<div class="table-responsive">
						<table class="table table-sm table-bordered mb-0">
							<thead class="bg-light">
								<tr>
									@foreach($wochenUebersicht as $tag => $daten)
										<th class="{{ $daten['heute'] ? 'table-dark' : '' }} {{ $daten['heute'] ? 'table-white' : 'text-secondary' }}">
											{{ $tag }} {{ $daten['datum'] }}
										</th>
									@endforeach
								</tr>
							</thead>
							<tbody>
								<tr>
									@foreach($wochenUebersicht as $tag => $daten)
										<td>
											<strong class="d-flex align-items-center gap-1">
												Eintritte
												<span class="badge {{ $daten['eroeffnungen']->count() === 0 ? 'bg-secondary' : 'bg-info' }}">
													{{ $daten['eroeffnungen']->count() }}
												</span>
											</strong>
											<ul class="list-unstyled mb-2">
												@foreach($daten['eroeffnungen'] as $eroeffnung)
													<li>
														<a href="{{ route('admin.eroeffnungen.verarbeitung', $eroeffnung['id']) }}">
															{{ $eroeffnung['name'] }}
														</a>
													</li>
												@endforeach
											</ul>

											<strong class="d-flex align-items-center gap-1">
												Mutationen
												<span class="badge {{ $daten['mutationen']->count() === 0 ? 'bg-secondary' : 'bg-info' }}">
													{{ $daten['mutationen']->count() }}
												</span>
											</strong>
											<ul class="list-unstyled mb-2">
												@foreach($daten['mutationen'] as $mutation)
													<li>
														<a href="{{ route('admin.mutationen.verarbeitung', $mutation['id']) }}">
															{{ $mutation['name'] }}
														</a>
													</li>
												@endforeach
											</ul>

											<strong class="d-flex align-items-center gap-1">
												Austritte
												<span class="badge {{ $daten['austritte']->count() === 0 ? 'bg-secondary' : 'bg-info' }}">
													{{ $daten['austritte']->count() }}
												</span>
											</strong>
											<ul class="list-unstyled mb-0">
												@foreach($daten['austritte'] as $austritt)
													<li>
														<a href="{{ route('admin.austritte.verarbeitung', $austritt['id']) }}">
															{{ $austritt['name'] }}
														</a>
													</li>
												@endforeach
											</ul>
										</td>
									@endforeach
								</tr>
							</tbody>
						</table>
					</div>
				</div>
			</div>
		</div>
	</div>
	@endrole

    <div class="row">
        <div class="col-12 col-md-6 col-lg-3">
            <div class="card widget-flat">
                <div class="card-body">
                    <div class="float-end">
                        <i class="mdi mdi-account-plus widget-icon bg-light"></i>
                    </div>
                <h5 class="text-secondary fw-normal mt-0">Meine Eröffnungen</h5>
                    <h3 class="mt-3 mb-0">{{ $eroeffnungenCount }}</h3>
                </div>
            </div>
        </div>

        <div class="col-12 col-md-6 col-lg-3">
            <div class="card widget-flat">
                <div class="card-body">
                    <div class="float-end">
                        <i class="mdi mdi-account-edit widget-icon bg-light"></i>
                    </div>
                    <h5 class="text-secondary fw-normal mt-0">Meine Mutationen</h5>
                    <h3 class="mt-3 mb-0">{{ $mutationenCount }}</h3>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-12 col-md-12 col-lg-6">
            <div class="card">
                <div class="card-header text-white bg-primary py-1">
                    <strong>Hilfe</strong>
                </div>
                <div class="card-body">
                    <p class="card-text">
                        Klicke auf <strong>Eröffnung erstellen</strong>, um einen neuen Eintritt oder Wiedereintritt zu erfassen.<br>
                        Über <strong>Mutation erstellen</strong> kannst du bestehende Benutzerdaten aktualisieren.<br>
                        <strong>Namensänderungen</strong> sind lediglich dem HR zu melden, der entsprechende Auftrag wird automatisch generiert.
                    </p>

                    <div class="d-flex gap-2">
                        <a href="{{ route('eroeffnungen.create') }}" class="btn btn-light">Eröffnung erstellen</a>
                        <a href="{{ route('mutationen.create') }}" class="btn btn-light">Mutation erstellen</a>
                    </div>
                </div>
            </div>
        </div>
    </div>

</div>

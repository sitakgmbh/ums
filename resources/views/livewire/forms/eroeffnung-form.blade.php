<form wire:submit.prevent="save">
    <div class="row">

        <div class="col-12 col-md-6 order-1 d-flex flex-column gap-3">

            <div class="card mb-0">
                <div class="card-header bg-primary text-white py-1">
                    <p class="mb-0"><strong>Stammdaten</strong></p>
                </div>
                <div class="card-body d-flex flex-column gap-3">

                    <div class="row g-3">
                        <div class="col-md-6 d-flex flex-column gap-1" wire:ignore>
                            <label for="anrede_id" class="form-label mb-0">Anrede</label>
                            <select id="anrede_id" class="form-control select2" @disabled($form->isReadonly)></select>
                        </div>
                        <div class="col-md-6 d-flex flex-column gap-1" wire:ignore>
                            <label for="titel_id" class="form-label mb-0">Titel</label>
                            <select id="titel_id" class="form-control select2" @disabled($form->isReadonly)></select>
                        </div>
                        <div class="col-md-6 d-flex flex-column gap-1">
                            <label for="vorname" class="form-label mb-0">Vorname</label>
                            <input type="text" id="vorname" wire:model.trim.blur="form.vorname" class="form-control" @disabled($form->isReadonly)>
                        </div>
                        <div class="col-md-6 d-flex flex-column gap-1">
                            <label for="nachname" class="form-label mb-0">Nachname</label>
                            <input type="text" id="nachname" wire:model.trim.blur="form.nachname" class="form-control" @disabled($form->isReadonly)>
                        </div>
                    </div>

                    <div class="row g-3">
                        <div class="col-md-6 d-flex flex-column gap-1" wire:ignore>
                            <label for="arbeitsort_id" class="form-label mb-0">Arbeitsort</label>
                            <select id="arbeitsort_id" class="form-control select2" @disabled($form->isReadonly)></select>
                        </div>
                        <div class="col-md-6 d-flex flex-column gap-1" wire:ignore>
                            <label for="unternehmenseinheit_id" class="form-label mb-0">Unternehmenseinheit</label>
                            <select id="unternehmenseinheit_id" class="form-control select2" @disabled($form->isReadonly)></select>
                        </div>
                        <div class="col-md-6 d-flex flex-column gap-1" wire:ignore>
                            <div class="d-flex align-items-center">
                                <label for="abteilung_id" class="form-label mb-0">Abteilung</label>
                                <i class="mdi mdi-information-outline text-muted ms-1" data-bs-toggle="tooltip" title="Ergänze die Abteilung im Kommentarfeld, wenn kein spezifischer Bereich auswählbar ist."></i>
                            </div>
                            <select id="abteilung_id" class="form-control select2" @disabled($form->isReadonly)></select>
                            <div class="form-check">
                                <input type="checkbox" id="zusatz_abteilung" wire:model="form.has_abteilung2" class="form-check-input" @disabled($form->isReadonly)>
                                <label class="form-check-label" for="zusatz_abteilung">Zusätzliche Abteilung</label>
                            </div>
                            <div x-data>
                                <div wire:ignore x-show="$wire.form.has_abteilung2" x-cloak>
                                    <select id="abteilung2_id" class="form-control select2" @disabled($form->isReadonly)></select>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6 d-flex flex-column gap-1" wire:ignore>
                            <div class="d-flex align-items-center">
                                <label for="funktion_id" class="form-label mb-0">Funktion</label>
                                <i class="mdi mdi-information-outline text-muted ms-1" data-bs-toggle="tooltip" title="Kontaktiere das HR, wenn die gesuchte Funktion nicht verfügbar ist."></i>
                            </div>
                            <select id="funktion_id" class="form-control select2" @disabled($form->isReadonly)></select>
                            <div class="form-check">
                                <input type="checkbox" id="neue_konstellation" wire:model="form.neue_konstellation" class="form-check-input" @disabled($form->isReadonly)>
                                <label class="form-check-label" for="neue_konstellation">Neue Konstellation</label>
								<i class="mdi mdi-information-outline text-muted ms-1" data-bs-toggle="tooltip" title="Aktiviere diese Option, wenn es sich um eine neue Konstellation von Arbeitsort, Unternehmenseinheit, Abteilung und Funktion handelt."></i>
                            </div>
                        </div>
                    </div>

                    <div class="row g-3">
                        <div class="col-md-6 d-flex flex-column gap-1" wire:ignore>
                            <label for="bezugsperson_id" class="form-label mb-0">Bezugsperson</label>
                            <select id="bezugsperson_id" class="form-control select2" @disabled($form->isReadonly)></select>
                            <div class="form-check">
                                <input type="checkbox" id="filter_mitarbeiter" wire:model="form.filter_mitarbeiter" class="form-check-input" @disabled($form->isReadonly)>
                                <label class="form-check-label" for="filter_mitarbeiter">Mitarbeiter filtern</label>
								<i class="mdi mdi-information-outline text-muted ms-1" data-bs-toggle="tooltip" title="Deaktiviere diese Option, wenn der gesuchte Mitarbeiter in einer anderen Abteilung arbeitet oder eine andere Funktion ausübt."></i>
                            </div>
                        </div>
						<div class="col-md-6 d-flex flex-column gap-1" wire:ignore>
							<div class="d-flex align-items-center">
								<label for="vorlage_benutzer_id" class="form-label mb-0">PC Berechtigungen übernehmen von</label>
								<i class="mdi mdi-alert-circle-outline text-danger ms-1" data-bs-toggle="tooltip" title="Die Funktion des ausgewählten Mitarbeiters sollte mit der Funktion des neuen Mitarbeiters übereinstimmen."></i>
							</div>
							<select id="vorlage_benutzer_id" class="form-control select2" @disabled($form->isReadonly)></select>
							<small class="text-danger">Bitte beachte den Hinweis!</small>
						</div>

                        <div class="col-md-6 d-flex flex-column gap-1" wire:ignore>
                            <label for="mailendung" class="form-label mb-0">E-Mail-Domain</label>
                            <select id="mailendung" class="form-control select2" @disabled($form->isReadonly)></select>
                        </div>
                        <div class="col-md-6 d-flex flex-column gap-1">
                            <label for="vertragsbeginn" class="form-label mb-0">Vertragsbeginn</label>
                            <input type="date" id="vertragsbeginn" class="form-control" wire:model="form.vertragsbeginn"  @disabled($form->isReadonly)>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card mb-0">
                <div class="card-header bg-primary text-white py-1">
                    <p class="mb-0"><strong>Kommentar</strong></p>
                </div>
                <div class="card-body">
                    <textarea id="kommentar" class="form-control" wire:model.defer="form.kommentar" rows="4" @disabled($form->isReadonly)></textarea>
                </div>
            </div>

			<div class="d-none d-md-block">
				@if ($errors->any())
					<div class="alert alert-danger mb-3">
						<ul class="mb-0 ps-2">
							@foreach ($errors->all() as $error)
								<li class="mb-0">{{ $error }}</li>
							@endforeach
						</ul>
					</div>
				@endif

				<button type="submit" class="btn btn-primary mb-3 @disabled($form->isReadonly)" wire:loading.attr="disabled" wire:target="save">
					<span wire:loading.remove wire:target="save">Speichern</span>
					<span wire:loading wire:target="save">
						<span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>
						Bitte warten
					</span>
				</button>
			</div>

        </div>

        <div class="col-12 col-md-6 order-2">
            <div class="card mt-3 mt-md-0">
                <div class="card-header bg-primary text-white py-1">
                    <p class="mb-0"><strong>Optionen</strong></p>
                </div>
                <div class="card-body">
                    <div class="row g-3">
						
						<div class="col-12 col-md-6 d-flex flex-column gap-1">
							@php
								$fields = [
									'kis_status'            => 'KIS-Benutzeraccount erstellen',
									'sap_status'            => 'SAP-Benutzeraccount erstellen',
									'is_lei'                => 'SAP-Leistungserbringer eröffnen',
									'tel_status'            => 'Telefonie',
									'raumbeschriftung_flag' => 'Raumbeschriftung',
									'key_waldhaus'          => 'Schlüsselrecht Klinik Waldhaus',
									'key_beverin'           => 'Schlüsselrecht Klinik Beverin',
									'key_rothenbr'          => 'Schlüsselrecht Rothenbrunnen',
									'berufskleider'         => 'Berufsbekleidung',
									'garderobe'             => 'Garderobe',
									'vorab_lizenzierung'=> 'Vor Eintritt lizenzieren',
								];

								$tooltips = [
									'vorab_lizenzierung' => 'Beachte, dass die frühzeitige Lizenzierung zusätzliche Lizenzkosten für die PDGR verursacht.',
								];
							@endphp

							@foreach($fields as $field => $label)
								<div class="form-check d-flex align-items-center">
									<input
										class="form-check-input"
										@disabled($form->isReadonly)
										type="checkbox"
										id="checkbox{{ ucfirst($field) }}"
										wire:model="form.{{ $field }}"
									>
									<label class="form-check-label ms-1" for="checkbox{{ ucfirst($field) }}">
										{{ $label }}
									</label>
										@if(isset($tooltips[$field]))
											<i class="mdi mdi-information-outline text-muted ms-1"
											   data-bs-toggle="tooltip"
											   data-bs-html="true"
											   data-bs-title="{!! $tooltips[$field] !!}"></i>
										@endif
								</div>
							@endforeach
						</div>

                        <div class="col-12 col-md-6 d-flex flex-column gap-3">
                            {{-- SAP Rolle --}}
                            <div x-data x-show="$wire.form.sap_status" x-cloak>
                                <label class="form-label" for="inputSapRolle">SAP Rolle</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="mdi mdi-toolbox"></i></span>
                                    <select class="form-select" id="inputSapRolle" wire:model="form.sap_rolle_id" @disabled($form->isReadonly)>
                                        <option value="">Bitte auswählen</option>
                                        @foreach($form->sapRollen as $rolle)
                                            <option value="{{ $rolle['id'] }}">{{ $rolle['name'] }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>

							<div x-show="$wire.form.tel_status" x-cloak>
								<div x-data="telefonieHandler()" x-init="init()" class="d-flex flex-column gap-2">
									
									<div>
										<label class="form-label" for="inputTelefonAuswahl">Telefonie</label>
										<div class="input-group">
											<span class="input-group-text"><i class="mdi mdi-phone"></i></span>
											<select class="form-select" id="inputTelefonAuswahl"
													x-model="auswahl"
													@change="applyState"
													wire:model="form.tel_auswahl"
													@disabled($form->isReadonly)>
												<option value="">Bitte auswählen</option>
												<option value="uebernehmen">Persönliche Nummer übernehmen</option>
												<option value="neu">Neue Nummer</option>
												<option value="manuell">Unpersönliche Nummer</option>
											</select>
										</div>
									</div>

									<div>
										<label class="form-label" for="inputTelefonnummer">Telefonnummer</label>
										<div class="input-group">
											<span class="input-group-text"><i class="mdi mdi-phone-incoming"></i></span>

											{{-- fixer Wert, kein wire:model --}}
											<template x-if="auswahl === 'neu'">
												<input type="text"
													   id="inputTelefonnummer"
													   class="form-control"
													   value="wird von ICT festgelegt"
													   disabled>
											</template>

											{{-- mit Livewire Binding --}}
											<template x-if="auswahl !== 'neu'">
												<input type="text"
													   id="inputTelefonnummer"
													   class="form-control"
													   wire:model="form.tel_nr"
													   placeholder="+41 58 225 XXXX oder XXXX"
													   :disabled="$wire.form.isReadonly || nummerDisabled">
											</template>
										</div>
									</div>

									<div>
										<label class="form-label">Optionen</label>
										<div class="d-flex flex-column gap-0">
											<div class="form-check">
												<input class="form-check-input" type="checkbox" id="checkboxTischtelefon"
													   x-model="tischtelefon" @change="updateHeadset"
													   wire:model="form.tel_tischtel"
													   @disabled($form->isReadonly)>
												<label class="form-check-label" for="checkboxTischtelefon">Tischtelefon</label>
											</div>
											<div class="form-check">
												<input class="form-check-input" type="checkbox" id="checkboxMobiltelefon"
													   wire:model="form.tel_mobiltel" x-model="mobiltelefon"
													   @change="updateAlarmierung"
													   @disabled($form->isReadonly)>
												<label class="form-check-label" for="checkboxMobiltelefon">Mobiltelefon</label>
											</div>
											<div class="form-check">
												<input class="form-check-input" type="checkbox" id="checkboxUcStandard"
													   x-model="ucstandard" @change="updateHeadset"
													   wire:model="form.tel_ucstd"
													   @disabled($form->isReadonly)>
												<label class="form-check-label" for="checkboxUcStandard">Softphone / PC-Applikation</label>
											</div>
											<div class="form-check">
												<input class="form-check-input" type="checkbox" id="checkboxAlarmierung"
													   wire:model="form.tel_alarmierung"
														:disabled="{{ $form->isReadonly ? 'true' : '!mobiltelefon' }}">
												<label class="form-check-label" for="checkboxAlarmierung">Alarmierung</label>
											</div>
										</div>
									</div>

									<div>
										<label class="form-label" for="inputHeadset">Headset</label>
										<div class="input-group">
											<span class="input-group-text"><i class="mdi mdi-headset"></i></span>
												<select class="form-select" id="inputHeadset"
													wire:model="form.tel_headset"
													x-model="headset"
													x-init="headset = $wire.form.tel_headset"
													:disabled="$wire.form.isReadonly || headsetDisabled">
													<option value="">Bitte auswählen</option>
													<option value="mono">Mono</option>
													<option value="stereo">Stereo</option>
												</select>
										</div>
									</div>

								</div>
							</div>

                            <div x-data x-show="$wire.form.raumbeschriftung_flag" x-cloak>
                                <label class="form-label" for="inputRaumbeschriftung">Raumbeschriftung</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="mdi mdi-pencil"></i></span>
                                    <input type="text" id="inputRaumbeschriftung" class="form-control"
                                           wire:model="form.raumbeschriftung" @disabled($form->isReadonly)>
                                </div>
                            </div>

                            <div x-data x-show="$wire.form.key_waldhaus" x-cloak>
                                <label class="form-label">Schlüsselrecht Klinik Waldhaus</label>
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" wire:model="form.key_wh_badge" @disabled($form->isReadonly)>
                                    <label class="form-check-label">Badge</label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" wire:model="form.key_wh_schluessel" @disabled($form->isReadonly)>
                                    <label class="form-check-label">Schlüssel</label>
                                </div>
                            </div>

                            <div x-data x-show="$wire.form.key_beverin" x-cloak>
                                <label class="form-label">Schlüsselrecht Klinik Beverin</label>
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" wire:model="form.key_be_badge" @disabled($form->isReadonly)>
                                    <label class="form-check-label">Badge</label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" wire:model="form.key_be_schluessel" @disabled($form->isReadonly)>
                                    <label class="form-check-label">Schlüssel</label>
                                </div>
                            </div>

                            <div x-data x-show="$wire.form.key_rothenbr" x-cloak>
                                <label class="form-label">Schlüsselrecht Rothenbrunnen</label>
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" wire:model="form.key_rb_badge" @disabled($form->isReadonly)>
                                    <label class="form-check-label">Badge</label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" wire:model="form.key_rb_schluessel" @disabled($form->isReadonly)>
                                    <label class="form-check-label">Schlüssel</label>
                                </div>
                            </div>

							<div x-data x-show="$wire.form.vorab_lizenzierung" x-cloak wire:ignore>
								<label class="form-label" for="kalender_berechtigungen">Berechtigungen Kalender</label>
								<select id="kalender_berechtigungen" 
										class="select2 form-control select2-multiple" 
										multiple="multiple" 
										@disabled($form->isReadonly)>
								</select>
								<small class="d-block mt-1 lh-sm">Wähle Personen aus, die bereits vor dem Eintritt des neuen Mitarbeitenden Zugriff auf dessen Kalender erhalten sollen.</small>
							</div>

                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-12 d-block d-md-none order-3 mt-0">
            @if ($errors->any())
                <div class="alert alert-danger mb-3">
                    <ul class="mb-0 ps-2">
                        @foreach ($errors->all() as $error)
                            <li class="mb-0">{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <button type="submit" class="btn btn-primary mb-3" wire:loading.attr="disabled" wire:target="save">
                <span wire:loading.remove wire:target="save">Speichern</span>
                <span wire:loading wire:target="save">
                    <span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>
                    Bitte warten
                </span>
            </button>
        </div>
    </div>
</form>



@push('scripts')
<script>
	function initSelect2(selector) {
		$(selector).each(function () {
			const $s = $(this);
			
			if (!$s.data('select2')) 
			{
				$s.select2({
					placeholder: 'Bitte auswählen',
					width: '100%'
				});
			}
			$s.off('change.select2-to-livewire').on('change.select2-to-livewire', function () {
				const id = $(this).attr('id');
				let val = $(this).val();
				if ($(this).prop('multiple')) 
				{
					@this.set('form.' + id, val || []);
				}
				else 
				{
					const parsed = val ? (isNaN(val) ? val : parseInt(val, 10)) : null;
					@this.set('form.' + id, parsed);
				}
			});
		});
	}

    Livewire.on('select2-options', (payload) => {
        const id = payload.id;
        const options = payload.options || [];
        const value = payload.value ?? null;
        const $select = $('#' + id);
		
        if (!$select.length) return;

        $select.empty().append('<option></option>');
		
        options.forEach(opt => {
            if (opt.display_name) 
			{
                $select.append(new Option(opt.display_name, opt.id, false, false));
            } 
			else 
			{
                $select.append(new Option(opt.name, opt.id, false, false));
            }
        });

		initSelect2($select);

		if (value !== null) 
		{
			$select.val(value).trigger('change.select2');
		}
    });

	function initCheckboxes(selector) {
		$(selector).each(function () {
			const $c = $(this);
			
			if (!$c.data('bound')) 
			{
				$c.data('bound', true);

				$c.on('change', function () {
					const id = $(this).attr('id');
					const checked = $(this).is(':checked');

					@this.set('form.' + id, checked);
				});
			}
		});
	}



	function telefonieHandler() {
		return {
			auswahl: @entangle('form.tel_auswahl').live,
			tischtelefon: @entangle('form.tel_tischtel').live,
			mobiltelefon: @entangle('form.tel_mobiltel').live,
			ucstandard: @entangle('form.tel_ucstd').live,
			alarmierung: @entangle('form.tel_alarmierung').live,
			headset: @entangle('form.tel_headset').live,

			nummerDisabled: false,
			headsetDisabled: true,

			init() {
				if (@this.form.isReadonly) 
				{
					this.nummerDisabled = true;
					this.headsetDisabled = true;
					return;
				}
				this.updateHeadset();
				this.updateAlarmierung();
			},

			applyState() {
				this.updateHeadset();
				this.updateAlarmierung();
			},

			updateHeadset() {
				if (@this.form.isReadonly) return;
				this.headsetDisabled = !(this.tischtelefon || this.ucstandard);
			},

			updateAlarmierung() {
				if (!this.mobiltelefon) {
					this.alarmierung = false;
				}
			}
		}
	}

	document.addEventListener('livewire:load', () => {
		initSelect2('.select2, .select2-multiple');
		initCheckboxes('input[type=checkbox]');
	});

	document.addEventListener('livewire:navigated', () => {
		initCheckboxes('input[type=checkbox]');
	});

	Livewire.hook('message.processed', () => {
		initSelect2('.select2, .select2-multiple');
		initCheckboxes('input[type=checkbox]');
	});

	document.addEventListener("DOMContentLoaded", () => {
		const tooltipTriggerList = document.querySelectorAll('[data-bs-toggle="tooltip"]')
		tooltipTriggerList.forEach(el => {
			new bootstrap.Tooltip(el)
		})
	})

</script>
@endpush

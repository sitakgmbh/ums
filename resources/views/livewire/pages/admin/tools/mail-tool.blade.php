<div id="mail-tool-root" wire:id="{{ $this->getId() }}">
    <div class="card">
        <div class="card-body">

            <div class="row">
                <div class="col-md-6" wire:ignore>
                    <label class="form-label">E-Mail</label>
                    <select id="mailable" class="form-select select2">
                        <option value="">Bitte auswählen</option>
                        @foreach($mailables as $group)
                            <optgroup label="{{ $group['label'] }}">
                                @foreach($group['items'] as $key => $item)
                                    <option
                                        value="{{ strtolower($key) }}"
                                        data-requires-model="{{ $item['model'] ? '1' : '0' }}"
                                        data-model-type="{{ $item['modelType'] ?? '' }}"
                                    >
                                        {{ $item['label'] }}
                                    </option>
                                @endforeach
                            </optgroup>
                        @endforeach
                    </select>
					<label class="form-text">Wähle die E-Mail aus, die du verwenden möchtest.</label>
                </div>

                <div id="model-wrapper" class="col-md-6" wire:ignore style="display:none;">
                    <label class="form-label">Datensatz</label>
                    <select id="model" class="form-select select2-model">
                        <option value="">Bitte auswählen</option>
                    </select>
					<label class="form-text">Wähle einen Antrag aus, dessen Angaben du verwenden möchtest.</label>
                </div>
            </div>

            <div class="row mt-3" id="testmail-section" @if(!$this->showTestSection) style="display:none;" @endif>
                <div class="col-md-6">
                    <label class="form-label">Empfänger</label>
                    <input type="email" wire:model="recipient" class="form-control" placeholder="test@example.ch">
                </div>

                <div class="col-md-6 d-flex align-items-end gap-2">
                    
					<button
						class="btn btn-primary"
						wire:click="send"
						wire:loading.attr="disabled"
						wire:target="send"
					>
						<span wire:loading.remove wire:target="send">E-Mail senden</span>
						<span wire:loading wire:target="send">Wird gesendet…</span>
					</button>
					
					@if($this->previewUrl)
						<a href="{{ $this->previewUrl }}" target="_blank" class="btn btn-secondary">
							Vorschau anzeigen
						</a>
					@endif

                </div>
            </div>

			@if($errors->any())
				<div class="alert alert-danger mt-3 mb-0">
					<p>Etwas hat nicht funktioniert:</p>
					<ul class="mb-0">
						@foreach($errors->all() as $error)
							<li>{{ $error }}</li>
						@endforeach
					</ul>
				</div>
			@endif

			@if($status)
				<div class="alert alert-{{ $statusType }} mt-3 mb-0">
					{{ $status }}
				</div>
			@endif

        </div>
    </div>
</div>

@push('scripts')
<script>
    let componentId = null;

    document.addEventListener('livewire:initialized', () => {
        const el = document.querySelector('#mail-tool-root');
        if (el) {
            componentId = el.getAttribute('wire:id');
        }
    });

    const models = @json($models);
    const modelWrapper = $('#model-wrapper');

    function updateModelDropdown(modelType) {
        const modelSelect = $('#model');
        modelSelect.empty().append('<option value="">Bitte auswählen</option>');

        if (!models[modelType]) {
            modelWrapper.hide();
            return;
        }

        models[modelType].forEach(item => {
            modelSelect.append(new Option(item.label, item.id));
        });

        if (!modelSelect.data('select2')) {
            modelSelect.select2({
                placeholder: 'Bitte auswählen',
                width: '100%',
                allowClear: true
            });

            modelSelect.on('change.select2', function () {
                if (componentId) {
                    Livewire.find(componentId).set('selectedModelId', $(this).val());
                }
            });

        } else {
            modelSelect.val('').trigger('change.select2');
        }

        modelWrapper.show();
    }

    $(document).ready(function () {

        $('#mailable').select2({
            placeholder: 'Bitte auswählen',
            width: '100%',
            allowClear: false
        }).on('change.select2', function () {
            const value = $(this).val();

            if (componentId) {
                Livewire.find(componentId).set('selectedMailable', value);
            }

            const option = document.querySelector(`#mailable option[value="${value}"]`);
            const requiresModel = option?.dataset?.requiresModel === '1';
            const modelType = option?.dataset?.modelType;

            if (requiresModel && modelType) {
                updateModelDropdown(modelType);
            } else {
                modelWrapper.hide();
                $('#model').empty();
            }
        });
    });

    document.addEventListener('livewire:update', () => {
        // Select2 re-init falls Model-Dropdown neu gerendert wurde
        if ($('#model').length && !$('#model').data('select2')) {
            $('#model').select2({
                placeholder: 'Bitte auswählen',
                width: '100%',
                allowClear: true
            }).on('change.select2', function () {
                if (componentId) {
                    Livewire.find(componentId).set('selectedModelId', $(this).val());
                }
            });
        }
    });
</script>
@endpush

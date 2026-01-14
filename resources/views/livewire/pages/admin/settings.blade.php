<div>
    @if (session()->has('success'))
        <div class="alert alert-success mb-3">{{ session('success') }}</div>
    @endif

    <form wire:submit.prevent="save" autocomplete="off" class="d-flex flex-column gap-1">
        @foreach ($settings as $group => $items)
            <div class="card">
                <div class="card-header text-white bg-primary py-1">
                    <p class="mb-0"><strong>{{ $group }}</strong></p>
                </div>
                <div class="card-body d-flex flex-column gap-2">
                    @foreach ($items as $index => $s)
                        @if ($s['type'] === 'bool')
                            <div class="form-check">
                                <input type="checkbox" id="setting_{{ $s['key'] }}"
                                       class="form-check-input"
                                       wire:model.defer="settings.{{ $group }}.{{ $index }}.value">
                                <label for="setting_{{ $s['key'] }}" class="form-check-label">
                                    {{ $s['name'] }}
                                </label>
                                <small class="form-text text-muted d-block">
                                    {{ $s['description'] }}
                                </small>
                            </div>
						@elseif ($s['type'] === 'enum')
							<div>
								<label for="setting_{{ $s['key'] }}" class="form-label">{{ $s['name'] }}</label>
								<select id="setting_{{ $s['key'] }}"
										class="form-select"
										wire:model.defer="settings.{{ $group }}.{{ $index }}.value">

									<option value="off">Aus</option>
									<option value="admin">Nur Admins</option>
									<option value="all">Alle Benutzer</option>

								</select>
								<small class="form-text text-muted">{{ $s['description'] }}</small>
							</div>
                        @elseif ($s['type'] === 'password')
                            <div>
                                <label for="setting_{{ $s['key'] }}" class="form-label">{{ $s['name'] }}</label>
                                <div class="input-group input-group-merge">
                                    <input type="password" id="setting_{{ $s['key'] }}"
                                           class="form-control"
                                           wire:model.defer="settings.{{ $group }}.{{ $index }}.value"
                                           autocomplete="new-password">
                                    <div class="input-group-text" data-password="false">
                                        <span class="password-eye"></span>
                                    </div>
                                </div>
                                <small class="form-text text-muted">{{ $s['description'] }}</small>
                            </div>
                        @else
                            <div>
                                <label for="setting_{{ $s['key'] }}" class="form-label">{{ $s['name'] }}</label>
                                <input type="text" id="setting_{{ $s['key'] }}"
                                       class="form-control"
                                       wire:model.defer="settings.{{ $group }}.{{ $index }}.value">
                                <small class="form-text text-muted">{{ $s['description'] }}</small>
                            </div>
                        @endif
                    @endforeach
                </div>
            </div>
        @endforeach

        <div class="mb-3"><x-action-button action="save" class="btn-primary">Speichern </x-action-button></div>
    </form>
</div>

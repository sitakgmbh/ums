@extends('livewire.components.modals.base-modal')

@section('body')
    <div>
        {{-- Suche Benutzername --}}
        <div class="row mb-3">
            <div class="col-md-10">
                <label for="username" class="form-label">Bitte Kürzel eingeben:</label>
                <input 
                    type="text" 
                    class="form-control @error('username') is-invalid @enderror" 
                    id="username"
                    wire:model="username"
                    placeholder="Benutzerkürzel"
                    @if($userFound) readonly @endif
                >
                @error('username')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>
            <div class="col-md-2 d-flex align-items-end">
                <button 
                    type="button" 
                    class="btn btn-dark w-100" 
                    wire:click="searchUser"
                    wire:loading.attr="disabled"
                    @if($userFound) disabled @endif
                >
                    <span wire:loading.remove wire:target="searchUser">Suchen</span>
                    <span wire:loading wire:target="searchUser">
                        <span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Bitte warten...</span>
                </button>
            </div>
        </div>

        {{-- Error Message --}}
        @if($errorMessage)
            <div class="alert alert-danger mb-0" role="alert">
                {!! $errorMessage !!}
            </div>
        @endif

        {{-- Success Message --}}
        @if($successMessage)
            <div class="alert alert-success" role="alert">
                {!! $successMessage !!}
            </div>
        @endif

        {{-- User Details (nur anzeigen wenn gefunden) --}}
        @if($userFound && $employeeDetails)
            <hr>

            <div class="row">
                {{-- Mitarbeiterdaten --}}
                <div class="col-md-6">
                    <h4 class="mt-1">Details</h4>
                    <div class="mb-2">
                        <label class="form-label">Anrede</label>
                        <input 
                            type="text" 
                            class="form-control" 
                            value="{{ $employeeDetails['salutation']['longname'] ?? '-' }}" 
                            readonly
                        >
                    </div>
                    <div class="mb-2">
                        <label class="form-label">Vorname</label>
                        <input 
                            type="text" 
                            class="form-control" 
                            value="{{ $employeeDetails['firstname'] ?? '-' }}" 
                            readonly
                        >
                    </div>
                    <div class="mb-2">
                        <label class="form-label">Nachname</label>
                        <input 
                            type="text" 
                            class="form-control" 
                            value="{{ $employeeDetails['surname'] ?? '-' }}" 
                            readonly
                        >
                    </div>
                    <div class="mb-2">
                        <label class="form-label">Funktion</label>
                        <input 
                            type="text" 
                            class="form-control" 
                            value="{{ $employeeDetails['state']['longname'] ?? '-' }}" 
                            readonly
                        >
                    </div>
					@if($funktionAbweichend)
						<div class="alert alert-warning mt-2 mb-0">Wert weicht von Funktion in Antrag ab.</div>
					@endif
                </div>

                {{-- Organisation --}}
                <div class="col-md-6">
                    <h4 class="mt-1">Organisation</h4>
                    
                    {{-- Organisationseinheiten --}}
                    <div class="mb-2">
                        <label class="form-label">Organisationseinheiten</label>
                        <div class="border rounded p-2 bg-light-subtle" style="min-height: 2em;">
                            @php
                                $orgUnits = $employeeDetails['organizationalunits'] ?? [];
                            @endphp
                            
                            @if(empty($orgUnits))
                                <span class="text-muted">Keine Organisationseinheiten gefunden.<br>Indirekte Einträge werden nicht angezeigt.</span>
                            @else
                                @foreach($orgUnits as $unit)
                                    <div class="form-check">
                                        <input 
                                            class="form-check-input" 
                                            type="checkbox" 
                                            wire:model="selectedOrgUnits"
                                            value="{{ $unit['id'] }}" 
                                            id="orgUnit{{ $unit['id'] }}"
                                        >
                                        <label class="form-check-label" for="orgUnit{{ $unit['id'] }}">
                                            {{ $unit['name'] }} ({{ $unit['shortname'] }})
                                            @if(isset($unit['rank']['code']))
                                                <br><small class="text-muted">{{ $unit['rank']['code'] }}</small>
                                            @endif
                                        </label>
                                    </div>
                                @endforeach
                            @endif
                        </div>
                    </div>
                    
                    {{-- Organisationseinheitengruppen --}}
                    <div class="mb-2">
                        <label class="form-label">Organisationseinheitengruppen</label>
                        <div class="border rounded p-2 bg-light-subtle" style="min-height: 2em;">
                            @php
                                $orgGroups = $employeeDetails['organizationalunitgroups'] ?? [];
                            @endphp
                            
                            @if(empty($orgGroups))
                                <span class="text-muted">Keine Organisationseinheitengruppen zugewiesen.</span>
                            @else
                                @foreach($orgGroups as $group)
                                    <div class="form-check">
                                        <input 
                                            class="form-check-input" 
                                            type="checkbox" 
                                            wire:model="selectedOrgGroups"
                                            value="{{ $group['id'] }}" 
                                            id="orgGroup{{ $group['id'] }}"
                                        >
                                        <label class="form-check-label" for="orgGroup{{ $group['id'] }}">
                                            {{ $group['name'] ?? 'Kein Name' }} ({{ $group['shortname'] ?? 'Ohne Kurzname' }})
                                        </label>
                                    </div>
                                @endforeach
                            @endif
                        </div>
                    </div>

                    {{-- Optionen --}}
                    <div class="row">
                        <div class="col-md-{{ $modalType === 'mutation' ? '6' : '12' }}">
                            <label class="form-label">Mitarbeiterfunktion</label>
                            <select 
                                class="form-select @error('employeeFunction') is-invalid @enderror" 
                                wire:model="employeeFunction"
                            >
                                <option value="">Keine Funktion</option>
                                <option value="34">Leistungserbringer</option>
                                <option value="74">Pflege</option>
                            </select>
                            @error('employeeFunction')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        @if($modalType === 'mutation')
                            <div class="col-md-6">
                                <label class="form-label">OE und Rollen</label>
                                <select 
                                    class="form-select @error('permissionMode') is-invalid @enderror" 
                                    wire:model="permissionMode"
                                >
                                    <option value="merge">Ergänzen</option>
                                    <option value="replace">Ersetzen</option>
                                </select>
                                @error('permissionMode')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            <hr>

            {{-- Benutzer --}}
            <h4 class="mt-3">Benutzer</h4>
            <div id="userListContainer">
                @php
                    $users = $employeeDetails['users'] ?? [];
                @endphp
                
                @foreach($users as $index => $user)
                    @php
                        $isLast = $index === count($users) - 1;
                        $cardClass = 'border rounded p-3 bg-light-subtle ' . ($isLast ? 'mb-0' : 'mb-2');
                    @endphp
                    
                    <div class="{{ $cardClass }}">
                        <div class="form-check mb-2">
                            <input 
                                class="form-check-input" 
                                type="radio" 
                                wire:model="selectedUserId"
                                value="{{ $user['id'] }}" 
                                id="selectUser{{ $user['id'] }}"
                            >
                            <label class="form-check-label fw-bold" for="selectUser{{ $user['id'] }}">
                                {{ $user['username'] }}
                            </label>
                        </div>
                        
                        @if($selectedUserId === $user['id'])
                            <div class="mb-0">
                                <label class="form-label">Zugewiesene Rollen:</label><br>
                                @php
                                    $roles = $user['roles'] ?? [];
                                @endphp
                                
                                @if(empty($roles))
                                    <span class="text-muted">Keine Rollen zugewiesen.</span>
                                @else
                                    @foreach($roles as $role)
                                        <div class="form-check form-check-inline">
                                            <input 
                                                class="form-check-input" 
                                                type="checkbox" 
                                                wire:model="selectedRoles"
                                                value="{{ $role['id'] }}" 
                                                id="role_{{ $user['id'] }}_{{ $role['id'] }}"
                                            >
                                            <label class="form-check-label" for="role_{{ $user['id'] }}_{{ $role['id'] }}">
                                                {{ $role['name'] }}
                                            </label>
                                        </div>
                                    @endforeach
                                @endif
                            </div>
                        @endif
                    </div>
                @endforeach

				<div class="alert alert-info mt-2 mb-0 d-flex align-items-center gap-2" role="alert">
					<i class="ri-information-line fs-5"></i><span>Bitte TVER beachten!</span>
				</div>

            </div>
        @endif
    </div>
@endsection

@section('footer')
    <button type="button" class="btn btn-secondary" wire:click="closeModal">
        {{ $userFound ? 'Abbrechen' : 'Schliessen' }}
    </button>

	<button
		type="button"
		class="btn btn-success me-1"
		wire:click="markAsComplete"
		wire:loading.attr="disabled"
	>
		<span wire:loading.remove wire:target="markAsComplete">Als erledigt markieren</span>
		<span wire:loading wire:target="markAsComplete">
			<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>
		</span>
	</button>
    
    @if($userFound)
        <button
            type="button"
            class="btn btn-primary"
            wire:click="submitUser"
            wire:loading.attr="disabled"
			@disabled(!$selectedUserId || $disableSubmit)
        >
            <span wire:loading.remove wire:target="submitUser">
                {{ $modalType === 'mutation' ? 'Aktualisieren' : 'Erstellen' }}
            </span>
            <span wire:loading wire:target="submitUser">
                <span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>
                Bitte warten...
            </span>
        </button>
    @endif
@endsection

@push('scripts')
<script>
    window.addEventListener('confirm-no-function', event => {
        if (confirm('Es wurde keine Mitarbeiterfunktion ausgewählt.\nMöchtest du den Benutzer wirklich ohne Funktion erstellen?')) 
		{
            @this.call('confirmSubmitWithoutFunction');
        }
    });
    
    window.addEventListener('close-modal-delayed', event => {
        setTimeout(() => {
            @this.call('closeModal');
        }, 500);
    });
</script>
@endpush
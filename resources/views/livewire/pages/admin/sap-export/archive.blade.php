<div>

	<div class="card mb-3">
		<div class="card-body">

			<div class="row g-2 align-items-center flex-wrap">

				<!-- Dropdown -->
				<div class="col-12 col-sm-auto">
					<select wire:model="selectedVersion"
							class="form-select form-select-sm">
						<option value="">Version auswählen…</option>
						@foreach($versions as $v)
							<option value="{{ $v }}">{{ $v }}</option>
						@endforeach
					</select>
				</div>

				<!-- Laden -->
				<div class="col-12 col-sm-auto">
					<button class="btn btn-primary btn-sm w-100"
							wire:click="loadVersion"
							wire:loading.attr="disabled"
							wire:target="loadVersion">
						<span wire:loading.remove wire:target="loadVersion">Laden</span>
						<span wire:loading wire:target="loadVersion">Bitte warten…</span>
					</button>
				</div>

				@if(!empty($rows))

					<!-- Export -->
					<div class="col-12 col-sm-auto">
						<button class="btn btn-secondary btn-sm w-100"
								wire:click="export"
								wire:loading.attr="disabled">
							Exportieren
						</button>
					</div>

					<!-- Suche -->
					<div class="col-12 col-sm-auto d-flex align-items-center gap-2">

						<input id="archive-search"
							   type="text"
							   class="form-control form-control-sm"
							   placeholder="Suchbegriff…">

						<button type="button"
								id="archive-search-btn"
								data-search
								class="btn btn-sm btn-primary">
							Suchen
						</button>

						<button type="button"
								id="archive-reset-btn"
								data-reset
								class="btn btn-sm btn-light">
							Reset
						</button>
					</div>


				@endif

			</div>

			@if(!empty($rows))

				<div class="table-responsive mt-3" style="max-height: 60vh; overflow-y:auto;">
					<table id="archive-table" class="table table-sm table-centered table-hover table-nowrap mb-0">
						<thead class="table-light">
							<tr>
								@foreach($visibleColumns as $col)
									<th style="cursor:pointer;">{{ $col }}</th>
								@endforeach
							</tr>
						</thead>
						<tbody>
							@foreach($rows as $r)
								<tr>
									@foreach($visibleColumns as $col)
										<td>{{ $r[$col] ?? '' }}</td>
									@endforeach
								</tr>
							@endforeach
						</tbody>
					</table>
				</div>

				<div id="archive-count" class="text-muted small mt-2">
					{{ count($rows) }} Einträge
				</div>


			@endif

			@if($selectedVersion && empty($rows))
				<div class="alert alert-info mt-3 mb-3">Keine Daten gefunden.</div>
			@endif

		</div>
	</div>

</div>


@push('scripts')
<script>
(function() {

    function normalize(s) {
        return s.toLowerCase()
            .normalize('NFD')
            .replace(/[\u0300-\u036f]/g, '')
            .replace(/ß/g, 'ss');
    }

    function updateCount() {
        const all = document.querySelectorAll('#archive-table tbody tr').length;
        const visible = Array.from(document.querySelectorAll('#archive-table tbody tr'))
            .filter(r => r.style.display !== 'none').length;

        const el = document.getElementById('archive-count');
        if (!el) return;

        if (visible === all) {
            el.textContent = `${all} Einträge`;
        } else {
            el.textContent = `${visible} / ${all} Einträge`;
        }
    }

    function runSearch() {
        const input = document.getElementById('archive-search');
        if (!input) return;

        const term = normalize(input.value);
        const rows = document.querySelectorAll('#archive-table tbody tr');

        rows.forEach(row => {
            const text = normalize(row.innerText);
            row.style.display = text.includes(term) ? '' : 'none';
        });

        updateCount();
    }

    function resetSearch() {
        const input = document.getElementById('archive-search');
        if (!input) return;

        input.value = '';
        document.querySelectorAll('#archive-table tbody tr')
            .forEach(r => r.style.display = '');

        updateCount();
    }

    // Delegierte Buttons
    document.addEventListener('click', (e) => {
        if (e.target.closest('[data-search]')) {
            runSearch();
        }
        if (e.target.closest('[data-reset]')) {
            resetSearch();
        }
    });

    // Enter im Feld
    document.addEventListener('keydown', (e) => {
        if (e.key !== 'Enter') return;
        if (document.activeElement?.id === 'archive-search') {
            runSearch();
        }
    });

    // Sortieren
    document.addEventListener('click', (e) => {
        const th = e.target.closest('#archive-table thead th');
        if (!th) return;

        const table = th.closest('table');
        const index = Array.from(th.parentNode.children).indexOf(th);
        const asc = !(th.dataset.asc === "true");
        th.dataset.asc = asc;

        sortTable(table, index, asc);
        updateCount();
    });

    function sortTable(table, colIndex, asc) {
        const tbody = table.tBodies[0];
        const rows = Array.from(tbody.querySelectorAll('tr'));

        rows.sort((a, b) => {
            const x = a.children[colIndex].innerText.toLowerCase();
            const y = b.children[colIndex].innerText.toLowerCase();

            if (!isNaN(x) && !isNaN(y)) {
                return asc ? x - y : y - x;
            }
            return asc ? x.localeCompare(y) : y.localeCompare(x);
        });

        rows.forEach(r => tbody.appendChild(r));
    }

})();
</script>
@endpush

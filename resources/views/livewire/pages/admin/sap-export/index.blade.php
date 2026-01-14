<div>
	@section("pageActions")
		<a href="{{ route('admin.sap-export.archive') }}" class="btn btn-primary" title="SAP Export Archiv"><i class="mdi mdi-file-clock"></i></a>
	@endsection
	
    <livewire:components.tables.sap-export-table />
</div>

@php
    $pageHelpKey = request()->route()?->getName();
    $helpFile = $pageHelpKey ? resource_path("help/{$pageHelpKey}.md") : null;
    $hasHelp = $helpFile && file_exists($helpFile);
@endphp

<div>
	<!-- Footer Start -->
	<footer class="footer">
		<div class="container-fluid">
			<div class="row">
				<div class="col-md-6">
					<script>document.write(new Date().getFullYear())</script> © PDGR ICT
				</div>
				<div class="col-md-6">
					<div class="text-md-end footer-links d-none d-md-block">
						@php
							$mode = \App\Models\Setting::getValue('show_help', 'off');
							$routeName = request()->route()->getName();
							$user = auth()->user();

							$showHelp = match ($mode) {
								'all' => true,
								'admin' => $user && (
									(method_exists($user, 'isAdmin') && $user->isAdmin())
									|| (method_exists($user, 'hasRole') && $user->hasRole('admin'))
								),
								default => false
							};
						@endphp

						@if($showHelp && Route::has('help.viewer'))
							<a href="#"
							   onclick="window.open('{{ route('help.viewer', ['key' => $routeName]) }}',
													'help', 'width=900,height=700'); return false;">
								Hilfe
							</a>
						@endif
						
						<a href="#" data-bs-toggle="offcanvas" data-bs-target="#offcanvasHelp">Kontakt</a>
					</div>
				</div>
			</div>
		</div>
	</footer>
	<!-- end Footer -->

	<div class="offcanvas offcanvas-end" tabindex="-1" id="offcanvasHelp" aria-labelledby="offcanvasHelpLabel">
		<div class="offcanvas-header">
			<h3 id="offcanvasHelpLabel">Kontakt</h3>
			<button type="button" class="btn-close text-reset" data-bs-dismiss="offcanvas" aria-label="Close"></button>
		</div>
		<div class="offcanvas-body">
			<p class="mb-2">Bei Fragen oder Problemen wende dich bitte an:</p>
			<!-- Kontakt -->
			<div class="border rounded p-3 bg-light mt-1 shadow-sm">
				<div class="d-flex align-items-center">
					<span><strong>PDGR ICT Servicedesk</strong></span>
				</div>
				<div class="d-flex align-items-center">
					<a href="tel:+41582252799" class="text-decoration-none link-secondary">+41 58 225 27 99</a>
				</div>
				<div class="d-flex align-items-center">
					<a href="mailto:servicedesk@pdgr.ch" class="text-decoration-none link-secondary">servicedesk@pdgr.ch</a>
				</div>
			</div>
		</div>
	</div>
</div>
<div style="display:flex; flex-direction:column; width:100%; height:100%;">

    {{-- GLOBALER HEADER MIT SUCHE --}}
    <div style="background:#f1f3f5; padding:8px 12px; border-bottom:1px solid #d0d5d8;
                display:flex; justify-content:space-between; align-items:center;">

        <div style="font-size:16px; color:#1D4E8F; font-weight:bold;">UMS Hilfe</div>

        <form wire:submit.prevent="submitSearch"
              style="display:flex; gap:6px; align-items:center; margin:0;">

            <input type="text"
                   wire:model.defer="query"
                   wire:keydown.enter.prevent="submitSearch"
                   placeholder="Suchbegriff eingeben..."
                   style="padding:4px 6px; border:1px solid #ccc; border-radius:4px; font-size:12px;">

            <button type="submit"
                    style="padding:4px 10px; background:#1D4E8F; color:#fff; border:none; border-radius:4px;
                           font-size:12px;">
                Suchen
            </button>
        </form>
    </div>

    {{-- DARUNTER: SIDEBAR + CONTENT --}}
    <div style="flex:1; display:flex; min-height:0;">

{{-- SIDEBAR --}}
<div class="sidebar" id="help-sidebar">

@foreach($toc as $title => $group)

    @php
        $groupAllowed = !isset($group['roles']) || $this->hasRoleAccess($group['roles']);
    @endphp

    @continue(!$groupAllowed)

    @php
        $visibleItems = collect($group['items'])->filter(function($entry) {
            $roles = $entry['roles'] ?? null;
            return !$roles || $this->hasRoleAccess($roles);
        });
    @endphp

    @continue($visibleItems->isEmpty())

    <div class="toc-group">

        {{-- nur rendern wenn titel vorhanden --}}
		@if(!in_array($title, ['', '_', '_2']))
			<div class="toc-title">{{ $title }}</div>
		@endif



        {{-- items --}}
        @foreach($visibleItems as $entry)

            @php
                $label  = $entry['title'];
                $routes = $entry['routes'] ?? [];
                $page   = $entry['page'] ?? null;

                $allKeys = [...$routes, ...($page ? [$page] : [])];

                $isActive = in_array($key, $allKeys, true);

                if(!$isActive && request()->route()) {
                    $current = request()->route()->getName();
                    if($current && in_array($current, $allKeys, true)) {
                        $isActive = true;
                    }
                }

                if ($page) {
                    $target = 'page:' . $page;
                } elseif(!empty($routes)) {
                    $target = 'route:' . $routes[0];
                } else {
                    $target = null;
                }
            @endphp

            @if($target)
                <div class="toc-item {{ $isActive ? 'toc-item-active' : '' }}"
                     wire:click="goTo('{{ $target }}')">
                    {{ $label }}
                </div>
            @endif

        @endforeach

    </div>
@endforeach
</div>





        {{-- CONTENT --}}
        <div style="flex:1; display:flex; flex-direction:column; min-height:0;">

            {{-- FALL 1: Treffer --}}
            @if(strlen($query) >= 2 && !empty($results))

                <div style="padding:14px; overflow-y:auto;">
                    <h3 style="margin-top:0;">Suchresultate für '{{ $query }}'</h3>

                    @foreach($results as $res)
                        <div style="padding:10px; margin-bottom:12px;
                                    border:1px solid #ddd; border-radius:4px;
                                    background:#fafafa; cursor:pointer;"
                             wire:click="goTo('page:{{ $res['key'] }}')">

                            <strong>{{ $res['key'] }}</strong>
                            <p style="margin:4px 0;">{!! $res['excerpt'] !!}</p>
                        </div>
                    @endforeach
                </div>

            {{-- FALL 2: Keine Treffer --}}
            @elseif(strlen($query) >= 2 && empty($results))

                <div style="padding:14px;">
                    <p style="margin-top:0;">Keine Ergebnisse gefunden für '{{ $query }}'</p>
                </div>

            {{-- FALL 3: normale Anzeige --}}
            @else

                <div class="content">
                    {!! $html !!}
                </div>

            @endif

        </div>

    </div>
</div>

@can('viewMine', App\Models\Room::class)
    <nav class="page-submenu">
        {{-- Navigation items --}}
        <a href="{{ route('rooms.index', ['view' => 'available', 'display' => request('display', 'cards')]) }}"
           class="page-submenu-item page-submenu-nav {{ request()->routeIs('rooms.index') && ($view ?? 'available') === 'available' ? 'active' : '' }}">
            {{ __('Available rooms') }}
        </a>
        <a href="{{ route('rooms.index', ['view' => 'mine', 'display' => request('display', 'cards')]) }}"
           class="page-submenu-item page-submenu-nav {{ request()->routeIs('rooms.index') && ($view ?? '') === 'mine' ? 'active' : '' }}">
            {{ __('My rooms') }}
        </a>

        @can('create', App\Models\Room::class)
            {{-- Separator --}}
            <span class="page-submenu-separator"></span>

            {{-- Secondary navigation --}}
            <a href="{{ route('room-discounts.index') }}"
               class="page-submenu-item page-submenu-secondary {{ request()->routeIs('room-discounts.*') ? 'active' : '' }}">
                {{ __('Discounts') }}
            </a>
            <a href="{{ route('room-options.index') }}"
               class="page-submenu-item page-submenu-secondary {{ request()->routeIs('room-options.*') ? 'active' : '' }}">
                {{ __('Options') }}
            </a>
            <a href="{{ route('custom-fields.index') }}"
               class="page-submenu-item page-submenu-secondary {{ request()->routeIs('custom-fields.*') ? 'active' : '' }}">
                {{ __('Custom fields') }}
            </a>
            <a href="{{ route('room-unavailabilities.index') }}"
               class="page-submenu-item page-submenu-secondary {{ request()->routeIs('room-unavailabilities.*') ? 'active' : '' }}">
                {{ __('Unavailabilities') }}
            </a>

            {{-- Separator --}}
            <span class="page-submenu-separator"></span>

            {{-- Action buttons --}}
            @if(request()->routeIs('rooms.index'))
                <a href="{{ route('rooms.create') }}" class="page-submenu-item page-submenu-action">
                    + {{ __('New room') }}
                </a>
            @elseif(request()->routeIs('room-discounts.*'))
                <a href="{{ route('room-discounts.create') }}" class="page-submenu-item page-submenu-action">
                    + {{ __('Add discount') }}
                </a>
            @elseif(request()->routeIs('room-options.*'))
                <a href="{{ route('room-options.create') }}" class="page-submenu-item page-submenu-action">
                    + {{ __('Add option') }}
                </a>
            @elseif(request()->routeIs('custom-fields.*'))
                <a href="{{ route('custom-fields.create') }}" class="page-submenu-item page-submenu-action">
                    + {{ __('Add field') }}
                </a>
            @elseif(request()->routeIs('room-unavailabilities.*'))
                <a href="{{ route('room-unavailabilities.create') }}" class="page-submenu-item page-submenu-action">
                    + {{ __('Add unavailability') }}
                </a>
            @endif
        @endcan
    </nav>
@endcan

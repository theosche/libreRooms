@can('viewAdmin', App\Models\Room::class)
    <nav class="page-submenu">
        {{-- Navigation items --}}
        <a href="{{ route('rooms.index', ['view' => 'available', 'display' => request('display', 'cards'), ...request('owner_id') ? ['owner_id' => request('owner_id')] : []]) }}"
           class="page-submenu-item page-submenu-nav {{ request()->routeIs('rooms.index') && ($view ?? 'available') === 'available' ? 'active' : '' }}">
            {{ __('Available rooms') }}
        </a>
        <a href="{{ route('rooms.index', ['view' => 'mine', 'display' => request('display', 'cards'), ...request('owner_id') ? ['owner_id' => request('owner_id')] : []]) }}"
           class="page-submenu-item page-submenu-nav {{ request()->routeIs('rooms.index') && ($view ?? '') === 'mine' ? 'active' : '' }}">
            {{ __('My rooms') }}
        </a>

        @can('viewAnyDiscounts', App\Models\Room::class)
            {{-- Separator --}}
            <span class="page-submenu-separator"></span>

            {{-- Secondary navigation --}}
            <a href="{{ route('room-discounts.index', request('room_id') ? ['room_id' => request('room_id')] : []) }}"
               class="page-submenu-item page-submenu-secondary {{ request()->routeIs('room-discounts.*') ? 'active' : '' }}">
                {{ __('Discounts') }}
            </a>
            <a href="{{ route('room-options.index', request('room_id') ? ['room_id' => request('room_id')] : []) }}"
               class="page-submenu-item page-submenu-secondary {{ request()->routeIs('room-options.*') ? 'active' : '' }}">
                {{ __('Options') }}
            </a>
            <a href="{{ route('custom-fields.index', request('room_id') ? ['room_id' => request('room_id')] : []) }}"
               class="page-submenu-item page-submenu-secondary {{ request()->routeIs('custom-fields.*') ? 'active' : '' }}">
                {{ __('Custom fields') }}
            </a>
        @endcan
        @can('viewAnyUnavailabilities', App\Models\Room::class)
            @cannot('viewAnyDiscounts', App\Models\Room::class)
                <span class="page-submenu-separator"></span>
            @endcannot
            <a href="{{ route('room-unavailabilities.index', request('room_id') ? ['room_id' => request('room_id')] : []) }}"
               class="page-submenu-item page-submenu-secondary {{ request()->routeIs('room-unavailabilities.*') ? 'active' : '' }}">
                {{ __('Unavailabilities') }}
            </a>
        @endcan
        @can('create', App\Models\Room::class)
            {{-- Separator --}}
            <span class="page-submenu-separator"></span>

            {{-- Action buttons --}}
            @if(request()->routeIs('rooms.index'))
                <a href="{{ route('rooms.create') }}" class="page-submenu-item page-submenu-action">
                    + {{ __('New room') }}
                </a>
            @endif
        @endcan
        @can('viewAnyDiscounts', App\Models\Room::class)
            @if(request()->routeIs('room-discounts.*'))
                @cannot('create', App\Models\Room::class)
                    <span class="page-submenu-separator"></span>
                @endcannot
                <a href="{{ route('room-discounts.create',['room_id' => $currentRoomId]) }}" class="page-submenu-item page-submenu-action">
                    + {{ __('Add discount') }}
                </a>
            @elseif(request()->routeIs('room-options.*'))
                @cannot('create', App\Models\Room::class)
                    <span class="page-submenu-separator"></span>
                @endcannot
                <a href="{{ route('room-options.create',['room_id' => $currentRoomId]) }}" class="page-submenu-item page-submenu-action">
                    + {{ __('Add option') }}
                </a>
            @elseif(request()->routeIs('custom-fields.*'))
                @cannot('create', App\Models\Room::class)
                    <span class="page-submenu-separator"></span>
                @endcannot
                <a href="{{ route('custom-fields.create',['room_id' => $currentRoomId]) }}" class="page-submenu-item page-submenu-action">
                    + {{ __('Add field') }}
                </a>
            @endif
        @endcan
        @if(request()->routeIs('room-unavailabilities.*'))
            @can('viewAnyUnavailabilities', App\Models\Room::class)
                @cannot('create', App\Models\Room::class)
                    @cannot('viewAnyDiscounts', App\Models\Room::class)
                        <span class="page-submenu-separator"></span>
                    @endcannot
                @endcannot
                <a href="{{ route('room-unavailabilities.create',['room_id' => $currentRoomId]) }}" class="page-submenu-item page-submenu-action">
                    + {{ __('Add unavailability') }}
                </a>
            @endcan
        @endif
    </nav>
@endcan

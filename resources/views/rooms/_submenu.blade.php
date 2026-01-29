@can('viewMine', App\Models\Room::class)
    <div class="mt-4 flex gap-2 flex-wrap">
        <a href="{{ route('rooms.index', ['view' => 'available']) }}"
           class="px-4 py-2 rounded-md {{ request()->routeIs('rooms.index') && ($view ?? 'available') === 'available' ? 'bg-blue-600 text-white' : 'bg-gray-200 text-gray-700 hover:bg-gray-300' }}">
            Salles disponibles
        </a>
        <a href="{{ route('rooms.index', ['view' => 'mine']) }}"
           class="px-4 py-2 rounded-md {{ request()->routeIs('rooms.index') && ($view ?? '') === 'mine' ? 'bg-blue-600 text-white' : 'bg-gray-200 text-gray-700 hover:bg-gray-300' }}">
            Mes salles
        </a>
        @can('create', App\Models\Room::class)
            <!-- Separator -->
            <span class="border-l border-gray-300 mx-2"></span>

            <a href="{{ route('room-discounts.index') }}"
               class="px-4 py-2 rounded-md {{ request()->routeIs('room-discounts.*') ? 'bg-green-600 text-white' : 'bg-gray-200 text-gray-700 hover:bg-gray-300' }}">
                Réductions
            </a>
            <a href="{{ route('room-options.index') }}"
               class="px-4 py-2 rounded-md {{ request()->routeIs('room-options.*') ? 'bg-green-600 text-white' : 'bg-gray-200 text-gray-700 hover:bg-gray-300' }}">
                Options
            </a>
            <a href="{{ route('custom-fields.index') }}"
               class="px-4 py-2 rounded-md {{ request()->routeIs('custom-fields.*') ? 'bg-green-600 text-white' : 'bg-gray-200 text-gray-700 hover:bg-gray-300' }}">
                Champs personnalisés
            </a>
        @endcan
    </div>
@endcan

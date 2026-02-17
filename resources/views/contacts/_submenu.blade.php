@if($canViewAll)
    <nav class="page-submenu">
        <a href="{{ route('contacts.index', ['view' => 'mine']) }}"
           class="page-submenu-item page-submenu-nav {{ ($view ?? 'mine') === 'mine' ? 'active' : '' }}">
            {{ __('My contacts') }}
        </a>
        <a href="{{ route('contacts.index', ['view' => 'all']) }}"
           class="page-submenu-item page-submenu-nav {{ ($view ?? '') === 'all' ? 'active' : '' }}">
            {{ __('All contacts') }}
        </a>

        <span class="page-submenu-separator"></span>

        <a href="{{ route('contacts.create') }}" class="page-submenu-item page-submenu-action">
            + {{ __('New contact') }}
        </a>
    </nav>
@else
    <nav class="page-submenu">
        <a href="{{ route('contacts.create') }}" class="page-submenu-item page-submenu-action">
            + {{ __('New contact') }}
        </a>
    </nav>
@endif

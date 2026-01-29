<div class="mt-4 flex gap-2 flex-wrap">
    <a href="{{ route('system-settings.edit') }}"
       class="px-4 py-2 rounded-md {{ request()->routeIs('system-settings.edit') ? 'bg-green-600 text-white' : 'bg-gray-200 text-gray-700 hover:bg-gray-300' }}">
        Réglages généraux
    </a>
    <a href="{{ route('identity-providers.index') }}"
       class="px-4 py-2 rounded-md {{ request()->routeIs('identity-providers.*') ? 'bg-green-600 text-white' : 'bg-gray-200 text-gray-700 hover:bg-gray-300' }}">
        Fournisseurs d'identité
    </a>
    <a href="{{ route('setup.environment') }}"
       class="px-4 py-2 rounded-md {{ request()->routeIs('setup.environment') ? 'bg-green-600 text-white' : 'bg-gray-200 text-gray-700 hover:bg-gray-300' }}">
        Environnement (.env)
    </a>
</div>

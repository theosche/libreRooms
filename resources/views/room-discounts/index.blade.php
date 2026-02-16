@extends('layouts.app')

@section('title', __('Discounts'))

@section('content')
<div class="max-w-7xl mx-auto py-6">
    <div class="page-header">
        <h1 class="page-header-title">{{ __('Discounts') }}</h1>

        @include('rooms._submenu', ['view' => null, 'currentRoomId' => $currentRoomId])
    </div>

    <!-- Filtres -->
    <div class="bg-white p-6 rounded-lg shadow mb-6">
        <form method="GET" action="{{ route('room-discounts.index') }}" class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div>
                <label for="room_id" class="block text-sm font-medium text-gray-700 mb-1">{{ __('Room') }}</label>
                <select name="room_id" id="room_id" class="form-select">
                    <option value="">{{ __('All rooms') }}</option>
                    @foreach($rooms as $room)
                        <option value="{{ $room->id }}" {{ request('room_id') == $room->id ? 'selected' : '' }}>
                            {{ $room->name }} ({{ $room->owner->contact->display_name() }})
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="flex items-end gap-2 col-span-2">
                <button type="submit" class="btn btn-primary">
                    {{ __('Filter') }}
                </button>
                @if(request()->has('room_id'))
                    <a href="{{ route('room-discounts.index') }}" class="btn btn-secondary">
                        {{ __('Reset') }}
                    </a>
                @endif
            </div>
        </form>
    </div>

    <!-- Tableau des réductions -->
    <div class="bg-white rounded-lg shadow overflow-hidden">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th scope="col" class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        {{ __('Room') }}
                    </th>
                    <th scope="col" class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        {{ __('Name') }}
                    </th>
                    <th scope="col" class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider hide-on-mobile">
                        {{ __('Description') }}
                    </th>
                    <th scope="col" class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        {{ __('Contact type') }}
                    </th>
                    <th scope="col" class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        {{ __('Type / Value') }}
                    </th>
                    <th scope="col" class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        {{ __('Status') }}
                    </th>
                    <th scope="col" class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        {{ __('Actions') }}
                    </th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                @forelse($discounts as $discount)
                    <tr class="hover:bg-gray-50 transition">
                        <td class="px-4 py-3 text-sm text-gray-900">
                            <a href="{{ route('rooms.show', $discount->room) }}">
                                {{ $discount->room->name }}
                            </a>
                        </td>
                        <td class="px-4 py-3 text-sm font-medium text-gray-900">
                            {{ $discount->name }}
                        </td>
                        <td class="px-4 py-3 text-sm text-gray-700 hide-on-mobile">
                            {{ Str::limit($discount->description, 50) }}
                        </td>
                        <td class="px-4 py-3 text-sm text-gray-700">
                            <div class="flex items-center gap-2">
                                @if($discount->limit_to_contact_type)
                                    @if($discount->limit_to_contact_type->value === 'individual')
                                        <svg class="w-5 h-5 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24" title="Privé·e">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                                        </svg>
                                    @else
                                        <svg class="w-5 h-5 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24" title="Organisation">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                                        </svg>
                                    @endif
                                @else
                                    <svg class="w-5 h-5 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24" title="Privé·e">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                                    </svg>
                                    <svg class="w-5 h-5 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24" title="Organisation">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                                    </svg>
                                @endif
                            </div>
                        </td>
                        <td class="px-4 py-3 text-sm text-gray-900">
                            @if($discount->type->value === 'fixed')
                                {{ currency(-$discount->value, $discount->room->owner) }}
                            @else
                                -{{ $discount->value }}%
                            @endif
                        </td>
                        <td class="px-4 py-3">
                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full {{ $discount->active ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                                {{ $discount->active ? __('Active') : __('Inactive') }}
                            </span>
                        </td>
                        <td class="px-4 py-3 text-sm font-medium">
                            <div class="action-group">
                                <a href="{{ route('room-discounts.edit', [$discount] + redirect_back_params()) }}" class="link-primary">
                                    {{ __('Edit') }}
                                </a>

                                <form action="{{ route('room-discounts.destroy', [$discount] + redirect_back_params()) }}" method="POST" class="inline"
                                      onsubmit="return confirm('{{ __('Are you sure you want to delete this discount?') }}');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="link-danger">
                                        {{ __('Delete') }}
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="px-6 py-4 text-center text-gray-500">
                            {{ __('No discounts found') }}
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <!-- Pagination -->
    <div class="mt-6">
        {{ $discounts->links() }}
    </div>
</div>
@endsection

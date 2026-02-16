@extends('layouts.app')

@section('title', __('Unavailabilities'))

@section('content')
<div class="max-w-7xl mx-auto py-6">
    <div class="page-header">
        <h1 class="page-header-title">{{ __('Unavailabilities') }}</h1>

        @include('rooms._submenu', ['view' => null, 'currentRoomId' => $currentRoomId])
    </div>

    <!-- Filtres -->
    <div class="bg-white p-6 rounded-lg shadow mb-6">
        <form method="GET" action="{{ route('room-unavailabilities.index') }}" class="grid grid-cols-1 md:grid-cols-3 gap-4">
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
                    <a href="{{ route('room-unavailabilities.index') }}" class="btn btn-secondary">
                        {{ __('Reset') }}
                    </a>
                @endif
            </div>
        </form>
    </div>

    <!-- Tableau des indisponibilites -->
    <div class="bg-white rounded-lg shadow overflow-hidden">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th scope="col" class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        {{ __('Room') }}
                    </th>
                    <th scope="col" class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        {{ __('Title') }}
                    </th>
                    <th scope="col" class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        {{ __('Start') }}
                    </th>
                    <th scope="col" class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        {{ __('End') }}
                    </th>
                    <th scope="col" class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        {{ __('Actions') }}
                    </th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                @forelse($unavailabilities as $unavailability)
                    <tr class="hover:bg-gray-50 transition">
                        <td class="px-4 py-3 text-sm text-gray-900">
                            <a href="{{ route('rooms.show', $unavailability->room) }}">
                                {{ $unavailability->room->name }}
                            </a>
                        </td>
                        <td class="px-4 py-3 text-sm font-medium text-gray-900">
                            {{ $unavailability->title ?? '-' }}
                        </td>
                        <td class="px-4 py-3 text-sm text-gray-700">
                            {{ $unavailability->startLocalTz()->format('d.m.Y H:i') }}
                        </td>
                        <td class="px-4 py-3 text-sm text-gray-700">
                            {{ $unavailability->endLocalTz()->format('d.m.Y H:i') }}
                        </td>
                        <td class="px-4 py-3 text-sm font-medium">
                            <div class="action-group">
                                <a href="{{ route('room-unavailabilities.edit', [$unavailability] + redirect_back_params()) }}" class="link-primary" title="{{ __('Edit') }}">
                                    <x-action-icon action="edit" />
                                </a>

                                <form action="{{ route('room-unavailabilities.destroy', [$unavailability] + redirect_back_params()) }}" method="POST" class="inline"
                                      onsubmit="return confirm('{{ __('Are you sure you want to delete this unavailability?') }}');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="link-danger" title="{{ __('Delete') }}">
                                        <x-action-icon action="delete" />
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="px-6 py-4 text-center text-gray-500">
                            {{ __('No unavailabilities found') }}
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <!-- Pagination -->
    <div class="mt-6">
        {{ $unavailabilities->links() }}
    </div>
</div>
@endsection

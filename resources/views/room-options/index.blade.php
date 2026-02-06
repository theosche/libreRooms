@extends('layouts.app')

@section('title', __('Options'))

@section('content')
<div class="max-w-7xl mx-auto py-6">
    <div class="page-header">
        <h1 class="page-header-title">{{ __('Options') }}</h1>

        @include('rooms._submenu', ['view' => null, 'currentRoomId' => $currentRoomId])
    </div>

    <!-- Filtres -->
    <div class="bg-white p-6 rounded-lg shadow mb-6">
        <form method="GET" action="{{ route('room-options.index') }}" class="grid grid-cols-1 md:grid-cols-3 gap-4">
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
                    <a href="{{ route('room-options.index') }}" class="btn btn-secondary">
                        {{ __('Reset') }}
                    </a>
                @endif
            </div>
        </form>
    </div>

    <!-- Tableau des options -->
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
                        Description
                    </th>
                    <th scope="col" class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        {{ __('Price') }}
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
                @forelse($options as $option)
                    <tr class="hover:bg-gray-50 transition">
                        <td class="px-4 py-3 text-sm text-gray-900">
                            <a href="{{ route('rooms.show', $option->room) }}">
                                {{ $option->room->name }}
                            </a>
                        </td>
                        <td class="px-4 py-3 text-sm font-medium text-gray-900">
                            {{ $option->name }}
                        </td>
                        <td class="px-4 py-3 text-sm text-gray-700 hide-on-mobile">
                            {{ Str::limit($option->description, 50) }}
                        </td>
                        <td class="px-4 py-3 text-sm text-gray-900">
                            {{ currency($option->price, $option->room->owner) }}
                        </td>
                        <td class="px-4 py-3">
                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full {{ $option->active ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                                {{ $option->active ? __('Active') : __('Inactive') }}
                            </span>
                        </td>
                        <td class="px-4 py-3 text-sm font-medium">
                            <div class="action-group">
                                <a href="{{ route('room-options.edit', $option) }}" class="link-primary">
                                    {{ __('Edit') }}
                                </a>

                                <form action="{{ route('room-options.destroy', $option) }}" method="POST" class="inline"
                                      onsubmit="return confirm('{{ __('Are you sure you want to delete this option?') }}');">
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
                        <td colspan="6" class="px-6 py-4 text-center text-gray-500">
                            {{ __('No options found') }}
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <!-- Pagination -->
    <div class="mt-6">
        {{ $options->links() }}
    </div>
</div>
@endsection

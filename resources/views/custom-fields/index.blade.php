@extends('layouts.app')

@section('title', __('Custom fields'))

@section('content')
<div class="max-w-7xl mx-auto py-6">
    <div class="page-header">
        <h1 class="page-header-title">{{ __('Custom fields') }}</h1>

        @include('rooms._submenu', ['view' => null, 'currentRoomId' => $currentRoomId])
    </div>

    <!-- Filtres -->
    <div class="bg-white p-6 rounded-lg shadow mb-6">
        <form method="GET" action="{{ route('custom-fields.index') }}" class="grid grid-cols-1 md:grid-cols-3 gap-4">
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
                    <a href="{{ route('custom-fields.index') }}" class="btn btn-secondary">
                        {{ __('Reset') }}
                    </a>
                @endif
            </div>
        </form>
    </div>

    <!-- Tableau des champs personnalisés -->
    <div class="bg-white rounded-lg shadow overflow-hidden">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th scope="col" class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        {{ __('Room') }}
                    </th>
                    <th scope="col" class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        Label
                    </th>
                    <th scope="col" class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        {{ __('Type') }}
                    </th>
                    <th scope="col" class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        Options
                    </th>
                    <th scope="col" class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider hide-on-mobile">
                        {{ __('Required') }}
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
                @forelse($customFields as $field)
                    <tr class="hover:bg-gray-50 transition">
                        <td class="px-4 py-3 text-sm text-gray-900">
                            <a href="{{ route('rooms.show', $field->room) }}">
                                {{ $field->room->name }}
                            </a>
                        </td>
                        <td class="px-4 py-3 text-sm font-medium text-gray-900">
                            {{ $field->label }}
                        </td>
                        <td class="px-4 py-3 text-sm text-gray-700">
                            <span class="px-2 py-1 text-xs font-medium rounded-md bg-gray-100 text-gray-800">
                                {{ ucfirst($field->type->value) }}
                            </span>
                        </td>
                        <td class="px-4 py-3 text-sm text-gray-700">
                            @if($field->options && in_array($field->type->value, ['select', 'radio', 'checkbox']))
                                <span class="text-xs text-gray-600">
                                    {{ implode(', ', array_slice($field->options, 0, 3)) }}
                                    @if(count($field->options) > 3)
                                        <span class="text-gray-400">+{{ count($field->options) - 3 }}</span>
                                    @endif
                                </span>
                            @else
                                <span class="text-gray-400 text-xs">-</span>
                            @endif
                        </td>
                        <td class="px-4 py-3 text-center hide-on-mobile">
                            @if($field->required)
                                <svg class="w-4 h-4" viewBox="0 0 448 512"><!--!Font Awesome Free v7.1.0 by @fontawesome - https://fontawesome.com License - https://fontawesome.com/license/free Copyright 2026 Fonticons, Inc.--><path d="M434.8 70.1c14.3 10.4 17.5 30.4 7.1 44.7l-256 352c-5.5 7.6-14 12.3-23.4 13.1s-18.5-2.7-25.1-9.3l-128-128c-12.5-12.5-12.5-32.8 0-45.3s32.8-12.5 45.3 0l101.5 101.5 234-321.7c10.4-14.3 30.4-17.5 44.7-7.1z"/></svg>
                            @endif
                        </td>
                        <td class="px-4 py-3">
                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full {{ $field->active ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                                {{ $field->active ? __('Active') : __('Inactive') }}
                            </span>
                        </td>
                        <td class="px-4 py-3 text-sm font-medium">
                            <div class="action-group">
                                <a href="{{ route('custom-fields.edit', $field) }}" class="link-primary">
                                    {{ __('Edit') }}
                                </a>

                                <form action="{{ route('custom-fields.destroy', $field) }}" method="POST" class="inline"
                                      onsubmit="return confirm('{{ __('Are you sure you want to delete this custom field?') }}');">
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
                            {{ __('No custom fields found') }}
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <!-- Pagination -->
    <div class="mt-6">
        {{ $customFields->links() }}
    </div>
</div>
@endsection

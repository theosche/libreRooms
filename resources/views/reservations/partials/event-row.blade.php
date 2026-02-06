<div class="form-element event-row" data-event-id="{{ $event['id'] }}">
    <div class="form-element-row event-row-date">
        {{-- Remove --}}
        <button
            type="button"
            class="event-remove"
            id="event-remove-{{ $event['id'] }}"
            aria-label="{{ __('Remove this date') }}"
        >
            ✕
        </button>

        {{-- Start datetime --}}
        <div class="form-field">
            <input
                type="datetime-local"
                name="events[{{ $event['id'] }}][start]"
                value="{{ $event['start'] ?? '' }}"
                class="event-start"
                required
            >
        </div>

        {{-- End datetime --}}
        <div class="form-field">
            <input
                type="datetime-local"
                name="events[{{ $event['id'] }}][end]"
                value="{{ $event['end'] ?? '' }}"
                class="event-end"
                required
            >
        </div>

        {{-- Availability status --}}
        <span class="status-label" id="event-status-{{ $event['id'] }}"></span>

        <input
            type="hidden"
            name="events[{{ $event['id'] }}][uid]"
            value="{{ $event['uid'] ?? '' }}"
        >
    </div>

    @if(!$availableOptions->isEmpty())
    <div class="form-element-row event-row-options">
        @foreach($availableOptions as $opt)
            <div class="form-field option-field">
                <input
                    type="checkbox"
                    id="option_{{ $event['id'] . '_' . $opt->id }}"
                    name="events[{{ $event['id'] }}][options][]"
                    value="{{ $opt->id }}"
                    @checked(in_array($opt->id, $event['options'] ?? []))
                >
                <label for="option_{{ $event['id'] . '_' . $opt->id }}" class="tooltip-target" data-tooltip="{{ $opt->description }}">
                    {{ $opt->name }} ({{ currency($opt->price,$owner) }}) ⓘ
                </label>
            </div>
        @endforeach
    </div>
    @endif

    <div class="form-element-row event-row-info">
        {{-- Info text / price --}}
        <div class="form-field event-info">
            <span class="event-info-text"></span>
        </div>
    </div>
</div>

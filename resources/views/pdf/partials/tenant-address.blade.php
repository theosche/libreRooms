<div class="tenant-address">
    @if($tenant->entity_name)
        <div>{{ html_entity_decode($tenant->entity_name) }}</div>
    @endif
    <div>{{ $tenant->first_name }} {{ $tenant->last_name }}</div>
    <div>{!! nl2br($tenant->street) !!}</div>
    <div>{{ $tenant->zip }} {{ $tenant->city }}</div>
    <div><a href="mailto:{{ $tenant->email }}">{{ $tenant->email }}</a></div>
    @if($tenant->phone)
        <div>{{ $tenant->phone }}</div>
    @endif
</div>

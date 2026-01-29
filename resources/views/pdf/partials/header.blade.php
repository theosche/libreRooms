@php
    $ownerContact = $owner->contact;
@endphp
<div class="header">
    <div>{{ $ownerContact->display_name() }}</div>
    <div>{{ $ownerContact->street }}</div>
    <div>{{ $ownerContact->zip }} {{ $ownerContact->city }}</div>
    <div><a href="mailto:{{ $ownerContact->email }}">{{ $ownerContact->email }}</a></div>
</div>

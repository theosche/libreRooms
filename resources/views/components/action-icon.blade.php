@props(['action'])

@php
    $icons = [
        'edit' => 'fa-solid fa-pen-to-square',
        'delete' => 'fa-solid fa-trash-can',
        'remove' => 'fa-solid fa-trash-can',
        'share' => 'fa-solid fa-share-nodes',
        'reminder' => 'fa-solid fa-bell',
        'paid' => 'fa-solid fa-circle-check',
        'cancel' => 'fa-solid fa-ban',
        'recreate' => 'fa-solid fa-rotate',
        'pdf' => 'fa-solid fa-file-pdf',
        'users' => 'fa-solid fa-users',
        'review' => 'fa-solid fa-clipboard-check',
        'view' => 'fa-solid fa-eye',
        'reservations' => 'fa-solid fa-list',
        'invoices' => 'fa-solid fa-file-invoice',
        'book' => 'fa-solid fa-calendar-plus'
    ];
@endphp

<i class="{{ $icons[$action] ?? '' }}"></i>

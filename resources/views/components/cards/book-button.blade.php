@props([
    'roomName',
    'detailUrl',
    'whatsappNumber' => null,
    'bookingEnabled' => false,
    'buttonClass' => 'room-card-book',
])

@php
    $bookHref = $bookingEnabled
        ? $detailUrl
        : \App\Support\EnquiryLink::build($roomName, $whatsappNumber, route('contact'));
@endphp

@if ($bookingEnabled)
    <a class="{{ $buttonClass }}" href="{{ $bookHref }}" aria-label="Book {{ $roomName }}">BOOK</a>
@else
    <a class="{{ $buttonClass }}" href="{{ $bookHref }}" target="_blank" rel="noopener noreferrer" aria-label="Book {{ $roomName }}">BOOK</a>
@endif

@props([
    'variant' => 'primary',
    'href' => null,
    'type' => 'button',
    'disabled' => false,
    'disabledReason' => null,
])

@php
    $classes = collect(['btn', 'btn-' . $variant, $disabled ? 'btn-disabled' : null])->filter()->implode(' ');
@endphp

@if ($disabled)
    <span {{ $attributes->merge(['class' => $classes]) }} aria-disabled="true" title="{{ $disabledReason }}">{{ $slot }}</span>
@elseif ($href)
    <a href="{{ $href }}" {{ $attributes->merge(['class' => $classes]) }}>{{ $slot }}</a>
@else
    <button type="{{ $type }}" {{ $attributes->merge(['class' => $classes]) }}>{{ $slot }}</button>
@endif

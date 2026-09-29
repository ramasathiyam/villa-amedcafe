@props([
    'href' => null,
    'disabled' => false,
    'disabledReason' => null,
    'target' => null,
    'rel' => null,
])

@php
    $classes = collect(['rule-link', $disabled ? 'rule-link-disabled' : null])->filter()->implode(' ');
@endphp

@if ($disabled || !$href)
    <span {{ $attributes->merge(['class' => $classes]) }} aria-disabled="true" title="{{ $disabledReason }}">
        <span class="rule-link-bar" aria-hidden="true"></span>{{ $slot }}
    </span>
@else
    <a href="{{ $href }}" {{ $attributes->merge(['class' => $classes]) }} @if($target) target="{{ $target }}" @endif @if($rel) rel="{{ $rel }}" @endif>
        <span class="rule-link-bar" aria-hidden="true"></span>{{ $slot }}
    </a>
@endif

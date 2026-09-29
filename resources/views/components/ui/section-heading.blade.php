@props([
    'as' => 'h2',
    'align' => 'center',
    'uppercase' => true,
])

@php
    $classes = collect(['section-heading', 'section-heading-' . $align, !$uppercase ? 'section-heading-normal-case' : null])->filter()->implode(' ');
@endphp

<{!! $as !!} {{ $attributes->merge(['class' => $classes]) }}>{{ $slot }}</{!! $as !!}>

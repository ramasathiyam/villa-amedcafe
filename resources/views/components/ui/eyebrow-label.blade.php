@props(['showBullet' => true])

<span {{ $attributes->merge(['class' => 'eyebrow']) }}>
    @if ($showBullet)
        <span class="eyebrow-bullet" aria-hidden="true"></span>
    @endif
    {{ $slot }}
</span>

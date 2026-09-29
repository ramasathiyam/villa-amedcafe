{{--
    Consumers wrap each child card in a `<div class="card-grid-slide">…</div>` themselves
    (the equivalent of the Next.js reference's CardGrid auto-wrapping each React child) —
    Blade has no direct equivalent of iterating a slot's children, so the slide wrapper is
    applied at the call site instead.
--}}
@props([
    'heading' => null,
    'dotCount' => null,
])

@if ($slot->isNotEmpty())
<section class="card-grid-section" data-card-grid>
    <div data-reveal="fade-up">
        @if ($heading)
            <div class="card-grid-heading">
                <x-ui.section-heading>{{ $heading }}</x-ui.section-heading>
            </div>
        @endif

        <div class="card-grid-track" data-card-track>
            {{ $slot }}
        </div>
    </div>

    @if ($dotCount && $dotCount > 1)
        <div class="card-grid-dots" data-card-dots>
            <button type="button" class="card-grid-arrow" data-arrow="prev" aria-label="Previous">‹</button>
            @for ($i = 0; $i < $dotCount; $i++)
                <button type="button" class="card-grid-dot @if($i === 0) is-active @endif" data-dot-index="{{ $i }}" aria-label="Go to slide {{ $i + 1 }}" aria-current="{{ $i === 0 ? 'true' : 'false' }}"></button>
            @endfor
            <button type="button" class="card-grid-arrow" data-arrow="next" aria-label="Next">›</button>
        </div>
    @endif
</section>
@endif

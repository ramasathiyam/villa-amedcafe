@props([
    'heading' => 'What Our Guests Say',
    'items' => [],
])

<section class="testimonials">
    <div data-reveal="fade-up">
        <x-ui.section-heading>{{ $heading }}</x-ui.section-heading>
    </div>
    <div class="testimonials-grid" data-reveal-group>
        @foreach ($items as $index => $item)
            <div class="testimonials-card" data-reveal-item style="--stagger-i: {{ min($index, 6) }}">
                <p>&ldquo;{{ $item['quote'] }}&rdquo;</p>
                <div class="testimonials-avatar-row">
                    <span class="testimonials-avatar" aria-hidden="true"></span>
                    <div>
                        <p>{{ $item['name'] }}</p>
                        <p aria-label="{{ $item['rating'] }} out of 5 stars">{{ str_repeat('★', $item['rating']) }}</p>
                    </div>
                </div>
            </div>
        @endforeach
    </div>
</section>

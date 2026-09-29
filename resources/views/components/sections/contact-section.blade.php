@props([
    'eyebrow',
    'heading',
    'body',
    'infoItems' => [],
    'formHeading',
])

<section class="contact-section">
    <div data-reveal="fade-up">
        <div class="contact-info-header">
            <p class="contact-eyebrow-rule">{{ $eyebrow }}</p>
            <x-ui.section-heading as="h2" align="left" :uppercase="false">{{ $heading }}</x-ui.section-heading>
            <p class="contact-body">{{ $body }}</p>
        </div>

        <ul class="contact-info-list">
            @foreach ($infoItems as $item)
                <li class="contact-info-item">
                    <span class="contact-info-icon" aria-hidden="true">{{ $item['icon'] }}</span>
                    <div>
                        <p class="contact-info-label">{{ $item['label'] }}</p>
                        <p class="contact-info-value">{!! $item['value'] !!}</p>
                    </div>
                </li>
            @endforeach
        </ul>
    </div>

    <div data-reveal="fade-up" style="transition-delay: 100ms">
        <x-ui.section-heading as="h2" align="left" class="contact-form-heading">{{ $formHeading }}</x-ui.section-heading>

        <form class="contact-form">
            <div>
                <label class="contact-field-label" for="contact-first-name">Name *</label>
                <div class="contact-field-row">
                    <input id="contact-first-name" class="contact-input" type="text" name="firstName" placeholder="First Name" required>
                    <input class="contact-input" type="text" name="lastName" placeholder="Last Name" aria-label="Last Name" required>
                </div>
            </div>

            <div>
                <label class="contact-field-label" for="contact-email">Email *</label>
                <input id="contact-email" class="contact-input" type="email" name="email" placeholder="Your Email" required>
            </div>

            <div>
                <label class="contact-field-label" for="contact-phone">Phone *</label>
                <input id="contact-phone" class="contact-input" type="tel" name="phone" placeholder="Your Number" required>
            </div>

            <div>
                <label class="contact-field-label" for="contact-message">Message *</label>
                <textarea id="contact-message" class="contact-textarea" name="message" placeholder="Your Message" required></textarea>
            </div>

            <div class="contact-submit-row">
                <x-ui.button type="submit" variant="solid">Send Message</x-ui.button>
            </div>
        </form>
    </div>
</section>

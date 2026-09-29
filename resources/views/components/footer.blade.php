@php
    $aboutLinks = \App\Support\SiteNav::footerLinks();
    $phone = \App\Models\Setting::get('phone', '');
    $whatsapp = \App\Models\Setting::get('whatsapp', '');
    $email = \App\Models\Setting::get('email', '');
    $address = \App\Models\Setting::get('address', '');
    $googleMapsUrl = \App\Models\Setting::get('google_maps_url', '');
@endphp
<footer class="footer">
    <div class="footer-newsletter">
        <div>
            <p class="footer-newsletter-heading">Become a Member</p>
            <p class="footer-newsletter-sub">Stay Connected</p>
        </div>
        <form class="footer-newsletter-form">
            <input type="email" name="email" placeholder="Email Address" required>
            <x-ui.button type="submit" variant="solid">Submit</x-ui.button>
        </form>
    </div>

    <div class="footer-columns">
        <div class="footer-brand">
            <img src="{{ asset('images/logo/logo-white.png') }}" alt="Amed Café & Hotel Kebun Wayan" class="footer-brand-mark">
        </div>

        <div>
            <h3>Contact Us</h3>
            <ul>
                <li><a href="mailto:{{ $email }}">{{ $email }}</a></li>
                <li><a href="tel:+{{ \App\Models\Setting::phoneDigits('phone') }}">{{ $phone }}</a></li>
                <li><a href="tel:+{{ \App\Models\Setting::phoneDigits('whatsapp') }}">{{ $whatsapp }}</a></li>
            </ul>
        </div>

        <div>
            <h3>About Us</h3>
            <ul>
                @foreach ($aboutLinks as $item)
                    <li>
                        @if (!empty($item['disabled']))
                            <span aria-disabled="true" title="Page coming soon" class="footer-link-disabled">{{ $item['label'] }}</span>
                        @else
                            <a href="{{ $item['href'] }}">{{ $item['label'] }}</a>
                        @endif
                    </li>
                @endforeach
            </ul>
        </div>

        <div>
            <h3>Location</h3>
            <address class="footer-address">{{ $address }}</address>
            <a class="footer-map-link" href="{{ $googleMapsUrl }}" target="_blank" rel="noopener noreferrer" aria-label="View Amed Café & Hotel Kebun Wayan on Google Maps">
                <img src="{{ asset('images/footer/map.png') }}" alt="Map showing the location of Amed Café & Hotel Kebun Wayan" class="footer-map-image">
            </a>
            <x-ui.rule-link href="{{ $googleMapsUrl }}" target="_blank" rel="noopener noreferrer">View on Google Maps</x-ui.rule-link>
        </div>
    </div>
</footer>

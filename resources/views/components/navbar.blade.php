@php
    $navItems = \App\Support\SiteNav::items();
@endphp
<header class="navbar">
    <div class="navbar-utility-row">
        <div class="navbar-contact-icons">
            <span aria-label="Phone">☎</span>
            <span aria-label="Email">✉</span>
        </div>

        <div class="navbar-utility-right">
            @if ($bookNowDisabled)
                <x-ui.button variant="primary" disabled disabled-reason="{{ $bookNowReason }}">Book Now</x-ui.button>
            @else
                <x-ui.button variant="primary" href="{{ $bookNowUrl }}" target="_blank" rel="noopener noreferrer">Book Now</x-ui.button>
            @endif
            <button type="button" class="navbar-mobile-toggle" data-mobile-toggle aria-label="Open menu" aria-expanded="false">
                <span class="navbar-mobile-toggle-bar"></span>
                <span class="navbar-mobile-toggle-bar"></span>
                <span class="navbar-mobile-toggle-bar"></span>
            </button>
        </div>
    </div>

    <div class="navbar-logo-row">
        <a href="/" class="navbar-logo">
            <img src="{{ asset('images/logo/logo-white.png') }}" alt="Amed Café & Hotel Kebun Wayan" class="navbar-logo-image">
        </a>
    </div>

    <nav class="navbar-nav-row" aria-label="Primary">
        <ul class="navbar-links">
            @foreach ($navItems as $item)
                @if (!empty($item['children']))
                    <li class="navbar-item-with-children" data-nav-dropdown>
                        <button type="button" class="navbar-dropdown-trigger" data-dropdown-trigger aria-haspopup="true" aria-expanded="false">
                            {{ $item['label'] }}
                            <span class="navbar-caret" aria-hidden="true">▾</span>
                        </button>
                        <div class="navbar-dropdown">
                            <ul>
                                @foreach ($item['children'] as $child)
                                    <li><a href="{{ $child['href'] }}">{{ $child['label'] }}</a></li>
                                @endforeach
                            </ul>
                        </div>
                    </li>
                @elseif (!empty($item['disabled']))
                    <li><span class="navbar-link-disabled" aria-disabled="true" title="Page coming soon">{{ $item['label'] }}</span></li>
                @else
                    <li><a href="{{ $item['href'] ?? '/' }}">{{ $item['label'] }}</a></li>
                @endif
            @endforeach
        </ul>
    </nav>

    <div class="mobile-panel" data-mobile-panel hidden role="dialog" aria-modal="true" aria-label="Mobile navigation">
        <button type="button" class="mobile-panel-close" data-mobile-close aria-label="Close menu">&times;</button>

        <ul class="mobile-panel-links">
            @foreach ($navItems as $item)
                @if (!empty($item['children']))
                    <li class="mobile-panel-group">
                        <button type="button" class="mobile-panel-submenu-trigger" data-submenu-trigger="{{ $item['label'] }}" aria-expanded="false">
                            {{ $item['label'] }}
                            <span aria-hidden="true">+</span>
                        </button>
                        <ul class="mobile-panel-submenu" data-submenu-panel="{{ $item['label'] }}" hidden>
                            @foreach ($item['children'] as $child)
                                <li><a href="{{ $child['href'] }}">{{ $child['label'] }}</a></li>
                            @endforeach
                        </ul>
                    </li>
                @elseif (!empty($item['disabled']))
                    <li><span class="mobile-panel-link-disabled" aria-disabled="true" title="Page coming soon">{{ $item['label'] }}</span></li>
                @else
                    <li><a href="{{ $item['href'] ?? '/' }}">{{ $item['label'] }}</a></li>
                @endif
            @endforeach
        </ul>

        @if ($bookNowDisabled)
            <x-ui.button variant="primary" disabled disabled-reason="{{ $bookNowReason }}">Book Now</x-ui.button>
        @else
            <x-ui.button variant="primary" href="{{ $bookNowUrl }}" target="_blank" rel="noopener noreferrer">Book Now</x-ui.button>
        @endif
    </div>
</header>

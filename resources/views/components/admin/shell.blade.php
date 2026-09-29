@props(['active' => null])

@php
    // Route-model-bound {dining} param (resolves to the DiningVenue on the Edit page,
    // absent on the index page) — used only to highlight which of the two fixed venues
    // is currently open, no extra query.
    $activeDiningSlug = optional(request()->route('dining'))->slug;
    // Same pattern for the {page} param on /admin/pages/{page}/edit.
    $activePageSlug = optional(request()->route('page'))->slug;
@endphp

<div class="admin-shell">
    <header class="admin-topbar">
        <div class="admin-topbar-brand">
            <img src="{{ asset('images/logo/logo-white.png') }}" alt="Amed Café & Hotel Kebun Wayan" class="admin-topbar-logo">
            <span class="admin-topbar-title">Admin</span>
        </div>

        <div class="admin-topbar-user">
            <span class="admin-topbar-name">{{ auth()->user()->name }}</span>
            <form method="POST" action="{{ route('admin.logout') }}">
                @csrf
                <button type="submit" class="admin-btn admin-btn-outline admin-btn-sm">Logout</button>
            </form>
        </div>
    </header>

    <div class="admin-body-grid">
        <nav class="admin-sidebar" aria-label="Admin sections">
            <ul class="admin-nav">
                <li><a href="{{ route('admin.dashboard') }}" class="admin-nav-link @if($active === 'dashboard') is-active @endif">Dashboard</a></li>
                <li><a href="{{ route('admin.rooms.index') }}" class="admin-nav-link @if($active === 'rooms') is-active @endif">Rooms</a></li>
                <li><a href="{{ route('admin.activities.index') }}" class="admin-nav-link @if($active === 'activities') is-active @endif">Activities</a></li>
                <li><a href="{{ route('admin.events.index') }}" class="admin-nav-link @if($active === 'events') is-active @endif">Events</a></li>
                <li class="admin-nav-group-item">
                    <details class="admin-nav-group" @if($active === 'dining') open @endif>
                        <summary class="admin-nav-link admin-nav-parent @if($active === 'dining') is-active @endif">Dining</summary>
                        <ul class="admin-nav-sub">
                            <li><a href="{{ route('admin.dining.edit', 'resto-amed-cafe') }}" class="admin-nav-sublink @if($activeDiningSlug === 'resto-amed-cafe') is-active @endif">Resto Amed Café</a></li>
                            <li><a href="{{ route('admin.dining.edit', 'barak-rooftop-and-bar') }}" class="admin-nav-sublink @if($activeDiningSlug === 'barak-rooftop-and-bar') is-active @endif">Barak Rooftop &amp; Bar</a></li>
                        </ul>
                    </details>
                </li>
                <li class="admin-nav-group-item">
                    <details class="admin-nav-group" @if($active === 'pages') open @endif>
                        <summary class="admin-nav-link admin-nav-parent @if($active === 'pages') is-active @endif">Site Pages</summary>
                        <ul class="admin-nav-sub">
                            <li><a href="{{ route('admin.pages.edit', 'home') }}" class="admin-nav-sublink @if($activePageSlug === 'home') is-active @endif">Home</a></li>
                            <li><a href="{{ route('admin.pages.edit', 'rooms') }}" class="admin-nav-sublink @if($activePageSlug === 'rooms') is-active @endif">Rooms</a></li>
                            <li><a href="{{ route('admin.pages.edit', 'activities') }}" class="admin-nav-sublink @if($activePageSlug === 'activities') is-active @endif">Activities</a></li>
                            <li><a href="{{ route('admin.pages.edit', 'spa') }}" class="admin-nav-sublink @if($activePageSlug === 'spa') is-active @endif">Spa</a></li>
                            <li><a href="{{ route('admin.pages.edit', 'resto-amed-cafe') }}" class="admin-nav-sublink @if($activePageSlug === 'resto-amed-cafe') is-active @endif">Resto Amed Café</a></li>
                            <li><a href="{{ route('admin.pages.edit', 'barak-rooftop-and-bar') }}" class="admin-nav-sublink @if($activePageSlug === 'barak-rooftop-and-bar') is-active @endif">Barak Rooftop &amp; Bar</a></li>
                        </ul>
                    </details>
                </li>
                <li><a href="{{ route('admin.banners.index') }}" class="admin-nav-link @if($active === 'banners') is-active @endif">Banner</a></li>
                <li><a href="{{ route('admin.settings.edit') }}" class="admin-nav-link @if($active === 'settings') is-active @endif">Site Settings</a></li>
                <li><a href="{{ route('admin.testimonials.index') }}" class="admin-nav-link @if($active === 'testimonials') is-active @endif">Testimonials</a></li>
            </ul>

            <hr class="admin-nav-divider">

            <ul class="admin-nav admin-sidebar-foot">
                <li><a href="{{ url('/') }}" target="_blank" rel="noopener noreferrer" class="admin-nav-link">View Website</a></li>
            </ul>
        </nav>

        <main class="admin-main">
            {{ $slot }}
        </main>
    </div>
</div>

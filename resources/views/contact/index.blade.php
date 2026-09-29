@extends('layouts.app')

@section('title', 'Contact Us — Amed Café & Hotel Kebun Wayan')
@section('description', 'The Balinese style hotel in Amed, Bali.')

@section('content')
    @php
        $googleMapsUrl = \App\Models\Setting::get('google_maps_url', '');
        $phone = \App\Models\Setting::get('phone', '');
        $whatsapp = \App\Models\Setting::get('whatsapp', '');
        $email = \App\Models\Setting::get('email', '');
        $address = \App\Models\Setting::get('address', '');
        $contactInfoItems = [
            [
                'icon' => '⌖',
                'label' => 'Visit Us',
                'value' => '<a href="' . $googleMapsUrl . '" target="_blank" rel="noopener noreferrer">' . $address . '</a>',
            ],
            [
                'icon' => '☎',
                'label' => 'Phone',
                'value' => '<a href="tel:+' . \App\Models\Setting::phoneDigits('phone') . '">' . $phone . '</a><br><a href="tel:+' . \App\Models\Setting::phoneDigits('whatsapp') . '">' . $whatsapp . '</a>',
            ],
            [
                'icon' => '✉',
                'label' => 'Email',
                'value' => '<a href="mailto:' . $email . '">' . $email . '</a>',
            ],
        ];
    @endphp

    <x-sections.hero
        title="Contact Us"
        subtitle="The Balinese Style Hotel in Amed Bali"
        title-align="left"
        :image="['src' => '/images/contact/contact-hero.png', 'alt' => 'Refill mineral water bottle on a café table at Amed Café & Hotel Kebun Wayan']"
    />

    <x-sections.intro-section
        heading="Contact Us"
        body="Nestled in the tranquil village of Amed, Kebun Wayan is a charming café and hotel surrounded by tropical gardens, offering a peaceful escape with the authentic beauty of Bali."
        link-label="Book Now"
        :link-disabled="true"
        link-disabled-reason="Booking flow not defined yet"
    />

    <x-sections.contact-section
        eyebrow="Amed Café & Hotel Kebun Wayan"
        heading="Contact Us"
        body="Lorem Ipsum is simply dummy text of the printing and typesetting industry. Lorem Ipsum has been the industry's standard dummy text ever since 1966, when designers at Letraset and James Mosley, the librarian at St Bride Printing Library in London,"
        form-heading="Get in Touch"
        :info-items="$contactInfoItems"
    />

    <x-banner page="contact" />
@endsection

@extends('layouts.admin')

@section('title', 'Site Settings')

@section('content')
    <x-admin.shell active="settings">
        <h1 class="admin-heading">Site Settings</h1>
        <p class="admin-subheading">Manage the contact information used across the website.</p>

        <form method="POST" action="{{ route('admin.settings.update') }}" class="admin-form admin-form-wide">
            @csrf
            @method('PUT')

            <div class="admin-field">
                <label class="admin-label" for="phone">Phone</label>
                <input id="phone" class="admin-input" type="text" name="phone" value="{{ old('phone', $settings['phone']) }}" required>
                <p class="admin-hint">Shown in the footer and Contact page as a tel: link, e.g. (0363) 23473.</p>
                @error('phone') <p class="admin-field-error">{{ $message }}</p> @enderror
            </div>

            <div class="admin-field">
                <label class="admin-label" for="whatsapp">WhatsApp</label>
                <input id="whatsapp" class="admin-input" type="text" name="whatsapp" value="{{ old('whatsapp', $settings['whatsapp']) }}" required>
                <p class="admin-hint">Local format with leading 0, e.g. 087715021995. Used for the WhatsApp enquiry links.</p>
                @error('whatsapp') <p class="admin-field-error">{{ $message }}</p> @enderror
            </div>

            <div class="admin-field">
                <label class="admin-label" for="email">Email</label>
                <input id="email" class="admin-input" type="email" name="email" value="{{ old('email', $settings['email']) }}">
                @error('email') <p class="admin-field-error">{{ $message }}</p> @enderror
            </div>

            <div class="admin-field">
                <label class="admin-label" for="address">Address</label>
                <textarea id="address" class="admin-textarea" name="address" rows="3" required>{{ old('address', $settings['address']) }}</textarea>
                @error('address') <p class="admin-field-error">{{ $message }}</p> @enderror
            </div>

            <div class="admin-field">
                <label class="admin-label" for="google_maps_url">Google Maps URL</label>
                <input id="google_maps_url" class="admin-input" type="url" name="google_maps_url" value="{{ old('google_maps_url', $settings['google_maps_url']) }}">
                @error('google_maps_url') <p class="admin-field-error">{{ $message }}</p> @enderror
            </div>

            <div class="admin-field">
                <label class="admin-label" for="expedia_url">Expedia URL</label>
                <input id="expedia_url" class="admin-input" type="url" name="expedia_url" value="{{ old('expedia_url', $settings['expedia_url']) }}">
                <p class="admin-hint">Leave empty to hide this button.</p>
                @error('expedia_url') <p class="admin-field-error">{{ $message }}</p> @enderror
            </div>

            <div class="admin-field">
                <label class="admin-label" for="booking_com_url">Booking.com URL</label>
                <input id="booking_com_url" class="admin-input" type="url" name="booking_com_url" value="{{ old('booking_com_url', $settings['booking_com_url']) }}">
                <p class="admin-hint">Leave empty to hide this button.</p>
                @error('booking_com_url') <p class="admin-field-error">{{ $message }}</p> @enderror
            </div>

            <div class="admin-field">
                <label class="admin-label" for="agoda_url">Agoda URL</label>
                <input id="agoda_url" class="admin-input" type="url" name="agoda_url" value="{{ old('agoda_url', $settings['agoda_url']) }}">
                <p class="admin-hint">Leave empty to hide this button.</p>
                @error('agoda_url') <p class="admin-field-error">{{ $message }}</p> @enderror
            </div>

            <div class="admin-form-actions">
                <button type="submit" class="admin-btn admin-btn-solid">Save Changes</button>
            </div>
        </form>
    </x-admin.shell>
@endsection

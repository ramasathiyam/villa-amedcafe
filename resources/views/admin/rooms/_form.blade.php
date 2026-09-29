@csrf

<div class="admin-field">
    <label class="admin-label" for="name">Name</label>
    <input id="name" class="admin-input" type="text" name="name" value="{{ old('name', $room->name) }}" required>
    @error('name') <p class="admin-field-error">{{ $message }}</p> @enderror
</div>

<div class="admin-form-row admin-form-row-4">
    <div class="admin-field">
        <label class="admin-label" for="rate_per_night">Rate / Night</label>
        <input id="rate_per_night" class="admin-input" type="number" min="0" name="rate_per_night" value="{{ old('rate_per_night', $room->rate_per_night) }}" required>
        @error('rate_per_night') <p class="admin-field-error">{{ $message }}</p> @enderror
    </div>
    <div class="admin-field">
        <label class="admin-label" for="currency">Currency</label>
        <input id="currency" class="admin-input" type="text" name="currency" value="{{ old('currency', $room->currency ?? 'Rp') }}" required>
        @error('currency') <p class="admin-field-error">{{ $message }}</p> @enderror
    </div>
    <div class="admin-field">
        <label class="admin-label" for="size_sqm">Size (m²)</label>
        <input id="size_sqm" class="admin-input" type="number" min="1" name="size_sqm" value="{{ old('size_sqm', $room->size_sqm) }}" required>
        @error('size_sqm') <p class="admin-field-error">{{ $message }}</p> @enderror
    </div>
    <div class="admin-field">
        <label class="admin-label" for="max_guests">Max Guests</label>
        <input id="max_guests" class="admin-input" type="number" min="1" name="max_guests" value="{{ old('max_guests', $room->max_guests) }}" required>
        @error('max_guests') <p class="admin-field-error">{{ $message }}</p> @enderror
    </div>
</div>

<div class="admin-field">
    <label class="admin-label" for="bedding">Bedding</label>
    <input id="bedding" class="admin-input" type="text" name="bedding" value="{{ old('bedding', $room->bedding) }}" required>
    @error('bedding') <p class="admin-field-error">{{ $message }}</p> @enderror
</div>

<div class="admin-field">
    <label class="admin-label" for="hotel_information">Hotel Information</label>
    <textarea id="hotel_information" class="admin-textarea" name="hotel_information" rows="4">{{ old('hotel_information', $room->hotel_information) }}</textarea>
    <p class="admin-hint">Shown under "More Information" on this room's detail page, and in the Family Room section on the Rooms page if this room is marked Featured. Leave empty to hide it.</p>
    @error('hotel_information') <p class="admin-field-error">{{ $message }}</p> @enderror
</div>

<div class="admin-checkbox-row">
    <label class="admin-checkbox">
        <input type="checkbox" name="includes_breakfast" value="1" @checked(old('includes_breakfast', $room->includes_breakfast))>
        Includes Breakfast
    </label>
    <label class="admin-checkbox">
        <input type="checkbox" name="is_featured" value="1" @checked(old('is_featured', $room->is_featured))>
        Featured (Family Room composite)
    </label>
    <label class="admin-checkbox">
        <input type="checkbox" name="is_active" value="1" @checked(old('is_active', $room->exists ? $room->is_active : true))>
        Active
    </label>
</div>

<div class="admin-form-row">
    <div class="admin-field">
        <label class="admin-label" for="total_units">
            Total Units <span class="admin-hint">(number of physical rooms of this type available to book)</span>
        </label>
        <input id="total_units" class="admin-input" type="number" min="0" name="total_units" value="{{ old('total_units', $room->total_units ?? 1) }}" required>
        @error('total_units') <p class="admin-field-error">{{ $message }}</p> @enderror
    </div>
    <div class="admin-field">
        <label class="admin-label" for="sort_order">Sort Order</label>
        <input id="sort_order" class="admin-input" type="number" min="0" name="sort_order" value="{{ old('sort_order', $room->sort_order ?? 0) }}">
        @error('sort_order') <p class="admin-field-error">{{ $message }}</p> @enderror
    </div>
</div>

<div class="admin-field">
    <label class="admin-label" for="image">Main Photo @if(!$room->exists) (required) @endif</label>

    @if ($room->exists && $room->image)
        <div class="admin-image-preview">
            <img src="{{ \App\Support\CmsImage::url($room->image) }}" alt="{{ $room->name }}">
        </div>
    @endif

    <input id="image" class="admin-input" type="file" name="image" accept="image/*">
    <p class="admin-hint">JPG, PNG, or WebP. Max 4MB. @if($room->exists) Leave empty to keep the current photo. @endif</p>
    @error('image') <p class="admin-field-error">{{ $message }}</p> @enderror
</div>

<div class="admin-form-actions">
    <button type="submit" class="admin-btn admin-btn-solid">{{ $room->exists ? 'Save Changes' : 'Create Room' }}</button>
    <a href="{{ route('admin.rooms.index') }}" class="admin-btn admin-btn-outline-dark">Cancel</a>
</div>

@csrf

<div class="admin-form-row">
    <div class="admin-field">
        <label class="admin-label" for="name">Guest Name</label>
        <input id="name" class="admin-input" type="text" name="name" value="{{ old('name', $testimonial->name) }}" required>
        @error('name') <p class="admin-field-error">{{ $message }}</p> @enderror
    </div>
    <div class="admin-field">
        <label class="admin-label" for="rating">Rating</label>
        <select id="rating" class="admin-input" name="rating" required>
            @for ($i = 5; $i >= 1; $i--)
                <option value="{{ $i }}" @selected(old('rating', $testimonial->rating ?? 5) == $i)>{{ $i }} star{{ $i > 1 ? 's' : '' }}</option>
            @endfor
        </select>
        @error('rating') <p class="admin-field-error">{{ $message }}</p> @enderror
    </div>
</div>

<div class="admin-field">
    <label class="admin-label" for="quote">Quote</label>
    <textarea id="quote" class="admin-textarea" name="quote" rows="4" required>{{ old('quote', $testimonial->quote) }}</textarea>
    @error('quote') <p class="admin-field-error">{{ $message }}</p> @enderror
</div>

<div class="admin-form-row">
    <div class="admin-field">
        <label class="admin-label" for="dining_venue_id">
            Dining Venue <span class="admin-hint">(leave blank to show sitewide)</span>
        </label>
        <select id="dining_venue_id" class="admin-input" name="dining_venue_id">
            <option value="" @selected(old('dining_venue_id', $testimonial->dining_venue_id) === null)>— Global —</option>
            @foreach ($venues as $venue)
                <option value="{{ $venue->id }}" @selected((string) old('dining_venue_id', $testimonial->dining_venue_id) === (string) $venue->id)>{{ $venue->name }}</option>
            @endforeach
        </select>
        @error('dining_venue_id') <p class="admin-field-error">{{ $message }}</p> @enderror
    </div>
    <div class="admin-field">
        <label class="admin-label" for="sort_order">Sort Order</label>
        <input id="sort_order" class="admin-input" type="number" min="0" name="sort_order" value="{{ old('sort_order', $testimonial->sort_order ?? 0) }}">
        @error('sort_order') <p class="admin-field-error">{{ $message }}</p> @enderror
    </div>
</div>

<div class="admin-checkbox-row">
    <label class="admin-checkbox">
        <input type="checkbox" name="is_active" value="1" @checked(old('is_active', $testimonial->exists ? $testimonial->is_active : true))>
        Active
    </label>
</div>

<div class="admin-form-actions">
    <button type="submit" class="admin-btn admin-btn-solid">{{ $testimonial->exists ? 'Save Changes' : 'Create Testimonial' }}</button>
    <a href="{{ route('admin.testimonials.index') }}" class="admin-btn admin-btn-outline-dark">Cancel</a>
</div>

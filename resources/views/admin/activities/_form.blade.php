@csrf

<div class="admin-field">
    <label class="admin-label" for="name">Name</label>
    <input id="name" class="admin-input" type="text" name="name" value="{{ old('name', $activity->name) }}" required>
    @error('name') <p class="admin-field-error">{{ $message }}</p> @enderror
</div>

<div class="admin-field">
    <label class="admin-label" for="description">
        Description <span class="admin-hint">(used by the Home page's teaser cards)</span>
    </label>
    <textarea id="description" class="admin-textarea" name="description" rows="3">{{ old('description', $activity->description) }}</textarea>
    @error('description') <p class="admin-field-error">{{ $message }}</p> @enderror
</div>

<div class="admin-form-row">
    <div class="admin-field">
        <label class="admin-label" for="price_label">
            Price Label <span class="admin-hint">(free text, e.g. "Rp700.000/Pax")</span>
        </label>
        <input id="price_label" class="admin-input" type="text" name="price_label" value="{{ old('price_label', $activity->price_label) }}">
        @error('price_label') <p class="admin-field-error">{{ $message }}</p> @enderror
    </div>
    <div class="admin-field">
        <label class="admin-label" for="sort_order">Sort Order</label>
        <input id="sort_order" class="admin-input" type="number" min="0" name="sort_order" value="{{ old('sort_order', $activity->sort_order ?? 0) }}">
        @error('sort_order') <p class="admin-field-error">{{ $message }}</p> @enderror
    </div>
</div>

<div class="admin-field">
    <label class="admin-label" for="includes">
        Includes <span class="admin-hint">(one item per line — used by the "Exceptional Experiences" grid)</span>
    </label>
    <textarea id="includes" class="admin-textarea" name="includes" rows="4">{{ old('includes', $activity->includes ? implode("\n", $activity->includes) : '') }}</textarea>
    @error('includes') <p class="admin-field-error">{{ $message }}</p> @enderror
</div>

<div class="admin-checkbox-row">
    <label class="admin-checkbox">
        <input type="checkbox" name="show_on_home" value="1" @checked(old('show_on_home', $activity->show_on_home))>
        Show on Home
    </label>
    <label class="admin-checkbox">
        <input type="checkbox" name="show_on_activity_page" value="1" @checked(old('show_on_activity_page', $activity->show_on_activity_page))>
        Show on Activity Page
    </label>
    <label class="admin-checkbox">
        <input type="checkbox" name="is_active" value="1" @checked(old('is_active', $activity->exists ? $activity->is_active : true))>
        Active
    </label>
</div>

<div class="admin-field">
    <label class="admin-label" for="image">Photo</label>

    @if ($activity->exists && $activity->image)
        <div class="admin-image-preview">
            <img src="{{ \App\Support\CmsImage::url($activity->image) }}" alt="{{ $activity->name }}">
        </div>
    @endif

    <input id="image" class="admin-input" type="file" name="image" accept="image/*">
    <p class="admin-hint">JPG, PNG, or WebP. Max 4MB. @if($activity->exists) Leave empty to keep the current photo. @endif</p>
    @error('image') <p class="admin-field-error">{{ $message }}</p> @enderror
</div>

<div class="admin-form-actions">
    <button type="submit" class="admin-btn admin-btn-solid">{{ $activity->exists ? 'Save Changes' : 'Create Activity' }}</button>
    <a href="{{ route('admin.activities.index') }}" class="admin-btn admin-btn-outline-dark">Cancel</a>
</div>

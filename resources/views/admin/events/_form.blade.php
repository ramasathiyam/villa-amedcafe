@csrf

<h2 class="admin-subheading-strong">Content</h2>

<div class="admin-field">
    <label class="admin-label" for="label">Label</label>
    <input id="label" class="admin-input" type="text" name="label" value="{{ old('label', $event->label) }}" required>
    <p class="admin-hint">Small eyebrow text above the heading, e.g. "Seasonal Event".</p>
    @error('label') <p class="admin-field-error">{{ $message }}</p> @enderror
</div>

<div class="admin-field">
    <label class="admin-label" for="title">Title</label>
    <input id="title" class="admin-input" type="text" name="title" value="{{ old('title', $event->title) }}" required>
    @error('title') <p class="admin-field-error">{{ $message }}</p> @enderror
</div>

<div class="admin-field">
    <label class="admin-label" for="description">Description</label>
    <textarea id="description" class="admin-textarea" name="description" rows="4" required>{{ old('description', $event->description) }}</textarea>
    @error('description') <p class="admin-field-error">{{ $message }}</p> @enderror
</div>

<h2 class="admin-subheading-strong">Image</h2>

<div class="admin-field">
    <label class="admin-label" for="background_image">Background Image @if(!$event->exists) (required) @endif</label>

    @if ($event->exists && $event->background_image)
        <div class="admin-image-preview">
            <img src="{{ \App\Support\CmsImage::url($event->background_image) }}" alt="{{ $event->title }}">
        </div>
    @endif

    <input id="background_image" class="admin-input" type="file" name="background_image" accept="image/*">
    <p class="admin-hint">JPG, PNG, or WebP. Max 4MB. @if($event->exists) Leave empty to keep the current photo. @endif</p>
    @error('background_image') <p class="admin-field-error">{{ $message }}</p> @enderror
</div>

<h2 class="admin-subheading-strong">Link</h2>

<div class="admin-field">
    <label class="admin-label" for="link_url">Discover More URL</label>
    <input id="link_url" class="admin-input" type="text" name="link_url" value="{{ old('link_url', $event->link_url) }}">
    <p class="admin-hint">Optional. The "Discover More" button is hidden when this is empty.</p>
    @error('link_url') <p class="admin-field-error">{{ $message }}</p> @enderror
</div>

<h2 class="admin-subheading-strong">Display</h2>

<div class="admin-form-row">
    <div class="admin-field">
        <label class="admin-label" for="display_from">Show From</label>
        <input id="display_from" class="admin-input" type="date" name="display_from" value="{{ old('display_from', optional($event->display_from)->toDateString()) }}">
        <p class="admin-hint">Optional. Leave empty to show with no start limit.</p>
        @error('display_from') <p class="admin-field-error">{{ $message }}</p> @enderror
    </div>
    <div class="admin-field">
        <label class="admin-label" for="display_until">Show Until</label>
        <input id="display_until" class="admin-input" type="date" name="display_until" value="{{ old('display_until', optional($event->display_until)->toDateString()) }}">
        <p class="admin-hint">Optional. Leave empty to show with no end limit.</p>
        @error('display_until') <p class="admin-field-error">{{ $message }}</p> @enderror
    </div>
</div>

<div class="admin-form-row">
    <div class="admin-checkbox-row">
        <label class="admin-checkbox">
            <input type="checkbox" name="is_active" value="1" @checked(old('is_active', $event->exists ? $event->is_active : true))>
            Active
        </label>
    </div>
    <div class="admin-field">
        <label class="admin-label" for="sort_order">Order</label>
        <input id="sort_order" class="admin-input" type="number" min="0" name="sort_order" value="{{ old('sort_order', $event->sort_order ?? 0) }}">
        @error('sort_order') <p class="admin-field-error">{{ $message }}</p> @enderror
    </div>
</div>

<div class="admin-form-actions">
    <button type="submit" class="admin-btn admin-btn-solid">{{ $event->exists ? 'Save Changes' : 'Create Event' }}</button>
    <a href="{{ route('admin.events.index') }}" class="admin-btn admin-btn-outline-dark">Cancel</a>
</div>

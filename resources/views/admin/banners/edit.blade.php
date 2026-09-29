@extends('layouts.admin')

@section('title', 'Edit Banner')

@section('content')
    <x-admin.shell active="banners">
        <h1 class="admin-heading">Edit Banner</h1>
        <p class="admin-subheading">{{ $pageLabel }}</p>

        <form method="POST" action="{{ route('admin.banners.update', $banner) }}" enctype="multipart/form-data" class="admin-form admin-form-wide">
            @csrf
            @method('PUT')

            <h2 class="admin-subheading-strong">Content</h2>

            <div class="admin-field">
                <label class="admin-label" for="eyebrow">Eyebrow</label>
                <input id="eyebrow" class="admin-input" type="text" name="eyebrow" value="{{ old('eyebrow', $banner->eyebrow) }}" required>
                @error('eyebrow') <p class="admin-field-error">{{ $message }}</p> @enderror
            </div>

            <div class="admin-field">
                <label class="admin-label" for="heading">Heading</label>
                <input id="heading" class="admin-input" type="text" name="heading" value="{{ old('heading', $banner->heading) }}" required>
                @error('heading') <p class="admin-field-error">{{ $message }}</p> @enderror
            </div>

            <div class="admin-field">
                <label class="admin-label" for="body">Body</label>
                <textarea id="body" class="admin-textarea" name="body" rows="4" required>{{ old('body', $banner->body) }}</textarea>
                @error('body') <p class="admin-field-error">{{ $message }}</p> @enderror
            </div>

            <h2 class="admin-subheading-strong">Image</h2>

            <div class="admin-field">
                <label class="admin-label" for="background_image">Background Image</label>

                <div class="admin-image-preview">
                    <img src="{{ \App\Support\CmsImage::url($banner->background_image) }}" alt="{{ $banner->heading }}">
                </div>

                <input id="background_image" class="admin-input" type="file" name="background_image" accept="image/*">
                <p class="admin-hint">JPG, PNG, or WebP. Max 4MB. Leave empty to keep the current photo.</p>
                @error('background_image') <p class="admin-field-error">{{ $message }}</p> @enderror
            </div>

            <h2 class="admin-subheading-strong">Link</h2>

            <div class="admin-field">
                <label class="admin-label" for="link_url">Discover More URL</label>
                <input id="link_url" class="admin-input" type="url" name="link_url" value="{{ old('link_url', $banner->link_url) }}">
                <p class="admin-hint">Optional. The "Discover More" button is hidden when this is empty.</p>
                @error('link_url') <p class="admin-field-error">{{ $message }}</p> @enderror
            </div>

            <div class="admin-checkbox-row">
                <label class="admin-checkbox">
                    <input type="checkbox" name="is_active" value="1" @checked(old('is_active', $banner->is_active))>
                    Active
                </label>
            </div>

            <div class="admin-form-actions">
                <button type="submit" class="admin-btn admin-btn-solid">Save Changes</button>
                <a href="{{ route('admin.banners.index') }}" class="admin-btn admin-btn-outline-dark">Cancel</a>
            </div>
        </form>
    </x-admin.shell>
@endsection

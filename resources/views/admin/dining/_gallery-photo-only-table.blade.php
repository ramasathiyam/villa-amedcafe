{{--
    Simple PHOTO | ACTIONS table for plain venue-image groups — every group EXCEPT
    "menu_highlight" (Name/Description/Sort content, see _gallery-group-table.blade.php)
    and dining_items (see _items-table.blade.php). No Name/Description/Sort fields here at
    all: Choose File replaces the SAME row's photo the instant a file is picked (never
    creates a new row), Delete removes the row + managed file. No Edit/Save button.

    Props: $dining (DiningVenue), $group (string), $heading (string), $images (Collection)
--}}
<section class="admin-room-images">
    <h2 class="admin-subheading-strong">{{ $heading }} ({{ $images->count() }})</h2>

    @if ($images->isNotEmpty())
        <div class="admin-item-table">
            <div class="admin-item-row-header">
                <span class="admin-item-col-photo">Photo</span>
                <span class="admin-item-col-actions" style="margin-left: auto; text-align: right; padding-right: 95px;">Actions</span>
            </div>

            @foreach ($images as $image)
                <div class="admin-item-row">
                    <img src="{{ \App\Support\CmsImage::url($image->image) }}" alt="{{ $image->alt_text }}" class="admin-item-row-thumb">

                    <form method="POST" action="{{ route('admin.dining.images.update', [$dining, $image]) }}" enctype="multipart/form-data" class="admin-item-row-form">
                        @csrf
                        @method('PATCH')
                        <input type="file" name="image" accept="image/*" class="admin-file-input-sm" title="Replace photo" onchange="this.form.submit()">
                    </form>

                    <form method="POST" action="{{ route('admin.dining.images.destroy', [$dining, $image]) }}" onsubmit="return confirm('Delete this photo?');">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="admin-btn admin-btn-danger admin-btn-sm">Delete</button>
                    </form>
                </div>
            @endforeach
        </div>
    @else
        <p class="admin-empty">No photos yet.</p>
    @endif

    <form method="POST" action="{{ route('admin.dining.images.store', $dining) }}" enctype="multipart/form-data" class="admin-upload-form">
        @csrf
        <input type="hidden" name="group" value="{{ $group }}">
        <div class="admin-field">
            <label class="admin-label">Photo</label>
            <input type="file" name="images[]" accept="image/*" multiple class="admin-input" required>
        </div>
        <button type="submit" class="admin-btn admin-btn-solid">+ Add Photo</button>
    </form>
</section>

{{--
    Reusable "carousel" CRUD table for one dining_venue_images `group`. Each venue's Edit
    page includes this once per named carousel section (e.g. Resto's "Menu/Food
    Highlights" = group gallery_1; Barak's "Menu Highlights (Food Strip)" = group
    menu_highlight) — fully independent per venue since $images is already scoped to
    $dining via DiningVenue::imagesByGroup().

    Props: $dining (DiningVenue), $group (string), $heading (string), $images (Collection)
--}}
<section class="admin-room-images">
    <h2 class="admin-subheading-strong">{{ $heading }} ({{ $images->count() }})</h2>

    @if ($images->isNotEmpty())
        <div class="admin-item-table admin-item-table-grid admin-item-table-actions-stack">
            <div class="admin-item-row-header">
                <span class="admin-item-col-photo">Photo</span>
                <span class="admin-item-col-text">Name</span>
                <span class="admin-item-col-text">Description</span>
                <span class="admin-item-col-sort">Sort</span>
                <span class="admin-item-col-actions">Actions</span>
            </div>

            @foreach ($images as $image)
                {{--
                    Exactly 5 real (boxed) children below, one per grid column — img, title
                    input, description input, sort input, actions div. Everything else
                    (hidden CSRF/method/group/alt_text inputs, and the two <form> tags
                    themselves) is display:none — zero layout footprint, so it can never be
                    mistaken for an extra column. The <form> tags exist only to hold
                    method/action/enctype; every actual field is a flat, direct child of
                    .admin-item-row tied back to its owning form via the HTML `form="id"`
                    attribute, not DOM nesting — this is what lets Name/Description/Sort/
                    Actions land in real, guaranteed-aligned grid columns instead of being
                    nested one level deeper inside a form's own box.
                --}}
                <div class="admin-item-row">
                    <img src="{{ \App\Support\CmsImage::url($image->image) }}" alt="{{ $image->alt_text }}" class="admin-item-row-thumb">

                    <input type="text" name="title" value="{{ $image->title }}" placeholder="Name" form="dvi-{{ $image->id }}-update" class="admin-input admin-input-sm">
                    <input type="text" name="description" value="{{ $image->description }}" placeholder="Description" form="dvi-{{ $image->id }}-update" class="admin-input admin-input-sm">
                    <input type="number" name="sort_order" value="{{ $image->sort_order }}" min="0" form="dvi-{{ $image->id }}-update" class="admin-input admin-input-sm admin-input-order">

                    <div class="admin-item-row-actions admin-item-col-actions">
                        <div class="admin-item-row-file">
                            {{-- Choosing a file submits the update form immediately (Name/Description/Sort
                                 above are saved together with the new photo) — no separate button. --}}
                            <input type="file" name="image" form="dvi-{{ $image->id }}-update" accept="image/*" class="admin-file-input-sm" title="Replace photo" onchange="this.form.submit()">
                        </div>
                        <div class="admin-item-row-save-delete">
                            <button type="submit" form="dvi-{{ $image->id }}-update" class="admin-btn admin-btn-outline-dark admin-btn-sm">Save</button>
                            <button type="submit" form="dvi-{{ $image->id }}-delete" class="admin-btn admin-btn-danger admin-btn-sm" onclick="return confirm('Delete this photo?')">Delete</button>
                        </div>
                    </div>

                    <form id="dvi-{{ $image->id }}-update" method="POST" action="{{ route('admin.dining.images.update', [$dining, $image]) }}" enctype="multipart/form-data" class="admin-item-row-form">
                        @csrf
                        @method('PATCH')
                        <input type="hidden" name="group" value="{{ $group }}">
                        <input type="hidden" name="alt_text" value="{{ $image->alt_text }}">
                    </form>

                    <form id="dvi-{{ $image->id }}-delete" method="POST" action="{{ route('admin.dining.images.destroy', [$dining, $image]) }}" class="admin-item-row-delete-shell">
                        @csrf
                        @method('DELETE')
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
        <div class="admin-field">
            <label class="admin-label">Name (optional)</label>
            <input type="text" name="title" class="admin-input">
        </div>
        <div class="admin-field">
            <label class="admin-label">Description (optional)</label>
            <input type="text" name="description" class="admin-input">
        </div>
        <button type="submit" class="admin-btn admin-btn-solid">+ Add Item</button>
    </form>
</section>

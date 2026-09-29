{{--
    Reusable "carousel" CRUD table for dining_items (e.g. Barak's "Signature Cocktails").
    $dining->items is already scoped to this venue via the hasMany relation, so this is
    fully independent per venue.

    Props: $dining (DiningVenue), $heading (string)
--}}
<section class="admin-room-images">
    <h2 class="admin-subheading-strong">{{ $heading }} ({{ $dining->items->count() }})</h2>

    @if ($dining->items->isNotEmpty())
        <div class="admin-item-table admin-item-table-grid">
            <div class="admin-item-row-header">
                <span class="admin-item-col-photo">Photo</span>
                <span class="admin-item-col-text">Name</span>
                <span class="admin-item-col-text">Description</span>
                <span class="admin-item-col-sort">Sort</span>
                <span class="admin-item-col-actions">Actions</span>
            </div>

            @foreach ($dining->items as $item)
                <div class="admin-item-row">
                    @if ($item->image)
                        <img src="{{ \App\Support\CmsImage::url($item->image) }}" alt="{{ $item->name }}" class="admin-item-row-thumb">
                    @else
                        <span class="admin-item-row-thumb"></span>
                    @endif

                    {{-- display:contents (see .admin-item-table-grid in admin.css) — this
                         form's visible fields become direct grid children of .admin-item-row
                         so Name/Description/Sort land in their own real grid columns, truly
                         aligned with the header, instead of being nested one level deeper
                         inside this form's own box. The form itself still submits normally. --}}
                    <form id="di-{{ $item->id }}-update" method="POST" action="{{ route('admin.dining.items.update', [$dining, $item]) }}" class="admin-item-row-form" enctype="multipart/form-data">
                        @csrf
                        @method('PATCH')
                        <input type="text" name="name" value="{{ $item->name }}" placeholder="Name" class="admin-input admin-input-sm" required>
                        <input type="text" name="description" value="{{ $item->description }}" placeholder="Description" class="admin-input admin-input-sm">
                        <input type="number" name="sort_order" value="{{ $item->sort_order }}" min="0" class="admin-input admin-input-sm admin-input-order">
                    </form>

                    {{-- Also display:contents — carries no visible fields of its own; its
                         Delete button lives in .admin-item-row-actions below and is tied back
                         to this form via the HTML `form` attribute, not DOM nesting. --}}
                    <form id="di-{{ $item->id }}-delete" method="POST" action="{{ route('admin.dining.items.destroy', [$dining, $item]) }}" class="admin-item-row-delete-shell">
                        @csrf
                        @method('DELETE')
                    </form>

                    <div class="admin-item-row-actions admin-item-col-actions">
                        <div class="admin-item-row-file">
                            {{-- Choosing a file submits the update form immediately (Name/Description/Sort
                                 above are saved together with the new photo) — no separate button for
                                 the photo swap itself. --}}
                            <input type="file" name="image" form="di-{{ $item->id }}-update" accept="image/*" class="admin-file-input-sm" title="Replace photo" onchange="this.form.submit()">
                        </div>
                        <div class="admin-item-row-save-delete">
                            <button type="submit" form="di-{{ $item->id }}-update" class="admin-btn admin-btn-outline-dark admin-btn-sm">Save</button>
                            <button type="submit" form="di-{{ $item->id }}-delete" class="admin-btn admin-btn-danger admin-btn-sm" onclick="return confirm('Delete this item?')">Delete</button>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    @else
        <p class="admin-empty">No items yet.</p>
    @endif

    <form method="POST" action="{{ route('admin.dining.items.store', $dining) }}" enctype="multipart/form-data" class="admin-upload-form">
        @csrf
        <div class="admin-field">
            <label class="admin-label">Name</label>
            <input type="text" name="name" class="admin-input" required>
            @error('name') <p class="admin-field-error">{{ $message }}</p> @enderror
        </div>
        <div class="admin-field">
            <label class="admin-label">Description</label>
            <input type="text" name="description" class="admin-input">
        </div>
        <div class="admin-field">
            <label class="admin-label">Photo</label>
            <input type="file" name="image" accept="image/*" class="admin-input">
            @error('image') <p class="admin-field-error">{{ $message }}</p> @enderror
        </div>
        <button type="submit" class="admin-btn admin-btn-solid">+ Add Item</button>
    </form>
</section>

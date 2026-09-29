{{--
    Reusable rate-plan CRUD table for one Room. $room->ratePlans is already scoped to this
    room via the hasMany relation. Same flattened-DOM pattern as Dining's Menu Highlights
    table: every visible field is a flat, direct child of .admin-item-row tied back to its
    owning <form> via the HTML `form="id"` attribute (not DOM nesting), so columns stay
    aligned with the header without needing a literal <table>.

    Props: $room (Room)
--}}
<section class="admin-room-images">
    <h2 class="admin-subheading-strong">Rate Plans ({{ $room->ratePlans->count() }})</h2>
    <p class="admin-hint">Prices are per unit, per night. Max Guests cannot exceed this room's own Max Guests ({{ $room->max_guests }}).</p>

    @if ($room->ratePlans->isNotEmpty())
        <div class="admin-item-table admin-item-table-grid admin-item-table-rate-plans">
            <div class="admin-item-row-header">
                <span>Name</span>
                <span>Guests</span>
                <span>Price/Night</span>
                <span>Compare-At</span>
                <span>Description</span>
                <span>Breakfast</span>
                <span>Active</span>
                <span class="admin-item-col-sort">Sort</span>
                <span class="admin-item-col-actions">Actions</span>
            </div>

            @foreach ($room->ratePlans as $plan)
                <div class="admin-item-row">
                    <input type="text" name="name" value="{{ $plan->name }}" placeholder="Name" form="rp-{{ $plan->id }}-update" class="admin-input admin-input-sm" required>
                    <input type="number" name="max_guests" value="{{ $plan->max_guests }}" min="1" max="{{ $room->max_guests }}" form="rp-{{ $plan->id }}-update" class="admin-input admin-input-sm">
                    <input type="number" name="price_per_night" value="{{ $plan->price_per_night }}" min="0" form="rp-{{ $plan->id }}-update" class="admin-input admin-input-sm">
                    <input type="number" name="compare_at_price_per_night" value="{{ $plan->compare_at_price_per_night }}" min="0" placeholder="—" form="rp-{{ $plan->id }}-update" class="admin-input admin-input-sm">
                    <input type="text" name="description" value="{{ $plan->description }}" placeholder="Package terms" form="rp-{{ $plan->id }}-update" class="admin-input admin-input-sm">

                    <div class="admin-item-row-checkbox">
                        <input type="checkbox" name="includes_breakfast" value="1" form="rp-{{ $plan->id }}-update" @checked($plan->includes_breakfast)>
                    </div>
                    <div class="admin-item-row-checkbox">
                        <input type="checkbox" name="is_active" value="1" form="rp-{{ $plan->id }}-update" @checked($plan->is_active)>
                    </div>

                    <input type="number" name="sort_order" value="{{ $plan->sort_order }}" min="0" form="rp-{{ $plan->id }}-update" class="admin-input admin-input-sm admin-input-order">

                    <div class="admin-item-row-actions admin-item-col-actions">
                        <div class="admin-item-row-save-delete">
                            <button type="submit" form="rp-{{ $plan->id }}-update" class="admin-btn admin-btn-outline-dark admin-btn-sm">Save</button>
                            <button type="submit" form="rp-{{ $plan->id }}-delete" class="admin-btn admin-btn-danger admin-btn-sm" onclick="return confirm('Delete this rate plan?')">Delete</button>
                        </div>
                    </div>

                    <form id="rp-{{ $plan->id }}-update" method="POST" action="{{ route('admin.rooms.rate-plans.update', [$room, $plan]) }}" class="admin-item-row-form">
                        @csrf
                        @method('PATCH')
                    </form>

                    <form id="rp-{{ $plan->id }}-delete" method="POST" action="{{ route('admin.rooms.rate-plans.destroy', [$room, $plan]) }}" class="admin-item-row-delete-shell">
                        @csrf
                        @method('DELETE')
                    </form>
                </div>
            @endforeach
        </div>
    @else
        <p class="admin-empty">No rate plans yet.</p>
    @endif

    <form method="POST" action="{{ route('admin.rooms.rate-plans.store', $room) }}" class="admin-upload-form">
        @csrf
        <div class="admin-form-row">
            <div class="admin-field">
                <label class="admin-label">Name</label>
                <input type="text" name="name" class="admin-input" required>
                @error('name') <p class="admin-field-error">{{ $message }}</p> @enderror
            </div>
            <div class="admin-field">
                <label class="admin-label">Max Guests</label>
                <input type="number" name="max_guests" min="1" max="{{ $room->max_guests }}" class="admin-input" required>
                @error('max_guests') <p class="admin-field-error">{{ $message }}</p> @enderror
            </div>
        </div>
        <div class="admin-form-row">
            <div class="admin-field">
                <label class="admin-label">Price / Night</label>
                <input type="number" name="price_per_night" min="0" class="admin-input" required>
                @error('price_per_night') <p class="admin-field-error">{{ $message }}</p> @enderror
            </div>
            <div class="admin-field">
                <label class="admin-label">Compare-At Price / Night <span class="admin-hint">(optional, must be higher than Price)</span></label>
                <input type="number" name="compare_at_price_per_night" min="0" class="admin-input">
                @error('compare_at_price_per_night') <p class="admin-field-error">{{ $message }}</p> @enderror
            </div>
        </div>
        <div class="admin-field">
            <label class="admin-label">Description <span class="admin-hint">(package terms — shown behind an info icon on the public page)</span></label>
            <input type="text" name="description" class="admin-input">
        </div>
        <div class="admin-checkbox-row">
            <label class="admin-checkbox">
                <input type="checkbox" name="includes_breakfast" value="1">
                Includes Breakfast
            </label>
            <label class="admin-checkbox">
                <input type="checkbox" name="is_active" value="1" checked>
                Active
            </label>
        </div>
        <button type="submit" class="admin-btn admin-btn-solid">+ Add Rate Plan</button>
    </form>
</section>

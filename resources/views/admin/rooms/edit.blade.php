@extends('layouts.admin')

@section('title', 'Edit Room')

@section('content')
    <x-admin.shell active="rooms">
        <div class="admin-page-header">
            <div>
                <h1 class="admin-heading">Edit Room</h1>
            </div>
            <a href="{{ route('admin.rooms.index') }}" class="admin-btn admin-btn-outline-dark">← Back to Rooms</a>
        </div>

        <form method="POST" action="{{ route('admin.rooms.update', $room) }}" enctype="multipart/form-data" class="admin-form admin-form-wide">
            @method('PUT')
            @include('admin.rooms._form', ['room' => $room])
        </form>

        <section class="admin-room-images">
            <h2 class="admin-subheading-strong">Room Images ({{ $room->images->count() }})</h2>
            <p class="admin-hint">Additional/detail photos shown alongside the main photo — e.g. the Family Room's extra detail shots.</p>

            @if ($room->images->isEmpty())
                <p class="admin-empty">No additional images yet.</p>
            @else
                <div class="admin-image-grid">
                    @foreach ($room->images as $image)
                        <div class="admin-image-card">
                            <img src="{{ \App\Support\CmsImage::url($image->image) }}" alt="{{ $image->alt_text }}">

                            <form method="POST" action="{{ route('admin.rooms.images.update', [$room, $image]) }}" class="admin-image-card-form">
                                @csrf
                                @method('PATCH')
                                <label class="admin-label-sm">Alt text</label>
                                <input type="text" name="alt_text" value="{{ $image->alt_text }}" class="admin-input admin-input-sm">
                                <label class="admin-label-sm">Order</label>
                                <input type="number" name="sort_order" value="{{ $image->sort_order }}" min="0" class="admin-input admin-input-sm admin-input-order">
                                <button type="submit" class="admin-btn admin-btn-outline-dark admin-btn-sm">Save</button>
                            </form>

                            <form method="POST" action="{{ route('admin.rooms.images.destroy', [$room, $image]) }}" onsubmit="return confirm('Delete this image?');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="admin-btn admin-btn-danger admin-btn-sm">Delete</button>
                            </form>
                        </div>
                    @endforeach
                </div>
            @endif

            <form method="POST" action="{{ route('admin.rooms.images.store', $room) }}" enctype="multipart/form-data" class="admin-upload-form">
                @csrf
                <div class="admin-field">
                    <label class="admin-label" for="images">Add Photos</label>
                    <input id="images" type="file" name="images[]" accept="image/*" multiple class="admin-input">
                    @error('images') <p class="admin-field-error">{{ $message }}</p> @enderror
                    @error('images.*') <p class="admin-field-error">{{ $message }}</p> @enderror
                </div>
                <div class="admin-field">
                    <label class="admin-label" for="alt_text">Alt Text (optional, applied to all photos uploaded in this batch)</label>
                    <input id="alt_text" type="text" name="alt_text" class="admin-input">
                </div>
                <button type="submit" class="admin-btn admin-btn-solid">Upload</button>
            </form>
        </section>

        @include('admin.rooms._rate-plans-table', ['room' => $room])
    </x-admin.shell>
@endsection

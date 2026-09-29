@extends('layouts.admin')

@section('title', 'Rooms')

@section('content')
    <x-admin.shell active="rooms">
        <div class="admin-page-header">
            <div>
                <h1 class="admin-heading">Rooms</h1>
            </div>
            <a href="{{ route('admin.rooms.create') }}" class="admin-btn admin-btn-solid">+ Add Room</a>
        </div>

        <div class="admin-table-wrap">
            <table class="admin-table">
                <thead>
                    <tr>
                        <th class="admin-table-col-xs">Photo</th>
                        <th>Room Name</th>
                        <th class="admin-table-col-md admin-table-right">Price</th>
                        <th class="admin-table-col-sm">Guests</th>
                        <th class="admin-table-col-sm">Status</th>
                        <th class="admin-table-col-md">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($rooms as $room)
                        <tr>
                            <td class="admin-table-col-xs admin-table-center"><img src="{{ \App\Support\CmsImage::url($room->image) }}" alt="{{ $room->name }}" class="admin-table-thumb"></td>
                            <td class="admin-table-name-cell">
                                <span class="admin-table-name">{{ $room->name }}</span>
                                @if ($room->is_featured)
                                    <span class="admin-badge admin-badge-accent">Featured</span>
                                @endif
                                <span class="admin-table-subtext">{{ $room->bedding }}</span>
                            </td>
                            <td class="admin-table-col-md admin-table-right">{{ $room->currency }}{{ number_format($room->rate_per_night) }}</td>
                            <td class="admin-table-col-sm admin-table-center">{{ $room->max_guests }}</td>
                            <td class="admin-table-col-sm admin-table-center">
                                @if ($room->is_active)
                                    <span class="admin-badge admin-badge-success">Active</span>
                                @else
                                    <span class="admin-badge admin-badge-muted">Inactive</span>
                                @endif
                            </td>
                            <td class="admin-table-col-md admin-table-center">
                                <div class="admin-table-actions">
                                    <a href="{{ route('admin.rooms.edit', $room) }}" class="admin-btn admin-btn-outline-dark admin-btn-sm">Edit</a>
                                    <form method="POST" action="{{ route('admin.rooms.destroy', $room) }}" onsubmit="return confirm('Delete this room? This also removes its detail photos.');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="admin-btn admin-btn-danger admin-btn-sm">Delete</button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="admin-empty">No rooms yet.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </x-admin.shell>
@endsection

@extends('layouts.admin')

@section('title', 'Events')

@section('content')
    <x-admin.shell active="events">
        <div class="admin-page-header">
            <div>
                <h1 class="admin-heading">Events</h1>
                <p class="admin-subheading">Manage the seasonal event banners shown on the Home page.</p>
            </div>
            <a href="{{ route('admin.events.create') }}" class="admin-btn admin-btn-solid">+ Add Event</a>
        </div>

        <div class="admin-table-wrap">
            <table class="admin-table">
                <thead>
                    <tr>
                        <th class="admin-table-col-xs">Image</th>
                        <th>Title</th>
                        <th class="admin-table-col-md">Display</th>
                        <th class="admin-table-col-sm">Status</th>
                        <th class="admin-table-col-sm">Order</th>
                        <th class="admin-table-col-md">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($events as $event)
                        @php
                            $display = match (true) {
                                $event->display_from && $event->display_until => $event->display_from->format('j M Y').' – '.$event->display_until->format('j M Y'),
                                (bool) $event->display_from => 'From '.$event->display_from->format('j M Y'),
                                (bool) $event->display_until => 'Until '.$event->display_until->format('j M Y'),
                                default => 'Always',
                            };
                            $status = $event->status();
                            $statusBadgeClass = match ($status) {
                                'Active' => 'admin-badge-success',
                                'Scheduled' => 'admin-badge-accent',
                                default => 'admin-badge-muted', // Ended, Inactive
                            };
                        @endphp
                        <tr>
                            <td>
                                @if ($event->background_image)
                                    <img src="{{ \App\Support\CmsImage::url($event->background_image) }}" alt="{{ $event->title }}" class="admin-table-thumb">
                                @else
                                    <span class="admin-table-thumb"></span>
                                @endif
                            </td>
                            <td class="admin-table-name-cell">
                                <span class="admin-table-name">{{ $event->title }}</span>
                                <span class="admin-table-subtext">{{ $event->label }}</span>
                            </td>
                            <td class="admin-table-center">{{ $display }}</td>
                            <td class="admin-table-center">
                                <span class="admin-badge {{ $statusBadgeClass }}">{{ $status }}</span>
                            </td>
                            <td class="admin-table-center">{{ $event->sort_order }}</td>
                            <td class="admin-table-col-md admin-table-center">
                                <div class="admin-table-actions">
                                    <a href="{{ route('admin.events.edit', $event) }}" class="admin-btn admin-btn-outline-dark admin-btn-sm">Edit</a>
                                    <form method="POST" action="{{ route('admin.events.destroy', $event) }}" onsubmit="return confirm('Delete this event?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="admin-btn admin-btn-danger admin-btn-sm">Delete</button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="admin-empty">
                                No events yet.<br>
                                <a href="{{ route('admin.events.create') }}" class="admin-btn admin-btn-solid admin-empty-cta">+ Add Event</a>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </x-admin.shell>
@endsection

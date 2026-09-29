@extends('layouts.admin')

@section('title', 'Activities')

@section('content')
    <x-admin.shell active="activities">
        <div class="admin-page-header">
            <div>
                <h1 class="admin-heading">Activities</h1>
            </div>
            <a href="{{ route('admin.activities.create') }}" class="admin-btn admin-btn-solid">+ Add Activity</a>
        </div>

        <div class="admin-table-wrap">
            <table class="admin-table">
                <thead>
                    <tr>
                        <th class="admin-table-col-xs">Photo</th>
                        <th>Name</th>
                        <th class="admin-table-col-md">Price</th>
                        <th class="admin-table-col-sm">Status</th>
                        <th class="admin-table-col-md">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($activities as $activity)
                        <tr>
                            <td>
                                @if ($activity->image)
                                    <img src="{{ \App\Support\CmsImage::url($activity->image) }}" alt="{{ $activity->name }}" class="admin-table-thumb">
                                @else
                                    <span class="admin-table-thumb"></span>
                                @endif
                            </td>
                            <td class="admin-table-name-cell">
                                <span class="admin-table-name">{{ $activity->name }}</span>
                                <span class="admin-table-subtext">
                                    @if ($activity->show_on_home) Home @endif
                                    @if ($activity->show_on_home && $activity->show_on_activity_page) · @endif
                                    @if ($activity->show_on_activity_page) Activity Page @endif
                                </span>
                            </td>
                            <td class="admin-table-center">{{ $activity->price_label ?: '—' }}</td>
                            <td class="admin-table-center">
                                @if ($activity->is_active)
                                    <span class="admin-badge admin-badge-success">Active</span>
                                @else
                                    <span class="admin-badge admin-badge-muted">Inactive</span>
                                @endif
                            </td>
                            <td class="admin-table-col-md admin-table-center">
                                <div class="admin-table-actions">
                                    <a href="{{ route('admin.activities.edit', $activity) }}" class="admin-btn admin-btn-outline-dark admin-btn-sm">Edit</a>
                                    <form method="POST" action="{{ route('admin.activities.destroy', $activity) }}" onsubmit="return confirm('Delete this activity?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="admin-btn admin-btn-danger admin-btn-sm">Delete</button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="admin-empty">No activities yet.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </x-admin.shell>
@endsection

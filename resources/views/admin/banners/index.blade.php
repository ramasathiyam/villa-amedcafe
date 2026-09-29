@extends('layouts.admin')

@section('title', 'Banner')

@section('content')
    <x-admin.shell active="banners">
        <h1 class="admin-heading">Banner</h1>
        <p class="admin-subheading">Each page has its own banner.</p>

        <div class="admin-table-wrap">
            <table class="admin-table">
                <thead>
                    <tr>
                        <th>Page</th>
                        <th class="admin-table-col-xs">Image</th>
                        <th>Heading</th>
                        <th class="admin-table-col-sm">Status</th>
                        <th class="admin-table-col-md">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($rows as $row)
                        <tr>
                            <td class="admin-table-name-cell">
                                <span class="admin-table-name">{{ $row['label'] }}</span>
                            </td>
                            <td>
                                @if ($row['banner'] && $row['banner']->background_image)
                                    <img src="{{ \App\Support\CmsImage::url($row['banner']->background_image) }}" alt="{{ $row['banner']->heading }}" class="admin-table-thumb">
                                @else
                                    <span class="admin-table-thumb"></span>
                                @endif
                            </td>
                            <td>{{ $row['banner']->heading ?? '—' }}</td>
                            <td class="admin-table-center">
                                @if ($row['banner']?->is_active)
                                    <span class="admin-badge admin-badge-success">Active</span>
                                @else
                                    <span class="admin-badge admin-badge-muted">Inactive</span>
                                @endif
                            </td>
                            <td class="admin-table-col-md admin-table-center">
                                @if ($row['banner'])
                                    <div class="admin-table-actions">
                                        <a href="{{ route('admin.banners.edit', $row['page']) }}" class="admin-btn admin-btn-outline-dark admin-btn-sm">Edit</a>
                                    </div>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </x-admin.shell>
@endsection

@extends('layouts.admin')

@section('title', 'Testimonials')

@section('content')
    <x-admin.shell active="testimonials">
        <div class="admin-page-header">
            <div>
                <h1 class="admin-heading">Testimonials</h1>
                <p class="admin-subheading">Manage guest testimonials displayed on the website.</p>
            </div>
            <a href="{{ route('admin.testimonials.create') }}" class="admin-btn admin-btn-solid">+ Add Testimonial</a>
        </div>

        <div class="admin-table-wrap">
            <table class="admin-table">
                <thead>
                    <tr>
                        <th>Guest</th>
                        <th>Quote</th>
                        <th class="admin-table-col-sm">Rating</th>
                        <th class="admin-table-col-md">Venue</th>
                        <th class="admin-table-col-sm">Status</th>
                        <th class="admin-table-col-md">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($testimonials as $testimonial)
                        <tr>
                            <td class="admin-table-name-cell">
                                <span class="admin-table-name">{{ $testimonial->name }}</span>
                            </td>
                            <td class="admin-table-wrap-cell">{{ \Illuminate\Support\Str::limit($testimonial->quote, 80) }}</td>
                            <td class="admin-table-center">{{ str_repeat('★', $testimonial->rating) }}</td>
                            <td class="admin-table-center">{{ $testimonial->venue->name ?? 'Global' }}</td>
                            <td class="admin-table-center">
                                @if ($testimonial->is_active)
                                    <span class="admin-badge admin-badge-success">Active</span>
                                @else
                                    <span class="admin-badge admin-badge-muted">Inactive</span>
                                @endif
                            </td>
                            <td class="admin-table-col-md admin-table-center">
                                <div class="admin-table-actions">
                                    <a href="{{ route('admin.testimonials.edit', $testimonial) }}" class="admin-btn admin-btn-outline-dark admin-btn-sm">Edit</a>
                                    <form method="POST" action="{{ route('admin.testimonials.destroy', $testimonial) }}" onsubmit="return confirm('Delete this testimonial?');">
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
                                No testimonials yet.<br>
                                Add your first guest testimonial to display it on the website.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </x-admin.shell>
@endsection

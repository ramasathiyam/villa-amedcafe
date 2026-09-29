@extends('layouts.admin')

@section('title', 'Edit Event')

@section('content')
    <x-admin.shell active="events">
        <div class="admin-page-header">
            <div>
                <h1 class="admin-heading">Edit Event</h1>
            </div>
            <a href="{{ route('admin.events.index') }}" class="admin-btn admin-btn-outline-dark">← Back to Events</a>
        </div>

        <form method="POST" action="{{ route('admin.events.update', $event) }}" enctype="multipart/form-data" class="admin-form admin-form-wide">
            @method('PUT')
            @include('admin.events._form', ['event' => $event])
        </form>
    </x-admin.shell>
@endsection

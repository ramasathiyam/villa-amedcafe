@extends('layouts.admin')

@section('title', 'Add Event')

@section('content')
    <x-admin.shell active="events">
        <div class="admin-page-header">
            <div>
                <h1 class="admin-heading">Add Event</h1>
            </div>
            <a href="{{ route('admin.events.index') }}" class="admin-btn admin-btn-outline-dark">← Back to Events</a>
        </div>

        <form method="POST" action="{{ route('admin.events.store') }}" enctype="multipart/form-data" class="admin-form admin-form-wide">
            @include('admin.events._form', ['event' => $event])
        </form>
    </x-admin.shell>
@endsection

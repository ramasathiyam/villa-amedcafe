@extends('layouts.admin')

@section('title', 'Add Room')

@section('content')
    <x-admin.shell active="rooms">
        <div class="admin-page-header">
            <div>
                <h1 class="admin-heading">Add Room</h1>
            </div>
            <a href="{{ route('admin.rooms.index') }}" class="admin-btn admin-btn-outline-dark">← Back to Rooms</a>
        </div>

        <form method="POST" action="{{ route('admin.rooms.store') }}" enctype="multipart/form-data" class="admin-form admin-form-wide">
            @include('admin.rooms._form', ['room' => $room])
        </form>
    </x-admin.shell>
@endsection

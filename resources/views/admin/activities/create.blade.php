@extends('layouts.admin')

@section('title', 'Add Activity')

@section('content')
    <x-admin.shell active="activities">
        <div class="admin-page-header">
            <div>
                <h1 class="admin-heading">Add Activity</h1>
            </div>
            <a href="{{ route('admin.activities.index') }}" class="admin-btn admin-btn-outline-dark">← Back to Activities</a>
        </div>

        <form method="POST" action="{{ route('admin.activities.store') }}" enctype="multipart/form-data" class="admin-form admin-form-wide">
            @include('admin.activities._form', ['activity' => $activity])
        </form>
    </x-admin.shell>
@endsection

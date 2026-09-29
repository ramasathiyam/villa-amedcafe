@extends('layouts.admin')

@section('title', 'Dashboard')

@section('content')
    <x-admin.shell active="dashboard">
        <h1 class="admin-heading">Welcome back, {{ auth()->user()->name }}</h1>
        <p class="admin-subheading">Ringkasan konten yang sedang aktif di website.</p>

        <div class="admin-stat-grid">
            <div class="admin-stat-card">
                <p class="admin-stat-label">Rooms</p>
                <p class="admin-stat-value">{{ $roomCount }}</p>
            </div>
            <div class="admin-stat-card">
                <p class="admin-stat-label">Activities</p>
                <p class="admin-stat-value">{{ $activityCount }}</p>
            </div>
            <div class="admin-stat-card">
                <p class="admin-stat-label">Dining Venues</p>
                <p class="admin-stat-value">{{ $diningVenueCount }}</p>
            </div>
            <div class="admin-stat-card">
                <p class="admin-stat-label">Dining Items</p>
                <p class="admin-stat-value">{{ $diningItemCount }}</p>
            </div>
            <div class="admin-stat-card">
                <p class="admin-stat-label">Testimonials</p>
                <p class="admin-stat-value">{{ $testimonialCount }}</p>
            </div>
            <div class="admin-stat-card">
                <p class="admin-stat-label">Events</p>
                <p class="admin-stat-value">{{ $eventCount }}</p>
            </div>
        </div>
    </x-admin.shell>
@endsection

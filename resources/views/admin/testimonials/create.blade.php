@extends('layouts.admin')

@section('title', 'Add Testimonial')

@section('content')
    <x-admin.shell active="testimonials">
        <div class="admin-page-header">
            <div>
                <h1 class="admin-heading">Add Testimonial</h1>
            </div>
            <a href="{{ route('admin.testimonials.index') }}" class="admin-btn admin-btn-outline-dark">← Back to Testimonials</a>
        </div>

        <form method="POST" action="{{ route('admin.testimonials.store') }}" class="admin-form admin-form-wide">
            @include('admin.testimonials._form', ['testimonial' => $testimonial, 'venues' => $venues])
        </form>
    </x-admin.shell>
@endsection

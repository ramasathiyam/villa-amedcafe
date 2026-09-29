@extends('layouts.admin')

@section('title', 'Edit Testimonial')

@section('content')
    <x-admin.shell active="testimonials">
        <div class="admin-page-header">
            <div>
                <h1 class="admin-heading">Edit Testimonial</h1>
            </div>
            <a href="{{ route('admin.testimonials.index') }}" class="admin-btn admin-btn-outline-dark">← Back to Testimonials</a>
        </div>

        <form method="POST" action="{{ route('admin.testimonials.update', $testimonial) }}" class="admin-form admin-form-wide">
            @method('PUT')
            @include('admin.testimonials._form', ['testimonial' => $testimonial, 'venues' => $venues])
        </form>
    </x-admin.shell>
@endsection

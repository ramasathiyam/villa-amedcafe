@extends('layouts.admin')

@section('title', 'Login')

@section('content')
    <div class="admin-auth-wrap">
        <div class="admin-auth-card">
            <div class="admin-auth-brand">
                <img src="{{ asset('images/logo/logo-white.png') }}" alt="Amed Café & Hotel Kebun Wayan" class="admin-auth-logo">
                <span class="admin-auth-eyebrow">Admin Access</span>
            </div>

            <h1 class="admin-auth-heading">Sign In</h1>

            @if ($errors->any())
                <div class="admin-alert" role="alert">
                    @foreach ($errors->all() as $error)
                        <p>{{ $error }}</p>
                    @endforeach
                </div>
            @endif

            <form method="POST" action="{{ route('admin.login.attempt') }}" class="admin-form">
                @csrf

                <div class="admin-field">
                    <label class="admin-label" for="email">Email</label>
                    <input id="email" class="admin-input" type="email" name="email" value="{{ old('email') }}" required autofocus>
                </div>

                <div class="admin-field">
                    <label class="admin-label" for="password">Password</label>
                    <input id="password" class="admin-input" type="password" name="password" required>
                </div>

                <button type="submit" class="admin-btn admin-btn-solid">Login</button>
            </form>
        </div>
    </div>
@endsection

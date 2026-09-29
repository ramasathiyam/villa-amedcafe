<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'Admin') — Amed Café & Hotel Kebun Wayan</title>
    <meta name="robots" content="noindex, nofollow">

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">

    @vite(['resources/css/admin.css'])
</head>
<body class="admin-body">
    @if (session('status'))
        <div class="admin-flash admin-flash-status">{{ session('status') }}</div>
    @endif

    @if (session('error'))
        <div class="admin-flash admin-flash-error">{{ session('error') }}</div>
    @endif

    @yield('content')
</body>
</html>

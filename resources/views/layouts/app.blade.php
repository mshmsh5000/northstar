<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>@yield('title', 'DoSomething.org')</title>

    <link rel="stylesheet" href="{{ asset('dist/app.css') }}">
    <script src="{{ asset('dist/modernizr.js') }}"></script>
    <meta name="csrf-token" content="{{ csrf_token() }}">
</head>

<body class="modernizr-no-js {{ $bodyClasses or '' }}">
<div class="chrome">
    @if (session('status'))
        <div class="messages">{{ session('status') }}</div>
    @endif
    <div class="wrapper">
        @section('navigation')
            @include('layouts.navigation')
        @show

        @yield('content')
    </div>
</div>
</body>

<script src="{{ asset('/dist/app.js') }}"></script>

</html>

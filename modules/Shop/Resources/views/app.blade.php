<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">
<head>
    <meta charset="utf-8">
    <link rel="icon" href="/storage/images/icon.png">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=400, initial-scale=1, maximum-scale=1">
    <title>@yield('title') - {{ config('app.name', 'Auth Module') }}</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link href="{{ mix('/css/auth.css') }}" rel="stylesheet">
</head>
<body class="noselect">
    <div id="app">
        @yield('content')
    </div>
    <script src="{{ mix('/js/auth.js') }}"></script>
</body>
</html>

<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">
  <head>
    <meta charset="utf-8" />
    <link rel="icon" href="/storage/images/icon.png" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <meta name="viewport" content="width=400, initial-scale=1, maximum-scale=1" />
    <title>@yield('title') - {{ config('app.name', 'Auth Module') }}</title>
    <meta name="csrf-token" content="{{ csrf_token() }}" />
    <link href="{{ mix('/css/auth.css') }}" rel="stylesheet" />
  </head>
  <body class="noselect">
    <div id="app">
      <div class="min-h-screen bg-blue-purple flex items-center justify-center py-12 px-4 sm:px-6 lg:px-8 select-none">
        <div class="max-w-sm w-full">
          <h1 class="text-4xl text-white font-light text-center uppercase">{{ config('app.name') }}</h1>
          @yield('content')
        </div>
      </div>
    </div>
    <script src="{{ mix('/js/auth.js') }}"></script>
  </body>
</html>

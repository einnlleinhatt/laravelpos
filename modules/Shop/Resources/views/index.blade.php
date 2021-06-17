@extends('shop::app') @section('title', 'Shop Module') @section('content')
<div class="min-h-screen bg-blue-purple flex items-center justify-center py-12 px-4 sm:px-6 lg:px-8 select-none">
  <div class="max-w-sm w-full">
    <div>
      <h1 class="text-4xl text-white font-light text-center uppercase">{{ config('app.name') }}</h1>
      <p class="mt-8 text-center text-sm leading-5 text-gray-500">
        @foreach($modules as $module) @if (!$loop->first) &nbsp;|&nbsp; @endif
        <a
          href="{{url($module->name == 'Auth' ? '/' : ($module->route ?? '/'))}}"
          class="font-bold text-yellow-400 hover:text-yellow-500 focus:outline-none focus:underline transition ease-in-out duration-150"
        >
          {{ $module->name == 'Auth' ? 'Home' : $module->name }}
        </a>
        @endforeach
      </p>
    </div>
  </div>
</div>
@endsection

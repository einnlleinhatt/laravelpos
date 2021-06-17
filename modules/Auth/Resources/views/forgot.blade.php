@extends('auth::app') @section('title', 'Forgot Password') @section('content')
<h2 class="mt-2 text-center leading-9 font-bold text-gray-400">
  {{ __('Get instructions to reset password') }}
</h2>
@include('auth::flash-message')
<form class="mt-4" action="/password/email" method="POST" autocomplete="off">
  @csrf
  <input type="hidden" name="remember" value="true" />
  <div class="rounded shadow-sm">
    <div>
      <input
        required
        type="email"
        name="email"
        placeholder="{{ __('Email Address') }}"
        class="border-gray-300 placeholder-gray-500 appearance-none rounded-none relative block w-full px-3 py-2 border text-gray-900 rounded-t focus:outline-none focus:shadow-outline-blue focus:border-blue-300 focus:z-10 sm:text-sm sm:leading-5"
      />
    </div>
    <button
      type="submit"
      class="w-full py-2 px-4 border border-transparent font-medium rounded-b text-white bg-yellow-600 hover:bg-yellow-500 focus:outline-none focus:border-yellow-700 focus:shadow-outline-yellow active:bg-yellow-700 transition duration-150 ease-in-out sm:text-sm sm:leading-5"
    >
      {{ __('Submit') }}
    </button>
  </div>
  <div class="mt-6"></div>
</form>
<p class="mt-8 text-center text-sm leading-5 text-gray-500">
  <a
    href="/auth/login"
    class="font-bold text-yellow-400 hover:text-yellow-500 focus:outline-none focus:underline transition ease-in-out duration-150"
  >
    {{ __('Login') }}
  </a>
  @foreach($modules as $module) &nbsp;|&nbsp;
  <a
    href="{{url($module->name == 'Auth' ? '/' : ($module->route ?? '/'))}}"
    class="font-bold text-yellow-400 hover:text-yellow-500 focus:outline-none focus:underline transition ease-in-out duration-150"
  >
    {{ $module->name == 'Auth' ? 'Home' : $module->name }}
  </a>
  @endforeach
</p>
@endsection

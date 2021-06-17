@extends('auth::app') @section('title', 'Forgot Password') @section('content')
<h2 class="mt-2 text-center leading-9 font-bold text-gray-400">
  {{ __('Reset your password') }}
</h2>
@include('auth::flash-message')
<form class="mt-4" action="/password/reset" method="POST" autocomplete="off">
  @csrf @php $segments = request()->segments(); $token = end($segments); @endphp
  <input type="hidden" name="token" value="{{ $token ?? '' }}" />
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
    <div>
      <input
        required
        type="password"
        name="password"
        style="margin-top:-1px;"
        placeholder="{{ __('New Password') }}"
        class="appearance-none rounded-none relative block w-full px-3 py-2 border border-b-0 border-gray-400 placeholder-gray-500 text-gray-900 rounded-t focus:border-blue-300 focus:outline-none focus:shadow-outline-blue focus:z-10 sm:text-sm sm:leading-5"
      />
    </div>
    <div>
      <input
        required
        type="password"
        name="password_confirmation"
        placeholder="{{ __('Confirm Password') }}"
        class="border-gray-300 placeholder-gray-500 appearance-none rounded-none relative block w-full px-3 py-2 border text-gray-900 rounded-b focus:outline-none focus:shadow-outline-blue focus:border-blue-300 focus:z-10 sm:text-sm sm:leading-5"
      />
    </div>
    <button
      type="submit"
      class="w-full mt-6 py-2 px-4 border border-transparent text-sm leading-5 font-medium rounded text-white bg-yellow-600 hover:bg-yellow-500 focus:outline-none focus:border-yellow-700 focus:shadow-outline-yellow active:bg-yellow-700 transition duration-150 ease-in-out"
    >
      {{ __('Reset password') }}
    </button>
  </div>
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

<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">
  <head>
    <meta charset="utf-8">
    <title>{{ config('app.name', 'Laravel') }}</title>
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    @yield('head-link')
  </head>
  <body>
    @include('includes/include-search-wrapper')
    <div class="site-wrapper" id="site-wrapper"> <!-- Body -->
      @include('includes/include-top-menu')
      @include('includes/include-header')
      @include('includes/include-sub-header')
      <main class="site-content" role="main">

        @yield('content')

      </main>
    </div>
    @yield('footer-scripts')
  </body>
</html>

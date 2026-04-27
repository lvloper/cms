<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="frontend">
  @include('components.common.top')
  <body class="overflow-hidden font-sans text-base tracking-normal leading-normal text-gray-800">

      <div id="main">
        {{ $slot }}
      </div>
      
  </body>
  @include('components.common.bottom')
</html>

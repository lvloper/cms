@if(isset($notLayout) && $notLayout)
{{ $slot }}
@else
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="frontend">
@include('components.common.top')

<body class="font-sans text-base tracking-normal leading-normal text-gray-800 ">

  <x-common.header />

  @php
  $index = isset($index) ? $index : false;
  @endphp

  <div id="main" class="{{ $index ? 'has-sidebar' : '' }}">

    @if($index)
    <x-common.sidebar />
    @endif

    @if($isModal ?? false)
    <div class="z-10 bg-white main-content">
      {!! $parentView !!}
    </div>
    @else
    <div class="z-10 bg-white main-content">
      {{ $slot }}
    </div>
    @endif


  </div>

  <x-common.footer />



  @if($isModal ?? false)
  <x-modal size="xl" open ref="page-modal" :backUrl="$parentUrl ?? null">
    {{ $slot }}
  </x-modal>
  @endif
  @include('components.common.bottom')
  <x-common.edit-resource-button />
</body>

</html>

@endisset

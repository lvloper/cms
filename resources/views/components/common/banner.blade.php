@props(['location', 'gap' => '4'])

@php
    $banners = \App\Models\Banner::where('location', $location)->where('status', 'published')->get();  
@endphp
@if($banners->count() > 0)
<div class="flex flex-col gap-{{ $gap }} w-full">
    @foreach($banners as $banner)
        @if($banner->image)
            <x-link :attrs="$banner->route">
                <x-image :image="$banner->image" />
            </x-link>
        @endif
    @endforeach
</div>
@endif
@props(['style' => 'full', 'image', 'imageMobile', 'caption'])

<x-block class="{{ isset($preview) ? 'min-h-[300px]' : '' }}">
    @if($style == 'full')
    <x-image :image="$image" :imageMobile="$image_mobile" :caption="$caption" />
    @else
    <div class="container {{ $style == 'compact' ? 'py-12 max-w-[950px]' : '' }}">
        <x-image :image="$image" :imageMobile="$image_mobile" :caption="$caption" />
    </div>
    @endif
</x-block>
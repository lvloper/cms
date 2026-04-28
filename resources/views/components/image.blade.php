@php
// @var string|array $image Espera la respuesta de App\Filament\Forms\Components\Image
// @var string|array $src Espera un string con la ruta de la imagen
// @var bool $background Si es true, crea un div con la imagen de fondo en lugar de un <img>

$default = $default ?? false;
if(isset($src)) {
$processedImages = [$src];
$processedImagesMobile = [$src];
} else {
$processedImages = [];
$processedImagesMobile = [];

if (isset($image)) {
if (is_array($image)) {
$processedImages = array_values($image);

// add storage url
$processedImages = array_map(function($img) {
if(str_starts_with($img, 'http')) {
return $img;
} else {
return Storage::url($img);
}
}, $processedImages);
} elseif (is_string($image)) {
if(str_starts_with($image, 'http')) {
$processedImages[] = $image;
} else {
$processedImages[] = Storage::url($image);
}
}
}

if (isset($imageMobile)) {
if (is_array($imageMobile)) {
$processedImagesMobile = array_values($imageMobile);
} elseif (is_string($imageMobile) && str_starts_with($imageMobile, 'http')) {
$processedImagesMobile[] = $imageMobile;
} elseif (is_string($imageMobile)) {
$processedImagesMobile[] = Storage::url($imageMobile);
}
}

// Si no hay imágenes procesadas, usa la imagen default
if ($default && empty($processedImages)) {
$processedImages = [$default];
}
}
@endphp

@if(isset($background) && $background)
@if(count($processedImagesMobile) > 0)
@foreach($processedImagesMobile as $img)
<div class="{{ $class ?? '' }} @if(count($processedImagesMobile) > 0) md:hidden @endif"
    style="background-image: url('{{ $img }}'); {{ isset($imageClass) ? $imageClass : 'background-size: cover; background-position: center; background-repeat: no-repeat' }}"
    role="img" @if(isset($alt) || isset($caption)) aria-label="{{ $alt ?? $caption ?? '' }}" @endif>
    {{ $slot ?? '' }}
    @if(isset($caption))
    <div class="px-4 py-2 bg-gray-200">{{ $caption }}</div>
    @endif
</div>
@endforeach
@endif

@if(count($processedImages) > 0)
@foreach($processedImages as $img)
<div class="{{ $class ?? '' }} {{ count($processedImagesMobile) > 0 ? 'hidden md:block' : '' }}"
    style="background-image: url('{{ $img }}'); {{ isset($imageClass) ? $imageClass : 'background-size: cover; background-position: center; background-repeat: no-repeat' }}"
    role="img" @if(isset($alt) || isset($caption)) aria-label="{{ $alt ?? $caption ?? '' }}" @endif>
    {{ $slot ?? '' }}
    @if(isset($caption))
    <div class="px-6 py-4 bg-gray-200">{{ $caption }}</div>
    @endif
</div>
@endforeach
@endif
@else
@if(count($processedImagesMobile) > 0)
@foreach($processedImagesMobile as $img)
<figure class="{{ $class ?? '' }} @if(count($processedImagesMobile) > 0) md:hidden @endif">
    <img src="{{ $img }}" alt="{{ $alt ?? $caption ?? '' }}"
        class="{{ $imageClass ?? 'object-' . ($fit ?? 'contain') . ' w-full h-full' }}">
    @if(isset($caption))
    <figcaption class="px-4 py-2 bg-gray-200">{{ $caption }}</figcaption>
    @endif
</figure>
@endforeach
@endif

@if(count($processedImages) > 0)
@foreach($processedImages as $img)
<figure class="{{ $class ?? '' }} {{ count($processedImagesMobile) > 0 ? 'hidden md:block' : '' }}">
    <img src="{{ $img }}" alt="{{ $alt ?? $caption ?? '' }}"
        class="{{ $imageClass ?? 'object-' . ($fit ?? 'contain') . ' w-full h-full' }}">
    @if(isset($caption))
    <figcaption class="px-6 py-4 bg-gray-200">{{ $caption }}</figcaption>
    @endif
</figure>
@endforeach
@endif
@endif

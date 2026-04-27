{{-- Block Render Content Component --}}
@props(['item'])

@php
$block = $item->getRawState();
$uid = uniqid();

$hidden = $block['hidden'] ?? false;
$mb = $block['mb'] ?? "";
$mdMb = $block['mdMb'] ?? "";
$clases = $block['clases'] ?? [];
$styles = $block['styles'] ?? [];
$stylesMd = $block['stylesMd'] ?? [];
$allClasses = implode(' ', array_merge([$mb, $mdMb], $clases));

$styleString = '';

if ($styles) {
    $styleString .= '<style>';
    foreach ($styles as $key => $value) {
        $styleString .= "#b{$uid} { {$key}: {$value}; } ";
    }
    $styleString .= '</style>';
}

if ($stylesMd) {
    $styleString .= '<style>@media (min-width: 768px) {';
    foreach ($stylesMd as $key => $value) {
        $styleString .= "#b{$uid} { {$key}: {$value}; } ";
    }
    $styleString .= '}</style>';
}
@endphp

<div id="b{{$uid}}" class="block block-preview relative {{ $allClasses }}">
    @if ($hidden)
    <div class="block-hidden">
        <span class="block-hidden-text">{{ __('Este bloque se encuentra oculto') }}</span>
    </div>
    @endif
    
    @php
        $data = $item->getRawState();
        $data['id'] = 'block-'.$uid;
        $data['preview'] = true;
    @endphp
    
    {!! str_replace('="images', '="/storage/images', $item->getParentComponent()->renderPreview($data)) !!}

    {!! $styleString !!}

    <div class="clear-both"></div>
</div>

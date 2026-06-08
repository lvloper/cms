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

<div id="b{{$uid}}" class="block block-preview {{ $allClasses }}" style="display: block; position: relative;">
    @if ($hidden)
    <div class="fi-visual-editor__hidden-block">
        <span>{{ __('Este bloque se encuentra oculto') }}</span>
    </div>
    @endif
    
    @php
        $data = $item->getRawState();
        $data['id'] = 'block-'.$uid;
        $data['preview'] = true;
    @endphp
    
    {!! '<style>.block-entrance{opacity:1!important;transform:none!important;}</style>' . str_replace('="images', '="/storage/images', $item->getParentComponent()->renderPreview($data)) !!}

    {!! $styleString !!}

    <div class="clear-both"></div>
</div>

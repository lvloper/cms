@php
    // Determinar las clases según el estilo seleccionado
    $buttonClasses = match($style ?? 'primary') {
        'link' => 'text-primary hover:underline font-semibold',
        'primary' => 'px-6 py-3 bg-primary hover:bg-primary-hover text-white font-semibold uppercase transition-colors',
        'secondary' => 'px-6 py-3 bg-secondary hover:bg-secondary-hover text-white font-semibold uppercase transition-colors',
        default => 'px-6 py-3 bg-primary hover:bg-primary-hover text-white font-semibold uppercase transition-colors'
    };

    // Determinar el tamaño
    $sizeClasses = match($size ?? 'md') {
        'sm' => 'text-sm px-4 py-2',
        'md' => 'text-base px-6 py-3',
        'lg' => 'text-lg px-8 py-4',
        default => 'text-base px-6 py-3'
    };

    // Determinar la alineación
    $alignmentClasses = match($alignment ?? 'center') {
        'left' => 'justify-start',
        'center' => 'justify-center',
        'right' => 'justify-end',
        default => 'justify-center'
    };

    // Combinar clases (si es link, no aplicar tamaño de padding)
    if ($style === 'link') {
        $finalClasses = $buttonClasses;
    } else {
        $finalClasses = $buttonClasses . ' ' . $sizeClasses;
    }
@endphp

<x-block>
    <div class="flex {{ $alignmentClasses }}">
        <x-link
            :attrs="$route ?? []"
            class="{{ $finalClasses }}"
            allowWireNavitage>
            {{ $route['label'] ?? 'Botón' }}
        </x-link>
    </div>
</x-block>

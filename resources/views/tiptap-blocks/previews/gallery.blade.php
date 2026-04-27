<div>
    <div style="padding: 0.5rem; border: 1px solid #e5e7eb; border-radius: 0.375rem; background-color: #f9fafb;">

        <div style="display: grid; grid-template-columns: repeat(4, 1fr); gap: 0.5rem;">
            @if(is_array($images))
                @foreach(array_slice($images, 0, 4) as $image)
                    <div style="aspect-ratio: 4/3; background-color: #e5e7eb; border-radius: 0.375rem; overflow: hidden;">
                        <img src="{{ Storage::url($image) }}" alt="" style="width: 100%; height: 100%; object-fit: cover;">
                    </div>
                @endforeach
            @else
                <div style="grid-column: span 4; text-align: center; color: #9ca3af; font-size: 0.875rem; padding: 1rem 0;">
                    Sin imágenes seleccionadas
                </div>
            @endif
        </div>

        @if(is_array($images) && count($images) > 4)
            <div style="text-align: center; color: #6b7280; font-size: 0.875rem; margin-top: 0.5rem;">
                +{{ count($images) - 4 }} más
            </div>
        @endif
    </div>
</div>

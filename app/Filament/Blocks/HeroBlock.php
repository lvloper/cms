<?php

namespace App\Filament\Blocks;

use App\Filament\Forms\Components\Field;

class HeroBlock extends PageBlock
{
    protected const NAME = 'Hero';

    protected const CATEGORY = 'Contenido';

    protected const LABEL = 'Hero';

    protected static function fields(): array
    {
        return [
            Field::text('title', 'Título')->required(),
            Field::textarea('subtitle', 'Bajada')->rows(3),
            Field::text('buttonText', 'Texto del botón'),
            Field::route('buttonLink', 'Enlace del botón'),
        ];
    }
}

<?php

namespace App\Filament\Blocks;

use App\Filament\Forms\Components\Field;

class SearchBlock extends PageBlock
{
    protected const NAME = 'Search';

    protected const LABEL = 'Búsqueda';

    protected const CATEGORY = 'Contenido';

    protected static function fields(): array
    {
        return [
            Field::text('title', 'Título')->default('Buscar'),
            Field::textarea('description', 'Descripción')
                ->default('Buscá en el sitio lo que necesitás.')
                ->rows(2),
        ];
    }
}

<?php

namespace App\Filament\Blocks;

use App\Filament\Forms\Components\Field;

class BaseQuoteBlock extends PageBlock
{
    protected const NAME = 'BaseQuote';

    protected const LABEL = 'Base: cita';

    protected static function fields(): array
    {
        return [
            Field::textarea('quote', 'Cita')->rows(4)->required(),
            Field::text('author', 'Autor/a'),
            Field::text('source', 'Cargo o fuente'),
            Field::image('image', 'Imagen', '640', '640'),
        ];
    }
}

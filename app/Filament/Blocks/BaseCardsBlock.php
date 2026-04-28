<?php

namespace App\Filament\Blocks;

use App\Filament\Forms\Components\Field;

class BaseCardsBlock extends PageBlock
{
    protected const NAME = 'BaseCards';

    protected const LABEL = 'Base: cards';

    protected static function fields(): array
    {
        return [
            Field::text('title', 'Título'),
            Field::rich('description', 'Descripción'),
            Field::repeater('items', 'Cards', [
                Field::text('title', 'Título')->required(),
                Field::textarea('description', 'Descripción')->rows(3),
                Field::image('image', 'Imagen', '800', '600'),
                Field::route('route')->allowAnchor(),
            ])
                ->addActionLabel('Agregar card')
                ->columns(1)
                ->defaultItems(3),
        ];
    }
}

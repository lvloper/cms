<?php

namespace App\Filament\Blocks;

use App\Filament\Forms\Components\Field;

class BaseLinkListBlock extends PageBlock
{
    protected const NAME = 'BaseLinkList';

    protected const LABEL = 'Base: lista de enlaces';

    protected static function fields(): array
    {
        return [
            Field::text('title', 'Título'),
            Field::rich('description', 'Descripción'),
            Field::repeater('items', 'Enlaces', [
                Field::route('route')
                    ->buttonLabel()
                    ->allowAnchor(),
                Field::textarea('description', 'Descripción')->rows(2),
            ])
                ->addActionLabel('Agregar enlace')
                ->defaultItems(3),
        ];
    }
}

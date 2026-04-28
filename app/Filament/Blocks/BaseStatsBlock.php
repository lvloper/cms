<?php

namespace App\Filament\Blocks;

use App\Filament\Forms\Components\Field;

class BaseStatsBlock extends PageBlock
{
    protected const NAME = 'BaseStats';

    protected const LABEL = 'Base: métricas';

    protected static function fields(): array
    {
        return [
            Field::text('title', 'Título'),
            Field::rich('description', 'Descripción'),
            Field::repeater('items', 'Métricas', [
                Field::text('value', 'Número')->required(),
                Field::text('label', 'Etiqueta')->required(),
                Field::textarea('description', 'Descripción')->rows(2),
            ])
                ->addActionLabel('Agregar métrica')
                ->columns(3)
                ->defaultItems(3),
        ];
    }
}

<?php

namespace App\Filament\Blocks;

use App\Filament\Forms\Components\Field;

class BaseCtaBlock extends PageBlock
{
    protected const NAME = 'BaseCta';

    protected const LABEL = 'Base: llamada a la acción';

    protected static function fields(): array
    {
        return [
            Field::text('eyebrow', 'Volanta'),
            Field::text('title', 'Título')->required(),
            Field::rich('description', 'Descripción'),
            Field::route('primary_route', 'Botón principal')
                ->buttonLabel()
                ->allowAnchor(),
            Field::route('secondary_route', 'Botón secundario')
                ->buttonLabel()
                ->allowAnchor(),
            Field::toggleButtons('variant', 'Variante', [
                'light' => 'Clara',
                'dark' => 'Oscura',
                'accent' => 'Destacada',
            ])
                ->default('accent')
                ->inline()
                ->required(),
        ];
    }
}

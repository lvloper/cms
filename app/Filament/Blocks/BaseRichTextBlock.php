<?php

namespace App\Filament\Blocks;

use App\Filament\Forms\Components\Field;

class BaseRichTextBlock extends PageBlock
{
    protected const NAME = 'BaseRichText';

    protected const LABEL = 'Base: texto enriquecido';

    protected static function fields(): array
    {
        return [
            Field::text('eyebrow', 'Volanta'),
            Field::text('title', 'Título'),
            Field::rich('content', 'Contenido', 'avanced')->required(),
            Field::toggleButtons('width', 'Ancho', [
                'narrow' => 'Angosto',
                'container' => 'Contenedor',
                'wide' => 'Amplio',
            ])
                ->default('container')
                ->inline()
                ->required(),
        ];
    }
}

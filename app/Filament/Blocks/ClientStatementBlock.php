<?php

namespace App\Filament\Blocks;

use App\Filament\Forms\Components\Field;
use App\Filament\Forms\Components\MediaPicker;
use Filament\Forms\Components\Select;

class ClientStatementBlock extends PageBlock
{
    protected const NAME = 'ClientStatement';

    protected const CATEGORY = 'Cliente';

    protected const LABEL = 'Cliente: declaración editorial';

    protected static function fields(): array
    {
        return [
            Field::text('eyebrow', 'Volanta')->maxLength(100),
            Field::text('title', 'Declaración principal')->required()->maxLength(220),
            Field::rich('body', 'Texto')->columnSpanFull(),
            Select::make('layout')
                ->label('Ubicación del texto')
                ->options([
                    'text_left' => 'Texto a la izquierda',
                    'text_right' => 'Texto a la derecha',
                ])
                ->default('text_left')
                ->required()
                ->native(false),
            ...MediaPicker::make(directory: 'media/clients/statements', label: 'Imagen o video de apoyo'),
        ];
    }
}

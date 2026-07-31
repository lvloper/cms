<?php

namespace App\Filament\Blocks;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TagsInput;

class ClientMarqueeBlock extends PageBlock
{
    protected const NAME = 'ClientMarquee';

    protected const CATEGORY = 'Cliente';

    protected const LABEL = 'Cliente: frases en movimiento';

    protected static function fields(): array
    {
        return [
            TagsInput::make('items')
                ->label('Frases')
                ->helperText('Agrega entre 3 y 6 frases largas relacionadas con el trabajo y el acompañamiento.')
                ->required(),
            Select::make('speed')
                ->label('Velocidad')
                ->options([
                    'slow' => 'Lenta',
                    'medium' => 'Media',
                ])
                ->default('slow')
                ->required(),
            Select::make('direction')
                ->label('Dirección')
                ->options([
                    'left' => 'Hacia la izquierda',
                    'right' => 'Hacia la derecha',
                ])
                ->default('left')
                ->required(),
        ];
    }
}

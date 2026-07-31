<?php

namespace App\Filament\Blocks;

use App\Filament\Forms\Components\Field;
use App\Filament\Forms\Components\MediaPicker;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\TextInput;

class ClientClosingBlock extends PageBlock
{
    protected const NAME = 'ClientClosing';

    protected const CATEGORY = 'Cliente';

    protected const LABEL = 'Cliente: cierre multimedia';

    protected static function fields(): array
    {
        return [
            Field::text('eyebrow', 'Volanta')->maxLength(100),
            Field::text('title', 'Título')->required()->maxLength(200),
            Field::textarea('body', 'Texto')->rows(3)->maxLength(500),
            Repeater::make('media')
                ->label('Mosaico multimedia')
                ->schema([
                    TextInput::make('label')->label('Nombre interno')->maxLength(100),
                    ...MediaPicker::make(directory: 'media/clients/closing'),
                ])
                ->minItems(1)
                ->maxItems(8)
                ->columns(2)
                ->reorderableWithButtons()
                ->cloneable()
                ->itemLabel(fn (array $state): string => $state['label'] ?? 'Nueva pieza')
                ->columnSpanFull(),
        ];
    }
}

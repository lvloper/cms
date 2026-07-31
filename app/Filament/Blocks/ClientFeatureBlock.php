<?php

namespace App\Filament\Blocks;

use App\Filament\Forms\Components\Field;
use App\Filament\Forms\Components\MediaPicker;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;

class ClientFeatureBlock extends PageBlock
{
    protected const NAME = 'ClientFeature';

    protected const CATEGORY = 'Cliente';

    protected const LABEL = 'Cliente: narrativa multimedia';

    protected static function fields(): array
    {
        return [
            Field::text('eyebrow', 'Volanta')->maxLength(100),
            Field::text('title', 'Título')->required()->maxLength(180),
            Field::rich('body', 'Texto')->columnSpanFull(),
            Field::textarea('outcome', 'Valor o resultado destacado')->rows(2)->maxLength(400),
            Select::make('layout')
                ->label('Ubicación del texto')
                ->options([
                    'text_left' => 'Texto a la izquierda',
                    'text_right' => 'Texto a la derecha',
                ])
                ->default('text_left')
                ->required()
                ->native(false),
            Repeater::make('media')
                ->label('Secuencia multimedia')
                ->schema([
                    TextInput::make('label')->label('Nombre interno')->maxLength(100),
                    ...MediaPicker::make(directory: 'media/clients/features'),
                    TextInput::make('caption')->label('Epígrafe')->maxLength(220),
                ])
                ->minItems(1)
                ->maxItems(4)
                ->columns(2)
                ->reorderableWithButtons()
                ->cloneable()
                ->itemLabel(fn (array $state): string => $state['label'] ?? 'Nueva escena')
                ->columnSpanFull(),
        ];
    }
}

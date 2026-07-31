<?php

namespace App\Filament\Blocks;

use App\Filament\Forms\Components\Field;
use App\Filament\Forms\Components\MediaPicker;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;

class ClientMetricsBlock extends PageBlock
{
    protected const NAME = 'ClientMetrics';

    protected const CATEGORY = 'Cliente';

    protected const LABEL = 'Cliente: métricas y escala';

    protected static function fields(): array
    {
        return [
            Field::text('eyebrow', 'Volanta')->maxLength(100),
            Field::text('title', 'Título')->required()->maxLength(180),
            Field::textarea('body', 'Texto')->rows(3)->maxLength(500),
            Select::make('layout')
                ->label('Ubicación del texto')
                ->options([
                    'text_left' => 'Texto a la izquierda',
                    'text_right' => 'Texto a la derecha',
                ])
                ->default('text_left')
                ->required()
                ->native(false),
            Repeater::make('metrics')
                ->label('Métricas')
                ->schema([
                    Field::text('value', 'Valor')->required()->maxLength(50),
                    Field::text('label', 'Descripción')->required()->maxLength(140),
                    Field::text('note', 'Nota o estado')->maxLength(180),
                ])
                ->minItems(1)
                ->maxItems(4)
                ->columns(3)
                ->reorderableWithButtons()
                ->cloneable()
                ->itemLabel(fn (array $state): string => $state['value'] ?? 'Nueva métrica')
                ->columnSpanFull(),
            ...MediaPicker::make(directory: 'media/clients/metrics', label: 'Imagen o video de contexto'),
        ];
    }
}

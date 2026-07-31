<?php

namespace App\Filament\Blocks;

use App\Filament\Forms\Components\Field;
use Filament\Forms\Components\Repeater;

class ClientProcessBlock extends PageBlock
{
    protected const NAME = 'ClientProcess';

    protected const CATEGORY = 'Cliente';

    protected const LABEL = 'Cliente: mapa de procesos';

    protected static function fields(): array
    {
        return [
            Field::text('eyebrow', 'Volanta')->maxLength(100),
            Field::text('title', 'Título')->required()->maxLength(180),
            Field::textarea('body', 'Texto')->rows(3)->maxLength(500),
            Repeater::make('nodes')
                ->label('Nodos del sistema')
                ->schema([
                    Field::text('label', 'Nombre')->required()->maxLength(100),
                    Field::textarea('detail', 'Detalle opcional')->rows(2)->maxLength(220),
                ])
                ->minItems(2)
                ->maxItems(12)
                ->columns(2)
                ->reorderableWithButtons()
                ->cloneable()
                ->itemLabel(fn (array $state): string => $state['label'] ?? 'Nuevo nodo')
                ->columnSpanFull(),
        ];
    }
}

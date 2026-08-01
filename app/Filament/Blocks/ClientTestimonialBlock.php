<?php

namespace App\Filament\Blocks;

use App\Filament\Forms\Components\Field;
use Filament\Forms\Components\Repeater;

class ClientTestimonialBlock extends PageBlock
{
    protected const NAME = 'ClientTestimonial';

    protected const CATEGORY = 'Cliente';

    protected const LABEL = 'Cliente: testimonios';

    protected static function fields(): array
    {
        return [
            Field::text('eyebrow', 'Volanta')->maxLength(100),
            Field::text('title', 'Título')->maxLength(180),
            Repeater::make('testimonials')
                ->label('Testimonio')
                ->schema([
                    Field::textarea('quote', 'Cita')->required()->rows(5)->maxLength(1200),
                    Field::text('person', 'Nombre')->required()->maxLength(120),
                    Field::text('role', 'Cargo')->required()->maxLength(180),
                ])
                ->minItems(1)
                ->maxItems(1)
                ->columns(2)
                ->itemLabel(fn (array $state): string => $state['person'] ?? 'Nuevo testimonio')
                ->columnSpanFull(),
        ];
    }
}

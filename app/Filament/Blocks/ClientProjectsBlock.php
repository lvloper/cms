<?php

namespace App\Filament\Blocks;

use App\Filament\Forms\Components\Field;
use App\Filament\Forms\Components\MediaPicker;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\TagsInput;

class ClientProjectsBlock extends PageBlock
{
    protected const NAME = 'ClientProjects';

    protected const CATEGORY = 'Cliente';

    protected const LABEL = 'Cliente: proyectos seleccionados';

    protected static function fields(): array
    {
        return [
            Field::text('eyebrow', 'Volanta')->maxLength(100),
            Field::text('title', 'Título')->required()->maxLength(180),
            Field::textarea('intro', 'Introducción')->rows(3)->maxLength(500),
            Repeater::make('projects')
                ->label('Proyectos')
                ->schema([
                    Field::text('eyebrow', 'Tipo o contexto')->maxLength(100),
                    Field::text('title', 'Título')->required()->maxLength(160),
                    Field::textarea('summary', 'Descripción')->required()->rows(3)->maxLength(500),
                    TagsInput::make('tags')->label('Capacidades o etiquetas'),
                    ...MediaPicker::make(directory: 'media/clients/projects'),
                ])
                ->minItems(1)
                ->maxItems(3)
                ->columns(2)
                ->reorderableWithButtons()
                ->cloneable()
                ->itemLabel(fn (array $state): string => $state['title'] ?? 'Nuevo proyecto')
                ->columnSpanFull(),
        ];
    }
}

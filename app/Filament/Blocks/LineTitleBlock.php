<?php

namespace App\Filament\Blocks;

use App\Filament\Traits\BlockComposer;
use Filament\Forms\Components as Form;
use App\Filament\Traits\FormShortcuts;
use Filament\Forms\Components\ToggleButtons;

class LineTitleBlock
{
    use BlockComposer;
    use FormShortcuts;
    public static function compose(): array
    {
        $name = 'LineTitle';

        $label = 'Bloque de titulo';

        $schema = [
            FormShortcuts::Input(name: 'title')
                ->label('Título con linea'),
            ToggleButtons::make('size')->label('Tamaño')
                ->inline()
                ->default('xl')
                ->options([
                    'md' => 'Pequeño',
                    'lg' => 'Mediano',
                    'xl' => 'Normal',
                    '2xl' => 'Grande',
                    '3xl' => 'Muy grande',
                ]),
            ToggleButtons::make('color')->label('Color')
                ->inline()
                ->default('black')
                ->options([
                    'black' => 'Negro',
                    'primary' => 'Rojo',
                    'secondary' => 'Violeta',
                ])
        ];

        return compact('name', 'label', 'schema');
    }
}

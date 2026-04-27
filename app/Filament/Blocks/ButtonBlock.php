<?php

namespace App\Filament\Blocks;

use App\Filament\Traits\BlockComposer;
use App\Filament\Traits\FormShortcuts;
use Filament\Forms\Components as Form;

class ButtonBlock
{
    use BlockComposer;

    public static function compose(): array
    {
        // Define the name of the block
        $name = 'Button';

        // Define the label of the block
        $label = 'Botón';

        // Define the fields for the block
        $schema = [
            FormShortcuts::RoutePicker(
                name: 'route',
                btnLabel: true,
                required: true
            )->label('Botón'),

            Form\ToggleButtons::make('style')
                ->options([
                    'link' => 'Link',
                    'primary' => 'Rojo',
                    'secondary' => 'Violeta'
                ])
                ->default('primary')
                ->inline()
                ->label('Estilo del botón')
                ->required(),

            Form\ToggleButtons::make('size')
                ->options([
                    'sm' => 'Pequeño',
                    'md' => 'Mediano',
                    'lg' => 'Grande'
                ])
                ->default('md')
                ->inline()
                ->label('Tamaño')
                ->required(),

            Form\ToggleButtons::make('alignment')
                ->options([
                    'left' => 'Izquierda',
                    'center' => 'Centro',
                    'right' => 'Derecha'
                ])
                ->default('center')
                ->inline()
                ->label('Alineación')
                ->required(),
        ];

        return compact('name', 'label', 'schema');
    }
}

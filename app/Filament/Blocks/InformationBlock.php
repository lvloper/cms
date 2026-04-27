<?php

namespace App\Filament\Blocks;

use App\Filament\Traits\BlockComposer;
use Filament\Forms\Components as Form;
use Filament\Forms\Components\ToggleButton;
use Filament\Forms\Components\ToggleButtons;
use App\Filament\Traits\FormShortcuts;

class InformationBlock
{
    use BlockComposer;

    public static function compose(): array
    {
        // Define the name of the block
        $name = 'Information';

        // Define the label of the block
        $label = 'Bloque de Información';

        // Define the fields for the block
        $schema = [
                
            FormShortcuts::Input(name: 'title')
            ->label('Ingrese un Titulo'),

            ToggleButtons::make('customBg')->label('Color de fondo')->inline()
            ->options([
                'bg-white' => 'Blanco',
                'bg-gray-2' => 'Gris claro',
            ])
            ->default('bg-white'),
            
            \App\Filament\Traits\FormShortcuts::TipTap(
                name: 'content',
                label: 'Contenido',
                profile: 'avanced',
                required: false
            ),

            
          Form\ToggleButtons::make('style')
          ->options([
              'container' => 'Contenedor',
                'compact' => 'Compacto',
                'border' => 'Con borde'
          ])
          ->default('container')
          ->inline()
          ->label('Estilo'),

          Form\Repeater::make('items')->schema([
            FormShortcuts::RoutePicker(name:'route', btnLabel: true, required: true)->label('Boton'),
            
            ])
            ->label('Botones')
            ->defaultItems(0)
            ->reorderable()
        ];

        return compact('name', 'label', 'schema');
    }
}

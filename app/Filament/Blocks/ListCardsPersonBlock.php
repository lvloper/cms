<?php

namespace App\Filament\Blocks;

use App\Filament\Traits\BlockComposer;
use App\Filament\Traits\FormShortcuts;
use Filament\Forms\Components as Form;

class ListCardsPersonBlock
{
    use BlockComposer;

    public static function compose(): array
    {
        // Define the name of the block
        $name = 'ListCardsPerson';

        // Define the label of the block
        $label = 'Lista de tarjetas de personas';

        // Define the fields for the block
        $schema = [
            /* //! Use https://filamentphp.com/docs/3.x/forms/installation 
             to see all available fields and layout */
             Form\Repeater::make('items')->schema([
                FormShortcuts::Image(
                    name: 'image',
                    label: 'Imagen',
                    width: '640',
                    height: '480',
                ),
                FormShortcuts::Input(name: 'title')->label('Nombre'),
                FormShortcuts::Input(name: 'work',)->label('Cargo'),
            ]),
            FormShortcuts::RoutePicker( name: 'route', btnLabel: true, label : 'Boton')
        ];

        return compact('name', 'label', 'schema');
    }
}

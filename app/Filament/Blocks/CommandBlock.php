<?php

namespace App\Filament\Blocks;

use App\Filament\Traits\BlockComposer;
use Filament\Forms\Components as Form;
use App\Filament\Forms\Components\RoutePicker;

class CommandBlock
{
    use BlockComposer;

    public static function compose(): array
    {
        // Define the name of the block
        $name = 'Command';

        // Define the label of the block
        $label = 'Buscador';

        // Define the fields for the block
        $schema = [
            /* //! Use https://filamentphp.com/docs/3.x/forms/installation 
             to see all available fields and layout */
             
              RoutePicker::make('searchIn')
                ->allowExternal(false)
                ->allowFile(false)
                ->pickerLabel('Buscar dentro de'),
        ];

        return compact('name', 'label', 'schema');
    }
}

<?php

namespace App\Filament\Blocks;

use App\Filament\Traits\BlockComposer;
use Filament\Forms\Components as Form;
use App\Filament\Traits\FormShortcuts;

class TitleModalBlock
{
    use BlockComposer;
    use FormShortcuts;

    public static function compose(): array
    {
        // Define the name of the block
        $name = 'TitleModal';

        // Define the label of the block
        $label = 'Titulo de modal';

        // Define the fields for the block
        $schema = [
            FormShortcuts::Input(name: 'title')
                ->label('Ingrese un Titulo'),

            Form\ToggleButtons::make('color')
                ->label('Color')
                ->inline()
                ->default('black')
                ->options([
                    'primary' => 'Rojo',
                    'black' => 'Negro',
                    'secondary' => 'Violeta',
                ]),
        ];

        return compact('name', 'label', 'schema');
    }
}

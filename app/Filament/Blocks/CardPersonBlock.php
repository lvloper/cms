<?php

namespace App\Filament\Blocks;

use App\Filament\Traits\BlockComposer;
use App\Filament\Traits\FormShortcuts;
use Filament\Forms\Components as Form;

class CardPersonBlock
{
    use BlockComposer;
    use FormShortcuts;
    public static function compose(): array
    {
        // Define the name of the block
        $name = 'CardPerson';

        // Define the label of the block
        $label = 'Tarjeta de persona';

        // Define the fields for the block
        $schema = [
            FormShortcuts::Image(
                name: 'image',
                label: 'Imagen',
                width: '640',
                height: '480',
            ),
            FormShortcuts::Input(name: 'title')->label('Nombre'),
            FormShortcuts::Input(name: 'work',)->label('Cargo'),
        ];

        return compact('name', 'label', 'schema');
    }
}

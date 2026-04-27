<?php

namespace App\Filament\Blocks;

use App\Filament\Traits\BlockComposer;
use Filament\Forms\Components as Form;
use App\Filament\Traits\FormShortcuts;

class ItemModalBlock
{
    use BlockComposer;
    use FormShortcuts;
    public static function compose(): array
    {
        // Define the name of the block
        $name = 'ItemModal';

        // Define the label of the block
        $label = 'Lista de personas';

        // Define the fields for the block
        $schema = [
            Form\Repeater::make('items')
                ->label('Lista de personas')
                ->addActionLabel('Agregar una persona')
                ->schema([
                    FormShortcuts::Input(name: 'title')->label('Nombre'),
                    FormShortcuts::Input(name: 'work')->label('Cargo'),
                ])
                ->columns(2),
        ];

        return compact('name', 'label', 'schema');
    }
}

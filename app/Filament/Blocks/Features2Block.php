<?php

namespace App\Filament\Blocks;

use App\Filament\Traits\BlockComposer;
use Filament\Forms\Components as Form;
use App\Filament\Traits\FormShortcuts;

class Features2Block
{
    use BlockComposer;
    use FormShortcuts;

    public static function compose(): array
    {

        $name = 'Features2';

        $label = 'Bloque de eventos';

        $schema = [
            Form\Repeater::make('items')->schema([
                FormShortcuts::Input(name: 'title')->label('Titulo'),
                FormShortcuts::Rich(name: 'description', type: 'basic')->label('Texto principal'),
                FormShortcuts::Image(name: 'image', directory: 'events')->label('Imagen'),
                FormShortcuts::RoutePicker('route'),
            ])
        ];

        return compact('name', 'label', 'schema');
    }
}

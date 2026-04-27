<?php

namespace App\Filament\Blocks;

use App\Filament\Traits\BlockComposer;
use Filament\Forms\Components as Form;
use App\Filament\Traits\FormShortcuts;

class ItemsBlock
{
    use BlockComposer;
    use FormShortcuts;
    public static function compose(): array
    {
        
        $name = 'Items';

        $label = 'Bloque de items de navegacion';

        $schema = [
            Form\Repeater::make('items')->schema([
                FormShortcuts::Input(name:'label')->label('Titulo')->required(),
            ])
        ];

        return compact('name', 'label', 'schema');
    }
}

<?php

namespace App\Filament\Blocks;

use App\Filament\Traits\BlockComposer;
use App\Filament\Traits\FormShortcuts;

class TextBlock
{
    use BlockComposer;
    use FormShortcuts;

    public static function compose(): array
    {
       
        $name = 'Text';

        
        $label = 'Bloque de Texto';

       
        $schema = [
          FormShortcuts::Input('title')->label('Titulo'),
          FormShortcuts::TipTap( name:'text', label: 'Texto', profile: 'avanced' )->label('Texto'),

        ];

        return compact('name', 'label', 'schema');
    }
}

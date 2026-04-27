<?php

namespace App\Filament\Blocks;

use App\Filament\Traits\BlockComposer;
use App\Filament\Traits\FormShortcuts;
use Filament\Forms\Components\Textarea;

class CodeBlock
{
    use BlockComposer;
    use FormShortcuts;

    public static function compose(): array
    {
        $name = 'Code';
        
        $label = 'Bloque de Código';
        
        $schema = [               
            Textarea::make('code')
                ->label('Código')
                ->rows(12)
                ->required()
        ];

        return compact('name', 'label', 'schema');
    }
}
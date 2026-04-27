<?php

namespace App\Filament\Blocks;

use App\Filament\Traits\BlockComposer;
use App\Filament\Traits\FormShortcuts;
use Wiebenieuwenhuis\FilamentCodeEditor\Components\CodeEditor;

class CodeBlock
{
    use BlockComposer;
    use FormShortcuts;

    public static function compose(): array
    {
        $name = 'Code';
        
        $label = 'Bloque de Código';
        
        $schema = [               
            CodeEditor::make('code')
                ->label('Código')
                ->required()
        ];

        return compact('name', 'label', 'schema');
    }
}
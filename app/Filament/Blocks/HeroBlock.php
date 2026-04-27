<?php

namespace App\Filament\Blocks;

use App\Filament\Traits\BlockComposer;
use Filament\Forms\Components as Form;
use App\Filament\Traits\FormShortcuts;
use Filament\Forms\Components\ToggleButtons;
use Filament\Forms\Form as FormsForm;

class HeroBlock
{
    use BlockComposer;
    use FormShortcuts;

    public static function compose(): array
    {

        // Define the name of the block
        $name = 'Hero';

        // Define the label of the block
        $label = 'Bloque principal';

        // Define the fields for the block
        $schema = [  
           
            FormShortcuts::TextArea(name:'title')->label('Titulo')->required(),
            ToggleButtons::make('estilos')->label('Estilo')->inline()->required()->options([
                '1' => 'Primario',
                '2' => 'Secundario'
            ])
            ->default('1'),
            FormShortcuts::Rich(name:'description')->label('Texto principal')->required(),
            FormShortcuts::Rich(name:'description2')->label('Texto secundario'),
        ];

        return compact('name', 'label', 'schema');
    }
}

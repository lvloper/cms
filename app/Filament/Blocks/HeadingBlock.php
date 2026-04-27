<?php

namespace App\Filament\Blocks;

use App\Filament\Traits\BlockComposer;
use Filament\Forms\Components as Form;

class HeadingBlock
{
    use BlockComposer;

    public static function compose(): array
    {
        // Define the name of the block
        $name = 'heading';

        // Define the label of the block
        $label = 'Ejemplo bloque de titulo';

        // Define the fields for the block
        $schema = [
            Form\ToggleButtons::make('level')
                ->label('Nivel de encabezado')
                ->options([
                    'h1' => 'H1',
                    'h2' => 'H2',
                    'h3' => 'H3',
                    'h4' => 'H4',
                    'h5' => 'H5',
                    'h6' => 'H6',
                ])
                ->inline()
                ->default('h1'),
            Form\TextInput::make('text')
                ->label('Titulo')
                ->placeholder('Escribe un titulo'),
        ];

        return compact('name', 'label', 'schema');
    }
}

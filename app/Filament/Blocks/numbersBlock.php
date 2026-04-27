<?php

namespace App\Filament\Blocks;

use App\Filament\Traits\BlockComposer;
use Filament\Forms\Components as Form;
use App\Filament\Traits\FormShortcuts;

class numbersBlock
{
    use BlockComposer;

    public static function compose(): array
    {
        // Define the name of the block
        $name = 'numbers';

        // Define the label of the block
        $label = 'Boque de numeros';

        // Define the fields for the block
        $schema = [
            Form\Section::make()
                ->schema([
                    Form\TextInput::make('title')
                    ->label('Titulo')
                    ->required(),

                    FormShortcuts::Image(
                        name: 'image',
                        label: 'Imagen',
                        width: false,
                        height: false,
                        hasMobile: true,
                    ),

                    Form\Repeater::make('numbers')
                        ->label('Números')
                        ->schema([
                            Form\TextInput::make('number')
                                ->label('Número')
                                ->required(),
                            Form\TextInput::make('label')
                                ->label('Etiqueta')
                                ->required(),
                        ])
                        ->defaultItems(3)
                        ->reorderable()
                        ->collapsible()
                ])
        ];

        return compact('name', 'label', 'schema');
    }
}

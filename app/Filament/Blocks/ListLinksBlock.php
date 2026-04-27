<?php

namespace App\Filament\Blocks;

use App\Filament\Traits\BlockComposer;
use Filament\Forms\Components as Form;
use App\Filament\Traits\FormShortcuts;

class ListLinksBlock
{
    use BlockComposer, FormShortcuts;

    public static function compose(): array
    {
        // Define the name of the block
        $name = 'ListLinks';

        // Define the label of the block
        $label = 'Lista de links';

        // Define the fields for the block
        $schema = [
            Form\Repeater::make('links')
                ->schema([
                    FormShortcuts::RoutePicker('url'),
                    Form\ToggleButtons::make('show_index')
                        ->options([
                            true => 'Mostrar indice',
                            false => 'No mostrar indice',
                        ])
                        ->label('Mostrar indice')
                        ->default(true)
                        ->inline()
                        ->required(),
                ])
                ->minItems(1)
                ->columnSpan('full')
                ->label('Links')
                ->default([
                    [
                        'url' => '#',
                    ],
                    [
                        'url' => '#',
                    ],
                ]),
        ];

        return compact('name', 'label', 'schema');
    }
}

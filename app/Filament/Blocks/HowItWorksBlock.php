<?php

namespace App\Filament\Blocks;

use App\Filament\Traits\BlockComposer;
use Filament\Forms\Components as Form;
use App\Filament\Traits\FormShortcuts;

class HowItWorksBlock
{
    use BlockComposer;
    use FormShortcuts;
    public static function compose(): array
    {

        $name = 'HowItWorks';


        $label = 'Como funciona';


        $schema = [
            FormShortcuts::Input(name: 'title')
                ->label('Título Principal'),
            FormShortcuts::Rich(name: 'description')
                ->label('Descripcion'),
            Form\Repeater::make('items')
                ->addActionLabel('Agregar un ítem')
                ->schema([

                    FormShortcuts::Input(name: 'title'),
                    FormShortcuts::Image(name: 'icon', directory: 'events', width: '100', height: '100')->label('Icono'),
                    FormShortcuts::Rich(name: 'description')

                ])
        ];

        return compact('name', 'label', 'schema');
    }
}

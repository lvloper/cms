<?php

namespace App\Filament\Blocks;

use App\Filament\Traits\BlockComposer;
use Filament\Forms\Components as Form;
use App\Filament\Traits\FormShortcuts;

class HeaderModalBlock
{
    use BlockComposer;
    use FormShortcuts;

    public static function compose(): array
    {
        // Define the name of the block
        $name = 'HeaderModal';

        // Define the label of the block
        $label = 'Encabezado de persona';

        // Define the fields for the block
        $schema = [
            FormShortcuts::Image(
                name: 'image',
                label: 'Imagen',
                width: '640',
                height: '480',
            ),
            FormShortcuts::Input(name: 'title')->label('Nombre'),
            FormShortcuts::Input(name: 'work',)->label('Cargo'),
            Form\Repeater::make('items')
                ->addActionLabel('Agregar una red social')
                ->label('Redes sociales')
                ->schema([
                    FormShortcuts::IconSocial(name: 'icon')->label('Icono de red social'),
                    FormShortcuts::RoutePicker(name: 'route', forceExternal: true)->columnSpan(2),

                ])
                ->columns(3),
            FormShortcuts::TipTap(name: 'text', label: 'Texto', profile: 'simple')

        ];

        return compact('name', 'label', 'schema');
    }
}

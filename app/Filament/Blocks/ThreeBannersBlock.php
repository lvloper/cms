<?php

namespace App\Filament\Blocks;

use App\Filament\Traits\BlockComposer;
use App\Filament\Traits\FormShortcuts;
use Filament\Forms\Components as Form;

class ThreeBannersBlock
{
    use BlockComposer;
    use FormShortcuts;

    public static function compose(): array
    {
        // Define the name of the block
        $name = 'ThreeBanners';

        // Define the label of the block
        $label = 'Banners 3 columnas';

        // Define the fields for the block
        $schema = [
            /* //! Use https://filamentphp.com/docs/3.x/forms/installation 
             to see all available fields and layout */
            Form\Repeater::make('items')
                ->addActionLabel('Agregar un banner')
                ->schema([
                    FormShortcuts::Input(name: 'title')->label('Titulo'),
                    FormShortcuts::Input(name: 'title2')->label('Titulo Grande'),
                    FormShortcuts::IconPicker(
                        name: 'icon',
                    )->label('Icono para mobile'),
                    FormShortcuts::Input(name: 'titleBtn')->label('Titulo Boton')->default('Ver más'),
                    FormShortcuts::Image(
                        name: 'image',
                        label: 'Imagen',
                        width: '500',
                        height: '500',
                    ),
                    FormShortcuts::RoutePicker('route'),
                ])
        ];

        return compact('name', 'label', 'schema');
    }
}

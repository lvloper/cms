<?php

namespace App\Filament\Blocks;

use App\Filament\Traits\BlockComposer;
use Filament\Forms\Components as Form;
use App\Filament\Traits\FormShortcuts;

class GalleryBlock
{
    use BlockComposer;

    public static function compose(): array
    {
        // Define the name of the block
        $name = 'Gallery';

        // Define the label of the block
        $label = 'Galería';

        // Define the fields for the block
        $schema = [
            /* //! Use https://filamentphp.com/docs/3.x/forms/installation 
             to see all available fields and layout */
            Form\Grid::make()
                ->schema([
                    Form\ToggleButtons::make('style')
                        ->options([
                            'full' => 'Full',
                            'container' => 'Contenedor',
                            'compact' => 'Compacto',
                        ])
                        ->default('full')
                        ->inline()
                        ->label('Estilo'),
                    Form\Toggle::make('auto_play')
                        ->default(true)
                        ->helperText('Activar para que la galería se reproduzca automáticamente')
                        ->label('Autoplay'),
                ])
                ->columns(2),

            Form\Repeater::make('images')->schema([

                FormShortcuts::Image(
                    name: 'image',
                    label: 'Imagen',
                    width: '1362',
                    height: '514',
                    hasMobile: true,
                ),
                FormShortcuts::RoutePicker( name: 'route', allowAnchor: true),
            ]),


        ];

        return compact('name', 'label', 'schema');
    }
}

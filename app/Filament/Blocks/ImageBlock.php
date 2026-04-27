<?php

namespace App\Filament\Blocks;

use App\Filament\Traits\BlockComposer;
use Filament\Forms\Components as Form;
use App\Filament\Traits\FormShortcuts;

class ImageBlock
{
    use BlockComposer;

    public static function compose(): array
    {
        // Define the name of the block
        $name = 'Image';

        // Define the label of the block
        $label = 'Bloque de imagen';

        // Define the fields for the block
        $schema = [
            Form\ToggleButtons::make('style')
            ->options([
              'full' => 'Full',
              'container' => 'Contenedor',
              'compact' => 'Compacto',
            ])
            ->default('full')
            ->inline()
            ->label('Estilo'),
            
            FormShortcuts::Image(
                name: 'image',
                label: 'Imagen',
                width: false,
                height: false,
                hasMobile: true,
            ),
            Form\TextInput::make('caption')
                ->label('Epigrafe'),
        ];

        return compact('name', 'label', 'schema');
    }
}

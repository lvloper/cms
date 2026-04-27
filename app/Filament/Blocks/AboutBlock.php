<?php

namespace App\Filament\Blocks;

use App\Filament\Traits\BlockComposer;
use Filament\Forms\Components as Form;
use App\Filament\Traits\FormShortcuts;
use Filament\Forms\Components\ToggleButtons;
use Filament\Forms\Form as FormsForm;

class AboutBlock
{
    use BlockComposer;
    use FormShortcuts;
    public static function compose(): array
    {

        $name = 'About';

        $label = 'Bloque de imagen con texto';

        $schema = [
            FormShortcuts::Image(
                name: 'image',
                label: 'Imagen',
                width: '640',
                height: '480',
            ),
            Form\ToggleButtons::make('position')
                ->label('Posición de imagen')
                ->options([
                    'left' => 'Izquierda',
                    'right' => 'Derecha',
                ])
                ->default('left')
                ->inline()
                ->required(),
            FormShortcuts::Input(name: 'title')
                ->label('Título Superior'),
            FormShortcuts::Input(
                name: 'title2',
            )
            ->label('Título Inferior'),
            FormShortcuts::Rich(
                name: 'text',
                type: 'basic'
            )
                ->label('Texto'),
            FormShortcuts::Rich(
                name: 'moreText',
                type: 'basic'
            )
                ->label('Texto extra'),
        ];

        return compact('name', 'label', 'schema');
    }
}

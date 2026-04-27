<?php

namespace App\Filament\Blocks;

use App\Filament\Traits\BlockComposer;
use Filament\Forms\Components as Form;
use App\Filament\Traits\FormShortcuts;

class CardPerson2Block
{
    use BlockComposer;
    use FormShortcuts;
    public static function compose(): array
    {

        $name = 'CardPerson2';


        $label = 'Tarjeta de persona a 2 columnas';

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
                    FormShortcuts::IconSocial(name: 'icon')->label('Icono de red social')->required(),
                    FormShortcuts::RoutePicker(name: 'route', forceExternal: true, required: true)->columnSpan(2),

                ])
                ->columns(3),
            FormShortcuts::TipTap(name: 'text', label: 'Texto', profile: 'simple'),
            FormShortcuts::RoutePicker(name: 'button_route', label: 'Ver más'),

        ];

        return compact('name', 'label', 'schema');
    }
}

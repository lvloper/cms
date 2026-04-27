<?php

namespace App\Filament\Blocks;

use App\Filament\Forms\Components\RoutePicker;
use App\Filament\Traits\BlockComposer;
use App\Filament\Traits\FormShortcuts;
use Filament\Forms\Components as Form;

class BaseLinkListBlock
{
    use BlockComposer;
    use FormShortcuts;

    public static function compose(): array
    {
        $name = 'BaseLinkList';
        $label = 'Base: lista de enlaces';
        $schema = [
            Form\TextInput::make('title')->label('Título'),
            self::Rich('description')->label('Descripción'),
            Form\Repeater::make('items')
                ->label('Enlaces')
                ->addActionLabel('Agregar enlace')
                ->schema([
                    RoutePicker::make('route')
                        ->label('Enlace')
                        ->buttonLabel()
                        ->allowAnchor(),
                    Form\Textarea::make('description')->label('Descripción')->rows(2),
                ])
                ->defaultItems(3),
        ];

        return compact('name', 'label', 'schema');
    }
}

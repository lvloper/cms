<?php

namespace App\Filament\Blocks;

use App\Filament\Forms\Components\RoutePicker;
use App\Filament\Traits\BlockComposer;
use App\Filament\Traits\FormShortcuts;
use Filament\Forms\Components as Form;

class BaseCardsBlock
{
    use BlockComposer;
    use FormShortcuts;

    public static function compose(): array
    {
        $name = 'BaseCards';
        $label = 'Base: cards';
        $schema = [
            Form\TextInput::make('title')->label('Título'),
            self::Rich('description')->label('Descripción'),
            Form\Repeater::make('items')
                ->label('Cards')
                ->addActionLabel('Agregar card')
                ->schema([
                    Form\TextInput::make('title')->label('Título')->required(),
                    Form\Textarea::make('description')->label('Descripción')->rows(3),
                    self::Image('image', 'Imagen', '800', '600'),
                    RoutePicker::make('route')
                        ->label('Enlace')
                        ->allowAnchor(),
                ])
                ->columns(1)
                ->defaultItems(3),
        ];

        return compact('name', 'label', 'schema');
    }
}

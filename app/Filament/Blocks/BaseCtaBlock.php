<?php

namespace App\Filament\Blocks;

use App\Filament\Forms\Components\RoutePicker;
use App\Filament\Traits\BlockComposer;
use App\Filament\Traits\FormShortcuts;
use Filament\Forms\Components as Form;

class BaseCtaBlock
{
    use BlockComposer;
    use FormShortcuts;

    public static function compose(): array
    {
        $name = 'BaseCta';
        $label = 'Base: llamada a la acción';
        $schema = [
            Form\TextInput::make('eyebrow')->label('Volanta'),
            Form\TextInput::make('title')->label('Título')->required(),
            self::Rich('description')->label('Descripción'),
            RoutePicker::make('primary_route')
                ->label('Botón principal')
                ->buttonLabel()
                ->allowAnchor(),
            RoutePicker::make('secondary_route')
                ->label('Botón secundario')
                ->buttonLabel()
                ->allowAnchor(),
            Form\ToggleButtons::make('variant')
                ->label('Variante')
                ->options([
                    'light' => 'Clara',
                    'dark' => 'Oscura',
                    'accent' => 'Destacada',
                ])
                ->default('accent')
                ->inline()
                ->required(),
        ];

        return compact('name', 'label', 'schema');
    }
}

<?php

namespace App\Filament\Blocks;

use App\Filament\Traits\BlockComposer;
use App\Filament\Traits\FormShortcuts;
use Filament\Forms\Components as Form;

class BaseStatsBlock
{
    use BlockComposer;
    use FormShortcuts;

    public static function compose(): array
    {
        $name = 'BaseStats';
        $label = 'Base: métricas';
        $schema = [
            Form\TextInput::make('title')->label('Título'),
            self::Rich('description')->label('Descripción'),
            Form\Repeater::make('items')
                ->label('Métricas')
                ->addActionLabel('Agregar métrica')
                ->schema([
                    Form\TextInput::make('value')->label('Número')->required(),
                    Form\TextInput::make('label')->label('Etiqueta')->required(),
                    Form\Textarea::make('description')->label('Descripción')->rows(2),
                ])
                ->columns(3)
                ->defaultItems(3),
        ];

        return compact('name', 'label', 'schema');
    }
}

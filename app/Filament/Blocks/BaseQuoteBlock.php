<?php

namespace App\Filament\Blocks;

use App\Filament\Traits\BlockComposer;
use App\Filament\Traits\FormShortcuts;
use Filament\Forms\Components as Form;

class BaseQuoteBlock
{
    use BlockComposer;
    use FormShortcuts;

    public static function compose(): array
    {
        $name = 'BaseQuote';
        $label = 'Base: cita';
        $schema = [
            Form\Textarea::make('quote')->label('Cita')->rows(4)->required(),
            Form\TextInput::make('author')->label('Autor/a'),
            Form\TextInput::make('source')->label('Cargo o fuente'),
            self::Image('image', 'Imagen', '640', '640'),
        ];

        return compact('name', 'label', 'schema');
    }
}

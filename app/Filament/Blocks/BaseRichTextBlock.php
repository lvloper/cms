<?php

namespace App\Filament\Blocks;

use App\Filament\Traits\BlockComposer;
use App\Filament\Traits\FormShortcuts;
use Filament\Forms\Components as Form;

class BaseRichTextBlock
{
    use BlockComposer;
    use FormShortcuts;

    public static function compose(): array
    {
        $name = 'BaseRichText';
        $label = 'Base: texto enriquecido';
        $schema = [
            Form\TextInput::make('eyebrow')->label('Volanta'),
            Form\TextInput::make('title')->label('Título'),
            self::TipTap('content', 'Contenido', 'avanced', true),
            Form\ToggleButtons::make('width')
                ->label('Ancho')
                ->options([
                    'narrow' => 'Angosto',
                    'container' => 'Contenedor',
                    'wide' => 'Amplio',
                ])
                ->default('container')
                ->inline()
                ->required(),
        ];

        return compact('name', 'label', 'schema');
    }
}

<?php

namespace App\Filament\Blocks;

use App\Filament\Traits\BlockComposer;
use Filament\Forms\Components as Form;

class BaseEmbedBlock
{
    use BlockComposer;

    public static function compose(): array
    {
        $name = 'BaseEmbed';
        $label = 'Base: embed';
        $schema = [
            Form\TextInput::make('title')->label('Título'),
            Form\Textarea::make('embed')->label('Iframe o HTML embed')->rows(6)->required(),
            Form\TextInput::make('caption')->label('Epígrafe'),
        ];

        return compact('name', 'label', 'schema');
    }
}

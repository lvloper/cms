<?php

namespace App\Filament\Blocks;

use App\Filament\Traits\BlockComposer;
use Filament\Forms\Components as Form;
use App\Filament\Traits\FormShortcuts;
class MarqueeBlock
{
    use BlockComposer;
    use FormShortcuts;
    public static function compose(): array
    {

        $name = 'Marquee';


        $label = 'Bloque de Marquee';


        $schema = [
            FormShortcuts::Input(name:'title')->label('Título')->required(),
            FormShortcuts::Rich(name:'description')->label('Descripción')->toolbarButtons(config('admin.richEditor.basic'))->required(),
            FormShortcuts::Gallery(name:'images', label:'Imágenes')->required()
            ->imageEditorMode(2)
            ->imageResizeMode('contain')
            ->imageResizeTargetWidth(null)
            ->imageResizeTargetHeight(null)
            ->helperText(null),
        ];

        return compact('name', 'label', 'schema');
    }
}

<?php

namespace App\Filament\Blocks;

use App\Filament\Traits\BlockComposer;
use Filament\Forms\Components as Form;
use Filament\Forms\Form as FormsForm;
use App\Filament\Traits\FormShortcuts;

class MasonryBlock
{
    use BlockComposer;
    use FormShortcuts;

    public static function compose(): array
    {

        $name = 'Masonry';

        // Define the label of the block
        $label = 'bloque de masonry';

        // Define the fields for the block
        $schema = [
            FormShortcuts::TextArea(name: 'title')->label('Título')->required(),
            FormShortcuts::Rich(name: 'description')->label('Descripción')->toolbarButtons(config('admin.richEditor.basic'))->required(),
            FormShortcuts::Image(name: 'image', directory: 'masonry', width: '320', height: '520', hasMobile: true, widthMobile: '320', heightMobile: '280')->label('Imagen'),
            Form\Repeater::make('items')->schema([
                FormShortcuts::Input(name: 'title')->label('Título')->required(),
                FormShortcuts::Rich(name: 'description')->label('Descripción')->toolbarButtons(config('admin.richEditor.basic'))->required(),
            ]),
            FormShortcuts::Rich(name: 'description2')->label('Descripción')->toolbarButtons(config('admin.richEditor.basic'))->required(false),
        ];

        return compact('name', 'label', 'schema');
    }
}

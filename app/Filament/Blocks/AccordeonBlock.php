<?php

namespace App\Filament\Blocks;

use App\Filament\Traits\BlockComposer;
use Filament\Forms\Components as Form;
use App\Filament\Traits\FormShortcuts;
use Filament\Schemas\Schema as FormsForm;

class AccordeonBlock
{
    use BlockComposer;
    use FormShortcuts;
    public static function compose(): array
    {

        $name = 'Accordeon';

        $label = 'Bloque de Acordeón';
        $schema = [
            FormShortcuts::TextArea(
                name: 'title',
            )->label('Titulo'),
            FormShortcuts::Rich('description', 'basic')->label('Descripción'),
            FormShortcuts::Rich('description2', 'basic')->label('Descripción 2'),
            Form\Repeater::make('items')
                ->addActionLabel('Agregar un ítem de acordeón')
                ->schema([
                    FormShortcuts::Input(name: 'title')->label('Titulo'),
                    FormShortcuts::Rich(name: 'description', type: 'advanced')->label('Descripción'),
                    FormShortcuts::Gallery(name: 'images', label: 'Imágenes', directory: 'accordeon')
                        ->imageEditorMode(2)
                        ->imageResizeMode('contain')
                        ->imageResizeTargetWidth(null)
                        ->imageResizeTargetHeight(null)
                        ->helperText(null),
                    FormShortcuts::RoutePicker('route'),
                ]),
        ];

        return compact('name', 'label', 'schema');
    }
}

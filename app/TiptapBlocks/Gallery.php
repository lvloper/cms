<?php

namespace App\TiptapBlocks;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\FileUpload;
use FilamentTiptapEditor\TiptapBlock;

class Gallery extends TiptapBlock
{
    public string $preview = 'tiptap-blocks.previews.gallery';

    public string $rendered = 'tiptap-blocks.rendered.gallery';

    public ?string $label = 'Galería';

    public ?string $icon = 'heroicon-o-photo';

    public string $width = '4xl';

    public function getFormSchema(): array
    {
        return [
            Select::make('type')
                ->label('Tipo de visualización')
                ->options([
                    'grid_lightbox' => 'Grilla 3 columnas + Lightbox',
                    'carousel_main' => 'Carrusel con foto principal',
                    'carousel_lightbox' => 'Carrusel + Lightbox',
                ])
                ->default('grid_lightbox')
                ->required(),

            FileUpload::make('images')
                ->label('Imágenes')
                ->image()
                ->multiple()
                ->directory('galleries')
                ->imageEditor()                ->imageEditorAspectRatios([
                    '4:3',
                ])
                ->imageCropAspectRatio('4:3')                ->panelLayout('grid')
                ->panelAspectRatio('4:3')
                ->helperText('Seleccioná o arrastrá varias imágenes. Tamaño ideal: 1200×900px (o proporción 4:3)')
                ->required()
                ->columnSpanFull()
                ->extraAttributes([
                    'data-gallery-fileupload' => 'true',
                ]),
        ];
    }
}

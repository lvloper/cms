<?php

namespace App\Filament\Forms\Components;

use Filament\Forms\Components\FileUpload;

class Gallery
{
    public static function make(
        string $name,
        string $label = 'Galería',
        string $directory = 'images',
        string $width = '1280',
        string $height = '640'
    ): FileUpload {
        return FileUpload::make($name)
            ->label($label)
            ->image()
            ->imageEditor()
            ->imageEditorMode(2)
            ->preserveFilenames()
            ->imageResizeTargetWidth($width)
            ->imageResizeTargetHeight($height)
            ->imageResizeMode('cover')
            ->imageResizeUpscale(false)
            ->orientImagesFromExif()
            ->multiple(true)
            ->panelLayout('grid')
            ->reorderable()
            ->directory($directory)
            ->helperText("Tamaño recomendado: $width x $height")
            ->visibility('public');
    }
}

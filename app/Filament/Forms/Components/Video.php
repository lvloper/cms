<?php

namespace App\Filament\Forms\Components;

use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\ToggleButtons;
use Filament\Schemas\Components\Utilities\Get;

class Video
{
    public static function make(
        string $videoType = 'videoType',
        string $videoId = 'videoId',
        string $videoFile = 'videoFile',
        string $directory = 'videos',
        bool $required = false,
        string $videoImage = 'videoImage',
        string $imageDirectory = 'images/videos',
    ): array {
        return [
            ToggleButtons::make($videoType)
                ->label('Tipo de contenido')
                ->options([
                    'youtube' => 'YouTube',
                    'upload' => 'Video MP4',
                    'image' => 'Imagen',
                ])
                ->inline()
                ->default(function (Get $get) use ($videoId, $videoFile, $videoImage) {
                    if (! empty($get($videoId))) {
                        return 'youtube';
                    }

                    if (! empty($get($videoFile))) {
                        return 'upload';
                    }

                    if (! empty($get($videoImage))) {
                        return 'image';
                    }

                    return 'youtube';
                })
                ->reactive(),

            TextInput::make($videoId)
                ->label('Codigo de YouTube')
                ->required(fn (Get $get): bool => $required && $get($videoType) === 'youtube')
                ->hidden(fn (Get $get): bool => $get($videoType) !== 'youtube')
                ->helperText('Ingresa el ID del video de YouTube (ej. 0Bjsv6Ft9nk).'),

            FileUpload::make($videoFile)
                ->label('Archivo de video')
                ->acceptedFileTypes(['video/mp4'])
                ->rules([
                    'mimes:mp4',
                    'mimetypes:video/mp4',
                ])
                ->validationMessages([
                    'mimes' => 'Por favor, sube un video valido en formato MP4.',
                    'mimetypes' => 'Por favor, sube un video valido en formato MP4. Verifica la extension de tu archivo.',
                ])
                ->directory($directory)
                ->visibility('public')
                ->preserveFilenames()
                ->downloadable()
                ->openable()
                ->multiple(false)
                ->maxFiles(1)
                ->required(fn (Get $get): bool => $required && $get($videoType) === 'upload')
                ->hidden(fn (Get $get): bool => $get($videoType) !== 'upload')
                ->helperText('Formatos soportados: MP4. Solo se permite un archivo.'),

            FileUpload::make($videoImage)
                ->label('Imagen')
                ->image()
                ->imageEditor()
                ->imageEditorMode(1)
                ->orientImagesFromExif()
                ->multiple(false)
                ->directory($imageDirectory)
                ->visibility('public')
                ->preserveFilenames()
                ->downloadable()
                ->openable()
                ->required(fn (Get $get): bool => $required && $get($videoType) === 'image')
                ->hidden(fn (Get $get): bool => $get($videoType) !== 'image')
                ->helperText('Formatos soportados: JPG, PNG, WebP.'),
        ];
    }
}

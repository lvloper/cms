<?php

namespace App\Filament\Forms\Components;

use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\ToggleButtons;
use Filament\Schemas\Components\Utilities\Get;

class MediaPicker
{
    public static function make(
        string $type = 'media_type',
        string $image = 'media_image',
        string $video = 'media_video',
        string $alt = 'media_alt',
        string $placeholder = 'media_placeholder',
        string $autoplay = 'media_autoplay',
        string $directory = 'media/clients',
        string $label = 'Imagen o video',
        string $width = '1600',
        string $height = '1000',
    ): array {
        return [
            ToggleButtons::make($type)
                ->label($label)
                ->options([
                    'image' => 'Imagen',
                    'video' => 'Video',
                ])
                ->icons([
                    'image' => 'heroicon-o-photo',
                    'video' => 'heroicon-o-video-camera',
                ])
                ->inline()
                ->default('image')
                ->required()
                ->reactive(),

            Image::make(
                name: $image,
                label: 'Archivo de imagen',
                width: $width,
                height: $height,
                directory: $directory.'/images',
            )
                ->visible(fn (Get $get): bool => $get($type) === 'image'),

            FileUpload::make($video)
                ->label('Archivo de video')
                ->acceptedFileTypes([
                    'video/mp4',
                    'video/webm',
                    'video/quicktime',
                ])
                ->disk('public')
                ->directory($directory.'/videos')
                ->visibility('public')
                ->downloadable()
                ->openable()
                ->multiple(false)
                ->maxFiles(1)
                ->maxSize(102400)
                ->visible(fn (Get $get): bool => $get($type) === 'video')
                ->helperText('MP4, WebM o MOV. Máximo 100 MB.'),

            TextInput::make($alt)
                ->label('Descripción accesible')
                ->helperText('Describe la imagen o el contenido del video. No repitas el texto visible.')
                ->maxLength(300),

            TextInput::make($placeholder)
                ->label('Texto mientras falta el archivo')
                ->placeholder('Reemplazar por imagen/video de…')
                ->helperText('Se muestra sobre una superficie gris hasta cargar la pieza definitiva.')
                ->required(fn (Get $get): bool => blank($get($image)) && blank($get($video)))
                ->maxLength(180),

            Toggle::make($autoplay)
                ->label('Reproducir video automáticamente')
                ->helperText('Se reproduce silenciado, en loop y conserva controles accesibles.')
                ->default(false)
                ->visible(fn (Get $get): bool => $get($type) === 'video'),
        ];
    }
}

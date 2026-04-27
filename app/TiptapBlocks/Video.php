<?php

namespace App\TiptapBlocks;

use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\ToggleButtons;
use Filament\Forms\Get;
use FilamentTiptapEditor\TiptapBlock;

class Video extends TiptapBlock
{
    public string $preview = 'tiptap-blocks.previews.video';

    public string $rendered = 'tiptap-blocks.rendered.video';

    public ?string $label = 'Video';

    public ?string $icon = 'heroicon-o-play-circle';

    public string $width = 'xl';

    public function getFormSchema(): array
    {
        return [
            ToggleButtons::make('style')
                ->options([
                    'default' => 'Default (Full wide)',
                    'compact' => 'Compacto',
                ])
                ->default('default')
                ->inline()
                ->label('Estilo'),

            ToggleButtons::make('videoType')
                ->label('Tipo de video')
                ->options([
                    'youtube' => 'YouTube',
                    'upload' => 'Subir video MP4',
                ])
                ->inline()
                ->default(function (Get $get) {
                    $videoId = $get('videoId');
                    $videoFile = $get('videoFile');

                    if (!empty($videoId)) {
                        return 'youtube';
                    } elseif (!empty($videoFile)) {
                        return 'upload';
                    }

                    return 'youtube';
                })
                ->reactive(),

            TextInput::make('videoId')
                ->label('Código de YouTube')
                ->required(fn(Get $get): bool => $get('videoType') === 'youtube')
                ->hidden(fn(Get $get): bool => $get('videoType') !== 'youtube')
                ->helperText('Ingresa el ID del video de YouTube (ej. B4NEVeHH3TI)'),

            FileUpload::make('videoFile')
                ->label('Archivo de video')
                ->acceptedFileTypes(['video/mp4'])
                ->directory('videos')
                ->visibility('public')
                ->preserveFilenames()
                ->downloadable()
                ->openable()
                ->multiple(false)
                ->maxFiles(1)
                ->hidden(fn(Get $get): bool => $get('videoType') !== 'upload')
                ->helperText('Formatos soportados: MP4 (máx. 10MB). Solo se permite un archivo.'),
        ];
    }
}

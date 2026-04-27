<?php

namespace App\Filament\Blocks;

use App\Filament\Traits\BlockComposer;
use Filament\Forms\Components as Form;

class VideoBlock
{
    use BlockComposer;

    public static function compose(): array
    {
        $name = 'Video';

        $label = 'Bloque de video';

        $schema = [
            Form\ToggleButtons::make('style')
                ->options([
                    'default' => 'Default (Full wide)',
                    'compact' => 'Compacto',
                ])
                ->default('default')
                ->inline()
                ->label('Estilo'),

            Form\ToggleButtons::make('videoType')
                ->label('Tipo de video')
                ->options([
                    'youtube' => 'YouTube',
                    'upload' => 'Subir video MP4',
                ])
                // ->required()
                ->inline()
                ->default(function (\Filament\Forms\Get $get) {
                    // Auto-detectar tipo basándose en campos existentes
                    $videoId = $get('videoId');
                    $videoFile = $get('videoFile');
                    
                    if (!empty($videoId)) {
                        return 'youtube';
                    } elseif (!empty($videoFile)) {
                        return 'upload';
                    }
                    
                    return 'youtube'; // Default fallback
                })
                ->reactive(),

            Form\TextInput::make('videoId')
                ->label('Código de YouTube')
                ->required(fn(\Filament\Forms\Get $get): bool => $get('videoType') === 'youtube')
                ->hidden(fn(\Filament\Forms\Get $get): bool => $get('videoType') !== 'youtube')
                ->helperText('Ingresa el ID del video de YouTube (ej. B4NEVeHH3TI)'),

            Form\FileUpload::make('videoFile')
                ->label('Archivo de video')
                ->acceptedFileTypes(['video/mp4'])
                ->directory('videos')
                ->visibility('public')
                ->preserveFilenames()
                ->downloadable()
                ->openable()
                ->multiple(false)
                ->maxFiles(1)
                ->hidden(fn(\Filament\Forms\Get $get): bool => $get('videoType') !== 'upload')
                ->helperText('Formatos soportados: MP4 (máx. 10MB). Solo se permite un archivo.'),
        ];

        return compact('name', 'label', 'schema');
    }
}

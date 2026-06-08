<?php

namespace App\Filament\Blocks;

use App\Filament\Forms\Components\Field;

class MediaTextBlock extends PageBlock
{
    protected const NAME = 'MediaText';

    protected const LABEL = 'Imagen y texto';

    protected const CATEGORY = 'Contenido';

    protected static function fields(): array
    {
        return [
            Field::toggleButtons('layout', 'Disposición', [
                'left' => 'Imagen a la izquierda',
                'right' => 'Imagen a la derecha',
            ])
                ->default('left')
                ->inline()
                ->required(),
            Field::video(
                videoType: 'media_type',
                videoId: 'youtube_id',
                videoFile: 'video_file',
                videoImage: 'image',
                imageDirectory: 'images/media-text',
            ),
            Field::text('title', 'Título'),
            Field::rich('content', 'Texto', 'basic'),
            Field::route('cta', 'Botón (opcional)'),
        ];
    }
}

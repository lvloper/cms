<?php

namespace App\Filament\Blocks;

use App\Filament\Forms\Components\Field;

class MediaBlock extends PageBlock
{
    protected const NAME = 'Media';

    protected const LABEL = 'Imagen / Video';

    protected const CATEGORY = 'Multimedia';

    protected static function fields(): array
    {
        return [
            Field::video(
                videoType: 'media_type',
                videoId: 'youtube_id',
                videoFile: 'video_file',
                videoImage: 'image',
                imageDirectory: 'images/media',
            ),
            Field::text('caption', 'Epígrafe'),
        ];
    }
}

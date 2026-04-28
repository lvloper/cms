<?php

namespace App\Filament\Blocks;

use App\Filament\Forms\Components\Field;

class BaseEmbedBlock extends PageBlock
{
    protected const NAME = 'BaseEmbed';

    protected const LABEL = 'Base: embed';

    protected static function fields(): array
    {
        return [
            Field::text('title', 'Título'),
            Field::textarea('embed', 'Iframe o HTML embed')->rows(6)->required(),
            Field::text('caption', 'Epígrafe'),
        ];
    }
}

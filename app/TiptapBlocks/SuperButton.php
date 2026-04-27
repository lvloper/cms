<?php

namespace App\TiptapBlocks;

use FilamentTiptapEditor\TiptapBlock;
use Filament\Forms\Components\TextInput;
use App\Filament\Traits\FormShortcuts;

class SuperButton extends TiptapBlock
{
    use FormShortcuts;

    public string $preview = 'tiptap-blocks.previews.super-button';

    public string $rendered = 'tiptap-blocks.rendered.super-button';

    public ?string $icon = 'heroicon-o-button';

    public ?string $label = 'Botón';

    public function getFormSchema(): array
    {
        return [
            TextInput::make('text')->label('Texto'),
            FormShortcuts::RoutePicker('url')->label('URL'),
        ];
    }
}
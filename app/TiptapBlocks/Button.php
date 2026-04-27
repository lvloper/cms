<?php

namespace App\TiptapBlocks;

use Filament\Forms\Components\ToggleButtons;
use FilamentTiptapEditor\TiptapBlock;
use Filament\Forms\Components\TextInput;
use App\Filament\Traits\FormShortcuts;
class Button extends TiptapBlock
{
    use FormShortcuts;
    public string $preview = 'tiptap-blocks.previews.button';

    public string $rendered = 'tiptap-blocks.rendered.button';
    
    public ?string $icon = 'heroicon-o-arrow-up-right';

    public ?string $label = 'Botón';


    public function getFormSchema(): array
    {
        return [
            TextInput::make('text')->label('Texto'),
            FormShortcuts::RoutePicker('route')->label('URL'),
            ToggleButtons::make('type')->options([
                'primary' => 'Rojo',
                'secondary' => 'Violeta',
                'big' => 'Super botón',
            ])->label('Estilo')
            ->inline(),
        ];
    }
}
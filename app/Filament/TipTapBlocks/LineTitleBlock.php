<?php

namespace App\Filament\TipTapBlocks;

use FilamentTiptapEditor\TiptapBlock;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\ToggleButtons;

class LineTitleBlock extends TiptapBlock
{
    public string $preview = 'blocks.tiptap.line-title';
 
    public string $rendered = 'blocks.tiptap.line-title';
    
    public ?string $icon = 'heroicon-o-text-size';

    public static function getBlockName(): string
    {
        return 'lineTitleBlock';
    }

    public function getFormSchema(): array
    {
        return [
            TextInput::make('name'),
            
            ToggleButtons::make('size')->label('Tamaño')->options([
                'sm' => 'Medio',
                'md' => 'Grande',
                'lg' => 'Pequeño',
            ])
            ->inline()
            ->default('md')
            ->required()
        ];
    }
}
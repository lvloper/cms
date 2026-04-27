<?php

namespace App\Filament\Templates;

use Filament\Forms\Components\Builder;
use Filament\Forms\Components\Actions\Action;

class ModalTemplate
{
    public static function schema(): array
    {
        // Copy here the blocks you want to use in the template
        $blocks = [
            \App\Filament\Blocks\TextBlock::make(),
            \App\Filament\Blocks\ItemModalBlock::make(),
            \App\Filament\Blocks\ImageBlock::make(),
            \App\Filament\Blocks\VideoBlock::make(),
            \App\Filament\Blocks\HeaderModalBlock::make(),
            \App\Filament\Blocks\TitleModalBlock::make(),
            \App\Filament\Blocks\CardPersonBlock::make(),
            \App\Filament\Blocks\CardPerson2Block::make(),
        ];    

        $defaultTemplate = [
        ];

        return [
            Builder::make('blocks')
                ->label('Bloques')
                ->blockPreviews(areInteractive: true)
                ->default( $defaultTemplate )
                ->blocks($blocks)
                ->columnSpan('full')
                ->cloneable()
                ->reorderableWithButtons()
                ->editAction(
                    fn (Action $action) => $action->closeModalByClickingAway(false)
                )
                ->view('filament-forms::components.editor')
                ->collapsible(),
        ];
    }
}

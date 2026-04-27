<?php

namespace App\Filament\Templates;

use Filament\Actions\Action;
use Filament\Forms\Components\Builder;

class ModalTemplate
{
    public static function schema(): array
    {
        // Copy here the blocks you want to use in the template
        $blocks = [
            \App\Filament\Blocks\BaseRichTextBlock::make(),
            \App\Filament\Blocks\BaseCtaBlock::make(),
            \App\Filament\Blocks\BaseCardsBlock::make(),
            \App\Filament\Blocks\BaseQuoteBlock::make(),
            \App\Filament\Blocks\BaseEmbedBlock::make(),
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

<?php

namespace App\Filament\Templates;

use App\Filament\Blocks\ClientClosingBlock;
use App\Filament\Blocks\ClientFeatureBlock;
use App\Filament\Blocks\ClientMarqueeBlock;
use App\Filament\Blocks\ClientMetricsBlock;
use App\Filament\Blocks\ClientProcessBlock;
use App\Filament\Blocks\ClientProjectsBlock;
use App\Filament\Blocks\ClientStatementBlock;
use App\Filament\Blocks\ClientTestimonialBlock;
use Filament\Actions\Action;
use Filament\Forms\Components\Builder;
use Filament\Forms\Components\Hidden;

class ClientTemplate
{
    public static function schema($form): array
    {
        return [
            Builder::make('blocks')
                ->label('Bloques del caso')
                ->blockPreviews(areInteractive: true)
                ->default([])
                ->blocks([
                    ClientMarqueeBlock::make(),
                    ClientProjectsBlock::make(),
                    ClientFeatureBlock::make(),
                    ClientStatementBlock::make(),
                    ClientProcessBlock::make(),
                    ClientMetricsBlock::make(),
                    ClientTestimonialBlock::make(),
                    ClientClosingBlock::make(),
                ])
                ->columnSpan('full')
                ->reorderableWithButtons()
                ->cloneable()
                ->editAction(
                    fn (Action $action) => $action->closeModalByClickingAway(false)
                )
                ->view('filament-forms::components.editor'),

            Hidden::make('blocks_pastable')
                ->default('')
                ->dehydrated(false),
        ];
    }
}

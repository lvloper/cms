<?php

namespace App\Filament\Templates;

use App\Filament\TipTapBlocks\LineTitleBlock;
use Filament\Forms\Components\Builder;
use Filament\Forms\Components\Actions\Action;

class DefaultTemplate
{
    public static function schema($form): array
    {
        // Copy here the blocks you want to use in the template
        $blocks = [
            \App\Filament\Blocks\HeroBlock::make(),
            \App\Filament\Blocks\LineTitleBlock::make(),
            \App\Filament\Blocks\ImageBlock::make(),
            \App\Filament\Blocks\VideoBlock::make(),
            \App\Filament\Blocks\AccordeonBlock::make(),
            \App\Filament\Blocks\AccordeonModalsColumnsBlock::make(),
            \App\Filament\Blocks\Features2Block::make(),
            \App\Filament\Blocks\CarrouselBlock::make(),
            \App\Filament\Blocks\MarqueeBlock::make(),
            \App\Filament\Blocks\CarrersBlock::make(),
            \App\Filament\Blocks\AboutBlock::make(),
            \App\Filament\Blocks\IgStoriesBlock::make(),
            \App\Filament\Blocks\accordeon_workersBlock::make(),
            \App\Filament\Blocks\MasonryBlock::make(),
            \App\Filament\Blocks\InformationBlock::make(),
            \App\Filament\Blocks\HowItWorksBlock::make(),
            \App\Filament\Blocks\CommandBlock::make(),
            \App\Filament\Blocks\SearchAndBannerBlock::make(),
            \App\Filament\Blocks\ThreeBannersBlock::make(),
            \App\Filament\Blocks\GalleryBlock::make(),
            \App\Filament\Blocks\numbersBlock::make(),
            \App\Filament\Blocks\ListCardsPersonBlock::make(),
            \App\Filament\Blocks\JobsofferListBlock::make(),
            \App\Filament\Blocks\ApplyButtonBlock::make(),
            \App\Filament\Blocks\ListLinksBlock::make(),
            \App\Filament\Blocks\MaterialsBlock::make(),
            \App\Filament\Blocks\RecurserosBlock::make(),
            \App\Filament\Blocks\CodeBlock::make(),


        ];

        // foreach ($blocks as $block) {
        //     $block
        //         ->label(function (?array $state): ?string {
        //             if ($state === null) {
        //                 return nul
        //             //     return 'Bloque';
        //             return $state['blockTitle'] ?? null;
        //         });
        // }

        $defaultTemplate = [
            // [
            //     'type' => 'heading',
            //     'data' => [
            //         'text' => fake()->sentence(),
            //     ],
            // ],
        ];

        return [
            Builder::make('blocks')
                ->label('Bloques')
                ->blockPreviews(areInteractive: true)
                ->default($defaultTemplate)
                ->blocks($blocks)
                ->columnSpan('full')
                ->reorderableWithButtons()
                ->cloneable()
                ->editAction(
                    fn(Action $action) => $action->closeModalByClickingAway(false)
                )
                ->view('filament-forms::components.editor'),
            
            // Hidden input for paste functionality
            \Filament\Forms\Components\Hidden::make('blocks_pastable')
                ->default('')
                ->dehydrated(false),
        ];
    }
}

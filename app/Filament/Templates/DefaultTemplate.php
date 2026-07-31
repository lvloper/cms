<?php

namespace App\Filament\Templates;

use Filament\Actions\Action;
use Filament\Forms\Components\Builder;
use Filament\Forms\Components\Hidden;

class DefaultTemplate
{
    public static function schema($form): array
    {
        // Los bloques originales eran ejemplos del CMS base. Se conservan sus
        // clases para poder renderizar contenido histórico, pero ya no se
        // ofrecen en el selector.
        $blocks = [];

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
                    fn (Action $action) => $action->closeModalByClickingAway(false)
                )
                ->view('filament-forms::components.editor'),

            // Hidden input for paste functionality
            Hidden::make('blocks_pastable')
                ->default('')
                ->dehydrated(false),
        ];
    }
}

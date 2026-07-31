<?php

namespace App\Filament\Templates;

use Filament\Actions\Action;
use Filament\Forms\Components\Builder;

class ModalTemplate
{
    public static function schema(): array
    {
        // Los bloques de ejemplo se retiraron del selector. Sus clases y
        // vistas se conservan únicamente para contenido histórico.
        $blocks = [];

        $defaultTemplate = [
        ];

        return [
            Builder::make('blocks')
                ->label('Bloques')
                ->blockPreviews(areInteractive: true)
                ->default($defaultTemplate)
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

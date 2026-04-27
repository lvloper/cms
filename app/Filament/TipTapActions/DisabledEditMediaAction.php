<?php

namespace App\Filament\TipTapActions;

use Filament\Forms\Components\Actions\Action;

class DisabledEditMediaAction extends Action
{
    public static function getDefaultName(): ?string
    {
        return 'filament_tiptap_edit_media';
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this
            ->color('danger')
            ->modalHeading('Eliminar medio')
            ->modalDescription('Para borrar la imagen, ciérrala y presiona Supr/Backspace sobre el elemento en el editor. La edición está deshabilitada.')
            ->modalSubmitActionLabel('Cerrar')
            ->modalCancelAction(false)
            ->action(fn () => null);
    }
}

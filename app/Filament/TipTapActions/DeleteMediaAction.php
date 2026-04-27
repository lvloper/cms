<?php

namespace App\Filament\TipTapActions;

use Filament\Forms\Components\Actions\Action;
use FilamentTiptapEditor\TiptapEditor;

class DeleteMediaAction extends Action
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
            ->modalDescription('Se eliminará el elemento seleccionado del editor. ¿Deseas continuar?')
            ->modalSubmitActionLabel('Eliminar')
            ->modalCancelActionLabel('Cancelar')
            ->action(function (TiptapEditor $component) {
                $component->getLivewire()->dispatch(
                    event: 'deleteMediaFromAction',
                    statePath: $component->getStatePath(),
                );
            });
    }
}

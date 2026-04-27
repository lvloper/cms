<?php

namespace App\Livewire;

use Livewire\Component;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use App\Filament\Traits\FormShortcuts;

class TestEditor extends Component implements HasForms
{
    use InteractsWithForms;
    use FormShortcuts;

    public ?array $data = [];

    public function mount(): void
    {
        $this->form->fill();
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                \FilamentTiptapEditor\TiptapEditor::make('content')
                    ->label('Editor de Prueba')
                    ->profile('default')
                    ->tools([]) // Force empty tools to avoid null issues
                    ->bubbleMenuTools([])
                    ->floatingMenuTools([])
                    ->helperText('Pega contenido de Google Docs o Word aquí para probar la limpieza de formato'),
            ])
            ->statePath('data');
    }

    public function render()
    {
        return view('livewire.test-editor')->layout('layouts.test-simple');
    }
}

<?php

namespace App\Livewire;

use Livewire\Component;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Components\RichEditor;
use Filament\Schemas\Schema;

class TestEditor extends Component implements HasForms
{
    use InteractsWithForms;

    public ?array $data = [];

    public function mount(): void
    {
        $this->form->fill();
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->schema([
                RichEditor::make('content')
                    ->label('Editor de Prueba')
                    ->helperText('Pega contenido de Google Docs o Word aquí para probar la limpieza de formato'),
            ])
            ->statePath('data');
    }

    public function render()
    {
        return view('livewire.test-editor')->layout('layouts.test-simple');
    }
}

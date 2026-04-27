<?php

namespace App\Filament\Blocks;

use App\Filament\Traits\BlockComposer;
use Filament\Forms\Components as Form;
use Filament\Forms\Form as FormsForm;
use App\Filament\Traits\FormShortcuts;

class CarrouselBlock
{
    use BlockComposer;

    public static function compose(): array
    {

        $name = 'Carrousel';


        $label = 'Carrousel de notas';


        $schema = [
            FormShortcuts::Input(name: 'title')->label('Titulo Principal')->default('Te puede interesar'),
            
            Form\ToggleButtons::make('content_type')
                ->label('Tipo de Contenido')
                ->options([
                    'latest' => 'Últimas novedades',
                    'specific' => 'Notas específicas',
                    'tags' => 'Por tags',
                ])
                ->default('tags')
                ->inline()
                ->reactive()
                ->required(),
            
            Form\Repeater::make('items')
                ->schema([
                    FormShortcuts::RoutePicker(
                        name: 'route',
                        label: 'Notas',
                        allowExternal: false,
                        required: true,
                        filter: fn($query) => $query->where('routable_type', 'App\Models\Blog')
                    )
                ])
                ->helperText('Seleccione notas específicas para mostrar')
                ->visible(fn(\Filament\Forms\Get $get) => $get('content_type') === 'specific'),
            
            Form\Select::make('selected_tags')
                ->label('Tags de Novedades')
                ->multiple()
                ->searchable()
                ->options(function () {
                    return \Spatie\Tags\Tag::all()->mapWithKeys(function ($tag) {
                        return [$tag->slug => $tag->name];
                    })->toArray();
                })
                ->helperText('Seleccione uno o más tags para mostrar las novedades que los contengan')
                ->placeholder('Buscar y seleccionar tags...')
                ->visible(fn(\Filament\Forms\Get $get) => $get('content_type') === 'tags'),

            // FormShortcuts::RoutePicker( 'route', btnLabel: true)
        ];



        return compact('name', 'label', 'schema');
    }
}

<?php

namespace App\Filament\Blocks;

use Filament\Forms\Components\Builder\Block;
use Filament\Forms\Components\KeyValue;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TagsInput;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Tabs;

abstract class PageBlock
{
    protected const NAME = '';

    protected const CATEGORY = 'Otros';

    protected const LABEL = null;

    protected const PREVIEW = null;

    abstract protected static function fields(): array;

    public static function make(): Block
    {
        $preview = static::PREVIEW ?? static::NAME;

        $block = Block::make(static::NAME)
            ->label(static::LABEL ?? static::NAME)
            ->meta('category', static::CATEGORY)
            ->schema([
                Tabs::make('Tabs')
                    ->tabs([
                        Tabs\Tab::make(__('Contenido'))
                            ->icon('heroicon-o-document-text')
                            ->schema(collect(static::fields())->flatten(1)->toArray()),
                        Tabs\Tab::make(__('General'))
                            ->icon('heroicon-o-document-text')
                            ->schema([
                                Grid::make(2)
                                    ->schema([
                                        TextInput::make('blockTitle')
                                            ->label(__('Título del bloque'))
                                            ->helperText(__('Darle título para identificar el bloque'))
                                            ->live()
                                            ->placeholder(__('')),
                                        TextInput::make('blockAnchor')
                                            ->label(__('Ancla del bloque'))
                                            ->helperText(__('Navega a este elemento con este anchor al final de la url'))
                                            ->placeholder(fn ($get) => '#' . str($get('blockTitle'))->slug())
                                            ->readOnly(),
                                    ]),
                            ]),
                        Tabs\Tab::make(__('Avanzado'))
                            ->icon('heroicon-o-cog')
                            ->schema([
                                Grid::make([
                                    'default' => 1,
                                    'md' => 2,
                                ])
                                    ->schema([
                                        Select::make('mb')
                                            ->label(__('Margen inferior'))
                                            ->placeholder(__('Selecciona un tamaño'))
                                            ->helperText(__('Se aplica para separarlo del siguiente'))
                                            ->default(null)
                                            ->options([
                                                'mb-0' => __('Sin margen'),
                                                'mb-6' => __('Pequeño'),
                                                'mb-12' => __('Estandar'),
                                                'mb-24' => __('Grande'),
                                            ]),

                                        Select::make('mdMb')
                                            ->label(__('Margen inferior en pantallas grandes'))
                                            ->helperText(__('Solo si necesitas un margen diferente'))
                                            ->placeholder(__('Selecciona un tamaño'))
                                            ->default('md:mb-0')
                                            ->options([
                                                'md:mb-0' => __('Sin margen'),
                                                'md:mb-6' => __('Pequeño'),
                                                'md:mb-12' => __('Estandar'),
                                                'md:mb-24' => __('Grande'),
                                            ]),
                                    ]),
                                TagsInput::make('clases')
                                    ->label(__('Clases'))
                                    ->suggestions([
                                        'bg-white',
                                        'bg-gray',
                                        'bg-primary',
                                        'bg-secondary',
                                        'bg-primary-white',
                                        'bg-white-gray',
                                    ])
                                    ->helperText(__('Agrega clases a este bloque, debera conocer las clases existentes para utilizar esta opción'))
                                    ->placeholder(__('Agrega clases CSS separadas por comas')),

                                Grid::make([
                                    'default' => 1,
                                    'md' => 2,
                                ])
                                    ->schema([
                                        KeyValue::make('styles')
                                            ->label(__('Estilos'))
                                            ->helperText(__('Debera conocer CSS para usar esta opción avanzada'))
                                            ->keyLabel(__('Propiedad'))
                                            ->valueLabel(__('Valor'))
                                            ->keyPlaceholder('font-size')
                                            ->valuePlaceholder('2rem')
                                            ->reorderable(),

                                        KeyValue::make('stylesMd')
                                            ->label(__('Estilos en pantallas grandes'))
                                            ->helperText(__('Si quieres sobre escribir los estilos'))
                                            ->keyLabel(__('Propiedad'))
                                            ->valueLabel(__('Valor'))
                                            ->keyPlaceholder('font-size')
                                            ->valuePlaceholder('2rem')
                                            ->reorderable(),
                                    ]),
                                Toggle::make('hidden')
                                    ->label(__('Ocultar bloque'))
                                    ->offIcon('heroicon-o-eye')
                                    ->onIcon('heroicon-o-eye-slash')
                                    ->onColor('danger')
                                    ->offColor('success')
                                    ->default(false),
                            ]),
                    ])
                    ->contained(false),
            ]);

        if ($preview !== false) {
            $block->preview('blocks.' . $preview);
        }

        return $block;
    }
}

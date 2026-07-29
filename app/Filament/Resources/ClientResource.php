<?php

namespace App\Filament\Resources;

use App\Filament\Forms\Components\Field;
use App\Filament\Forms\Components\Image;
use App\Filament\Resources\Bases\ResourceBase;
use App\Filament\Resources\ClientResource\Pages;
use App\Filament\Templates\DefaultTemplate;
use App\Models\Client;
use Filament\Forms\Components\ColorPicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Repeater\TableColumn;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\ToggleButtons;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;

class ClientResource extends ResourceBase
{
    protected static ?string $model = Client::class;

    protected static ?string $modelLabel = 'Cliente';

    protected static ?string $pluralModelLabel = 'Clientes';

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-building-office-2';

    protected static function mainTab(Schema $schema): array
    {
        return [
            Section::make('Identidad del cliente')
                ->description('El logo se muestra antes del contenido compuesto con bloques.')
                ->aside()
                ->schema([
                    Image::make(
                        name: 'logo',
                        label: 'Logo',
                        width: '800',
                        height: '400',
                        directory: 'images/clients/logos',
                    ),
                    ColorPicker::make('color')
                        ->label('Color del cliente')
                        ->helperText('Se usa como fondo del encabezado en el popup de la home.')
                        ->hex()
                        ->required()
                        ->default('#FFFFFF'),
                    Select::make('popup_text_color')
                        ->label('Texto del encabezado')
                        ->helperText('Elegí negro o blanco según el contraste con el color del cliente.')
                        ->options([
                            'black' => 'Negro',
                            'white' => 'Blanco',
                        ])
                        ->required()
                        ->default('white')
                        ->native(false),
                    Toggle::make('is_featured')
                        ->label('Cliente destacado')
                        ->helperText('Los clientes destacados aparecen en el slider de la home.')
                        ->default(true),
                ]),
            ...DefaultTemplate::schema($schema),
        ];
    }

    protected static function additionalTabs(Schema $schema): array
    {
        return [
            Tabs\Tab::make('Trabajos')
                ->icon('heroicon-o-briefcase')
                ->schema([
                    Repeater::make('works')
                        ->label('Trabajos relacionados')
                        ->helperText('Cada trabajo enlaza a una URL externa y puede pertenecer a varias categorías.')
                        ->table([
                            TableColumn::make('Trabajo')->markAsRequired(),
                            TableColumn::make('Categorías')->markAsRequired(),
                            TableColumn::make('URL externa')->markAsRequired(),
                            TableColumn::make('Imagen'),
                            TableColumn::make('Descripción'),
                        ])
                        ->schema([
                            TextInput::make('title')
                                ->label('Trabajo')
                                ->required()
                                ->maxLength(255),
                            Select::make('categories')
                                ->label('Categorías')
                                ->options(Client::WORK_CATEGORIES)
                                ->multiple()
                                ->required(),
                            TextInput::make('external_url')
                                ->label('URL externa')
                                ->url()
                                ->required()
                                ->maxLength(2048),
                            Image::make(
                                name: 'image',
                                label: 'Imagen',
                                width: '1200',
                                height: '800',
                                directory: 'images/clients/works',
                            ),
                            Textarea::make('description')
                                ->label('Descripción')
                                ->rows(2)
                                ->maxLength(500),
                        ])
                        ->default([])
                        ->reorderableWithButtons()
                        ->cloneable()
                        ->columnSpanFull(),
                ]),
            Tabs\Tab::make('Testimonios')
                ->icon('heroicon-o-chat-bubble-left-right')
                ->schema([
                    Repeater::make('testimonials')
                        ->label('Testimonios')
                        ->schema([
                            TextInput::make('person')
                                ->label('Persona')
                                ->required()
                                ->maxLength(255),
                            TextInput::make('position')
                                ->label('Cargo')
                                ->required()
                                ->maxLength(255),
                            Image::make(
                                name: 'image',
                                label: 'Imagen',
                                width: '600',
                                height: '600',
                                directory: 'images/clients/testimonials',
                                forceRatio: true,
                            ),
                            Field::rich('testimonial', 'Testimonio')
                                ->required()
                                ->columnSpanFull(),
                        ])
                        ->columns(2)
                        ->default([])
                        ->reorderableWithButtons()
                        ->cloneable()
                        ->itemLabel(fn (array $state): ?string => $state['person'] ?? 'Nuevo testimonio')
                        ->columnSpanFull(),
                ]),
            Tabs\Tab::make('Preview')
                ->icon('heroicon-o-play-circle')
                ->schema([
                    Repeater::make('preview_items')
                        ->label('Canales de preview')
                        ->helperText('Se reproducen exactamente en este orden; el primer item será el primer canal.')
                        ->schema([
                            ToggleButtons::make('type')
                                ->label('Tipo de canal')
                                ->options([
                                    'testimonial' => 'Testimonio',
                                    'image' => 'Imagen',
                                    'video' => 'Video',
                                ])
                                ->icons([
                                    'testimonial' => 'heroicon-o-chat-bubble-left-right',
                                    'image' => 'heroicon-o-photo',
                                    'video' => 'heroicon-o-video-camera',
                                ])
                                ->default('image')
                                ->required()
                                ->inline()
                                ->reactive()
                                ->columnSpanFull(),
                            FileUpload::make('file')
                                ->label(fn (Get $get): string => $get('type') === 'video' ? 'Video' : 'Imagen')
                                ->acceptedFileTypes(fn (Get $get): array => $get('type') === 'video'
                                    ? ['video/mp4', 'video/webm', 'video/quicktime']
                                    : ['image/jpeg', 'image/png', 'image/webp', 'image/avif'])
                                ->disk('public')
                                ->directory('media/clients/previews')
                                ->visibility('public')
                                ->maxSize(102400)
                                ->required(fn (Get $get): bool => in_array($get('type'), ['image', 'video'], true))
                                ->visible(fn (Get $get): bool => in_array($get('type'), ['image', 'video'], true))
                                ->columnSpanFull(),
                            TextInput::make('duration_ms')
                                ->label('Duración personalizada (ms)')
                                ->helperText(fn (Get $get): string => $get('type') === 'video'
                                    ? 'Vacío: usa automáticamente la duración natural del video.'
                                    : 'Vacío: dura 1000 ms.')
                                ->numeric()
                                ->integer()
                                ->minValue(100)
                                ->maxValue(600000)
                                ->step(100)
                                ->placeholder('1000'),
                        ])
                        ->default([])
                        ->reorderableWithButtons()
                        ->cloneable()
                        ->itemLabel(fn (array $state): string => match ($state['type'] ?? null) {
                            'testimonial' => 'Testimonio',
                            'video' => filled($state['file'] ?? null) ? 'Video · '.basename((string) $state['file']) : 'Video',
                            'image' => filled($state['file'] ?? null) ? 'Imagen · '.basename((string) $state['file']) : 'Imagen',
                            default => 'Nuevo canal',
                        })
                        ->columnSpanFull(),
                ]),
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListClients::route('/'),
            'create' => Pages\CreateClient::route('/create'),
            'edit' => Pages\EditClient::route('/{record}/edit'),
        ];
    }
}

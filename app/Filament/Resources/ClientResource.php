<?php

namespace App\Filament\Resources;

use App\Filament\Forms\Components\Field;
use App\Filament\Forms\Components\Image;
use App\Filament\Forms\Components\MediaPicker;
use App\Filament\Resources\Bases\ResourceBase;
use App\Filament\Resources\ClientResource\Pages;
use App\Filament\Templates\ClientTemplate;
use App\Models\Client;
use Filament\Forms\Components\ColorPicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Repeater\TableColumn;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TagsInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\ToggleButtons;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;
use Filament\Tables\Table;

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
                ->description('El logo forma parte del hero propio de la vista Cliente.')
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
            Section::make('Hero del caso')
                ->description('Encabezado editorial fijo. Después de este hero se renderizan los bloques de categoría Cliente.')
                ->aside()
                ->schema([
                    TextInput::make('hero_eyebrow')
                        ->label('Volanta')
                        ->placeholder('Caso de cliente')
                        ->maxLength(100),
                    TextInput::make('hero_title')
                        ->label('Título principal')
                        ->helperText('Si queda vacío se usa el nombre del cliente.')
                        ->maxLength(180),
                    Textarea::make('hero_summary')
                        ->label('Resumen')
                        ->rows(4)
                        ->maxLength(800)
                        ->columnSpanFull(),
                    TextInput::make('relationship_since')
                        ->label('Inicio o duración de la relación')
                        ->placeholder('Desde 2018')
                        ->maxLength(80),
                    TagsInput::make('hero_services')
                        ->label('Capacidades aplicadas')
                        ->helperText('Usa etiquetas breves; se muestran como una lista editorial.')
                        ->columnSpanFull(),
                    ...MediaPicker::make(
                        type: 'hero_media_type',
                        image: 'hero_media_image',
                        video: 'hero_media_video',
                        alt: 'hero_media_alt',
                        placeholder: 'hero_media_placeholder',
                        autoplay: 'hero_media_autoplay',
                        directory: 'media/clients/heroes',
                        label: 'Media principal del hero',
                        width: '1800',
                        height: '1200',
                    ),
                ]),
            Section::make('Evidencia comercial para Socies')
                ->description('Estos datos delimitan qué puede mencionar la conversación. Publicar la página no habilita automáticamente el uso comercial.')
                ->aside()
                ->schema([
                    TextInput::make('public_name')
                        ->label('Nombre público')
                        ->helperText('Nombre exacto que Socies puede mostrar en el chat.')
                        ->maxLength(255),
                    TextInput::make('industry')
                        ->label('Rubro')
                        ->helperText('Ayuda a priorizar casos del mismo sector.')
                        ->maxLength(255),
                    Textarea::make('paco_summary')
                        ->label('Descripción breve aprobada')
                        ->rows(2)
                        ->maxLength(500),
                    Textarea::make('paco_chat_text')
                        ->label('Texto aprobado para Socies')
                        ->helperText('Opcional. Se usa literalmente o se acorta sin agregar afirmaciones.')
                        ->rows(3)
                        ->maxLength(700),
                    Toggle::make('paco_use_authorized')
                        ->label('Permiso de uso comercial')
                        ->helperText('Autoriza mencionar públicamente al cliente dentro de la conversación.')
                        ->default(false),
                    Toggle::make('paco_chat_enabled')
                        ->label('Disponible para el chat')
                        ->helperText('Solo se considera si también tiene permiso de uso y la página está publicada.')
                        ->default(false),
                ]),
            Section::make('Chat de cierre del caso')
                ->description('La última sección del caso abre la conversación de Socies y registra este cliente como origen de la consulta.')
                ->aside()
                ->schema([
                    Textarea::make('paco_closing_message')
                        ->label('Mensaje inicial')
                        ->helperText('Se muestra como el primer mensaje del chat. Si queda vacío, se usa el mensaje predeterminado.')
                        ->placeholder('Hola, ¿te gustaría hacer algo similar para tu organización? Contanos tu caso.')
                        ->rows(3)
                        ->maxLength(700)
                        ->columnSpanFull(),
                ]),
            ...ClientTemplate::schema($schema),
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
                        ->helperText('Cada trabajo puede usarse como evidencia solo con permiso explícito y contenido documentado.')
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
                            Textarea::make('problem')
                                ->label('Problema documentado')
                                ->rows(2)
                                ->maxLength(700),
                            Textarea::make('solution')
                                ->label('Qué hizo Socies')
                                ->rows(2)
                                ->maxLength(700),
                            Textarea::make('result')
                                ->label('Resultado documentado')
                                ->helperText('Dejar vacío si no existe evidencia verificable.')
                                ->rows(2)
                                ->maxLength(700),
                            Textarea::make('paco_text')
                                ->label('Texto breve aprobado para Socies')
                                ->rows(2)
                                ->maxLength(500),
                            TextInput::make('tags')
                                ->label('Tags de búsqueda')
                                ->helperText('Separados por coma: landing, donaciones, ONG.')
                                ->maxLength(500),
                            Toggle::make('use_authorized')
                                ->label('Permiso de uso comercial')
                                ->default(false),
                            Toggle::make('chat_enabled')
                                ->label('Mostrar en el chat')
                                ->default(false),
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
                            Textarea::make('short_quote')
                                ->label('Versión breve aprobada')
                                ->helperText('Si queda vacía, Socies puede acortar la cita exacta sin cambiar su sentido.')
                                ->rows(2)
                                ->maxLength(500)
                                ->columnSpanFull(),
                            TextInput::make('source_url')
                                ->label('Fuente o evidencia')
                                ->url()
                                ->maxLength(2048)
                                ->columnSpanFull(),
                            Toggle::make('use_authorized')
                                ->label('Permiso de uso comercial')
                                ->default(false),
                            Toggle::make('chat_enabled')
                                ->label('Mostrar en el chat')
                                ->default(false),
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

    public static function table(Table $table): Table
    {
        return parent::table($table)
            ->reorderable('sort_order')
            ->defaultSort('sort_order');
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

<?php

namespace App\Filament\Traits;

use App\Models\Route;
use Filament\Forms\Components as Component;
use Filament\Forms\Components\Section;
use Filament\Forms\Get;
use Filament\Forms\Set;
use Filament\Support\RawJs;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Str;
use App\Enums\Status;
use Filament\Forms\Components\Actions\Action;

trait HasRoute
{
    public static function formRoute($form): array
    {
        $modelClass = $form->getModel();
        $forceParent = $modelClass::$forceParent ?? null;
        $editLayout = $modelClass::$editLayout ?? false;
        $editDate = $modelClass::$editDate ?? false;

        return [
            Section::make(__('Configuración de SEO'))
                ->aside(true)
                ->columns(1)
                ->schema([
                    Component\TextInput::make('route.title')
                        ->label('Título')
                        ->placeholder('Ingresar titulo')
                        ->default(function (Get $get, ?Model $record) {
                            // If editing and route.title already exists, keep it
                            if ($record && $record->route && $record->route->title) {
                                return $record->route->title;
                            }

                            // If creating new or route.title is empty, try to get from resource title
                            $resourceTitle = $get('title') ?? ($record->title ?? null);
                            return $resourceTitle;
                        })
                        ->live(onBlur: true)
                        ->afterStateUpdated(function (Get $get, Set $set, ?string $operation, ?string $old, ?string $state, ?Model $record) {

                            if ($operation == 'edit' && $record->isPublished()) {
                                return;
                            }

                            if (($get('route.slug') ?? '') !== Str::slug($old)) {
                                return;
                            }

                            $set('route.slug', Str::slug($state));
                        })
                        ->required()
                        ->maxLength(255),

                    Component\FileUpload::make('route.image')
                        ->label('Imagen de portada')
                        ->image()
                        ->maxSize(1024)
                                    ->image()
                        ->imageEditor()
                        ->imageEditorMode(2)
                        ->preserveFilenames()
                        ->directory('images/routes')
                        ->imageResizeTargetWidth(1200)
                        ->imageResizeTargetHeight(630)
                        ->imageResizeMode('cover')
                        ->helperText('Sube una imagen para compartir en redes sociales. 1200 x 630 px. Tamaño máximo: 1MB.'),

                    Component\Select::make('route.parent_id')
                        ->label('Madre')
                        ->options(function (Get $get) {
                            return Route::query()
                                ->when($get('id'), function (Builder $query, $id) {
                                    $query->where('id', '!=', $id);
                                })
                                ->pluck('title', 'id');
                        })
                        ->searchable()
                        ->live()
                        ->nullable()
                        ->hidden(fn() => $forceParent !== null),

                    Component\TextInput::make('route.slug')
                        ->label('Url personalizada')
                        ->helperText('Ingresa "home" para redireccionar a ' . url('/'))
                        ->prefix(function ($get) {
                            $parentRoute = Route::find($get('route.parent_id'));
                            return $parentRoute ? url($parentRoute->getFullPath()) . '/' : url('/');
                        })
                        ->reactive()
                        ->mask(RawJs::make(<<<'JS'
                        function (value) {
                            return value
                            .replace(/ /g, '-')
                            .replace(/[^a-z-\s]+/g, '');
                        }
                    JS))
                        ->maxLength(200)
                        /*->unique(
                        table: 'routes', 
                        column: 'slug', 
                        ignorable: fn ($record) => $record->id === $record->route->routable_id ? $record->route : null
                    )**/
                        ->required()
                        ->suffixAction(
                            Action::make('go')
                                ->icon('heroicon-o-eye')
                                ->openUrlInNewTab(true)
                                ->url(fn($record): string => !$record ? '#' : $record->previewUrl)
                        ),

                    Component\ToggleButtons::make('route.status')
                        ->label('Estado')
                        ->options(Status::class)
                        ->inline()
                        ->default(Status::Draft)
                        ->required()
                        ->hiddenLabel(),
                ]),

            Section::make(__('Fecha'))
                ->aside(true)
                ->columns(1)
                ->visible(fn() => $editDate)
                ->schema([
                    Component\DateTimePicker::make('created_at')
                        ->label('Fecha de creación')
                        ->disabled()
                        ->readOnly(),
                    Component\DateTimePicker::make('updated_at')
                        ->label('Fecha de actualización')
                        ->disabled()
                        ->readOnly(),
                    Component\DateTimePicker::make('published_at')
                        ->label('Fecha de publicación')
                        ->default(now())
                        ->required()
                ])
                ->columns(3),


            Section::make(__('Diseño'))
                ->aside(true)
                ->columns(1)
                ->visible(fn() => $editLayout)
                ->schema([
                    Component\Select::make('route.layout')
                        ->label('')
                        ->options([
                            'default' => 'Default',
                            'hasIndex' => 'Con índice',
                            'modal' => 'Modal',
                            'home' => 'Home',
                        ])
                        ->default('default')
                    // ->required()
                ]),

            Section::make(__('Avanzado'))
                ->description('¡Importante! Asegúrate de que los códigos ingresados no contengan errores, ya que podrían afectar el funcionamiento de la página. No recomendamos copiar códigos sin conocimiento previo.')
                ->aside(true)
                ->columns(1)
                ->collapsed(true)
                ->schema([


                    Section::make(__('Scripts y CSS personalizados'))
                        ->collapsed()
                        ->schema([
                            Component\Textarea::make('route.custom_css')
                                ->label('CSS Personalizado')
                                ->placeholder('/* Ingresa tu CSS personalizado aquí */')
                                ->rows(8)
                                ->helperText('CSS que se aplicará únicamente a esta página. No incluyas las etiquetas <style>.')
                                ->columnSpanFull(),
                            Component\Textarea::make('route.header_scripts')
                                ->label('Scripts del Header')
                                ->placeholder('<!-- Scripts que se cargarán en el <head> -->')
                                ->rows(6)
                                ->helperText('Scripts que se ejecutarán en el header de la página. Incluye las etiquetas <script>.')
                                ->columnSpanFull(),

                            Component\Textarea::make('route.footer_scripts')
                                ->label('Scripts del Footer')
                                ->placeholder('<!-- Scripts que se cargarán antes del cierre del </body> -->')
                                ->rows(6)
                                ->helperText('Scripts que se ejecutarán al final de la página. Incluye las etiquetas <script>.')
                                ->columnSpanFull(),
                        ]),
                ]),
        ];
    }
}

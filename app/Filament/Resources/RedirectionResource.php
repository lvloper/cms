<?php

namespace App\Filament\Resources;

use App\Filament\Resources\RedirectionResource\Pages;
use App\Models\Redirection;
use Filament\Actions;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\TextInputColumn;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\BooleanColumn;
use Filament\Tables\Columns\BadgeColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Illuminate\Database\Eloquent\Builder;

class RedirectionResource extends Resource
{
    protected static ?string $model = Redirection::class;

    protected static ?string $modelLabel = 'Redirección';
    
    protected static ?string $pluralModelLabel = 'Redirecciones';

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-arrow-right-circle';
    
    protected static string|\UnitEnum|null $navigationGroup = 'Configuración';

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->schema([
                Section::make('Configuración de Redirección')
                    ->description('Configure las URLs de origen y destino para la redirección')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                TextInput::make('old_url')
                                    ->label('URL de Origen')
                                    ->helperText('URL relativa que será redirigida (ej: /vieja-pagina)')
                                    ->required()
                                    ->maxLength(255)
                                    ->rules(['regex:/^[a-zA-Z0-9\-_\/\?\&\=\.]+$/'])
                                    ->placeholder('vieja-pagina')
                                    ->suffixAction(
                                        Actions\Action::make('test_redirect')
                                            ->icon('heroicon-o-arrow-top-right-on-square')
                                            ->tooltip('Probar redirección')
                                            ->url(function ($get) {
                                                return $get('old_url') ? url('/' . ltrim($get('old_url'), '/')) : null;
                                            })
                                            ->openUrlInNewTab()
                                    ),
                                    
                                TextInput::make('new_url')
                                    ->label('URL de Destino')
                                    ->helperText('URL de destino (relativa como /nueva-pagina o absoluta con https://) - Opcional para modo borrador')
                                    ->maxLength(255)
                                    ->placeholder('nueva-pagina o https://ejemplo.com')
                                    ->suffixAction(
                                        Actions\Action::make('preview_destination')
                                            ->icon('heroicon-o-eye')
                                            ->tooltip('Ver destino')
                                            ->url(function ($get) {
                                                $url = $get('new_url');
                                                if (!$url) return null;
                                                
                                                // Si es una URL externa, devolverla tal como está
                                                if (str_starts_with($url, 'http://') || str_starts_with($url, 'https://')) {
                                                    return $url;
                                                }
                                                
                                                // Si es una URL relativa, convertirla a completa
                                                return url('/' . ltrim($url, '/'));
                                            })
                                            ->openUrlInNewTab()
                                    ),
                            ]),
                            
                        Grid::make(2)
                            ->schema([
                                Select::make('redirect_code')
                                    ->label('Código de Redirección')
                                    ->options(Redirection::getRedirectCodes())
                                    ->default(301)
                                    ->required()
                                    ->helperText('301 para redirecciones permanentes, 302 para temporales'),
                                    
                                Toggle::make('is_active')
                                    ->label('Activa')
                                    ->default(true)
                                    ->helperText('Desactive para pausar la redirección sin eliminarla'),
                            ]),
                            
                        Textarea::make('description')
                            ->label('Descripción')
                            ->helperText('Descripción opcional para documentar el propósito de esta redirección')
                            ->rows(3)
                            ->columnSpanFull(),
                    ])
                    ->columns(2),
            ])
            ->columns(1);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                IconColumn::make('view_current_icon')
                    ->label('')
                    ->getStateUsing(fn() => true)
                    ->icon('heroicon-o-arrow-up-right')
                    ->tooltip('Abrir en el sitio')
                    ->url(fn($record) => url($record->old_url))
                    ->openUrlInNewTab()
                    ->alignCenter(),
                TextColumn::make('old_url')
                    ->label('URL Origen')
                    ->searchable()
                    ->sortable()
                    ->extraAttributes([
                        'class' => 'max-w-[380px] overflow-hidden text-ellipsis whitespace-nowrap hover:overflow-visible hover:whitespace-normal transition-all duration-300',
                    ])
                    ->url(fn($record) => url($record->formatted_old_url ?? $record->old_url))
                    ->openUrlInNewTab()
                    ->tooltip('Abrir en el sitio actual'),
                    
                TextInputColumn::make('new_url')
                    ->label('URL Destino')
                    ->extraAttributes([
                        'class' => 'max-w-[380px] overflow-hidden text-ellipsis whitespace-nowrap hover:overflow-visible hover:whitespace-normal transition-all duration-300',
                    ])
                    ->placeholder('Sin destino (Borrador)')
                    ->rules(['nullable','string','max:255'])
                    ->tooltip('Click para editar')
                    ->alignEnd(),

                IconColumn::make('pick_route')
                    ->label('')
                    ->getStateUsing(fn() => true)
                    ->icon('heroicon-m-magnifying-glass')
                    ->action(
                        Actions\Action::make('pick-route')
                            ->label('')
                            ->modalHeading('Seleccionar ruta interna')
                            ->modalSubmitActionLabel('Aplicar')
                            ->form([
                                \Filament\Forms\Components\Select::make('route_id')
                                    ->label('Ruta')
                                    ->options(\App\Models\Route::getSelectOptions())
                                    ->searchable()
                                    ->getSearchResultsUsing(fn(string $search): array => \App\Models\Route::getSelectOptions($search))
                                    ->preload()
                                    ->required(),
                                \Filament\Forms\Components\TextInput::make('anchor')
                                    ->label('Ancla (opcional)')
                                    ->placeholder('seccion-1')
                                    ->helperText('Se agregará al final de la URL con # (ej: #seccion-1)')
                                    ->maxLength(100),
                            ])
                            ->action(function (array $data, $record) {
                                if (!isset($data['route_id'])) return;
                                
                                $route = \App\Models\Route::find($data['route_id']);
                                if (! $route) return;

                                // Construir la URL final con el anchor si existe
                                $finalUrl = $route->full_slug;
                                if (!empty($data['anchor'])) {
                                    $finalUrl .= '#' . trim($data['anchor'], '#');
                                }

                                $record->update(['new_url' => $finalUrl]);
                            })
                    )
                    ->tooltip('Buscar ruta interna')
                    ->alignCenter(),
                    
                BadgeColumn::make('redirect_code')
                    ->label('Código')
                    ->colors([
                        'success' => 301,
                        'warning' => 302,
                        'primary' => [303, 307, 308],
                    ])
                    ->sortable(),
                    
                BooleanColumn::make('is_active')
                    ->label('Activa')
                    ->sortable()
                    ->action(function ($record) {
                        $record->update(['is_active' => !$record->is_active]);
                    }),
                    
                TextColumn::make('description')
                    ->label('Descripción')
                    ->limit(50)
                    ->tooltip(function (TextColumn $column): ?string {
                        $state = $column->getState();
                        if (strlen($state) <= 50) {
                            return null;
                        }
                        return $state;
                    })
                    ->toggleable(isToggledHiddenByDefault: true),
                    
                TextColumn::make('created_at')
                    ->label('Creada')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('redirect_code')
                    ->label('Código de Redirección')
                    ->options(Redirection::getRedirectCodes()),
                    
                SelectFilter::make('is_active')
                    ->label('Estado')
                    ->options([
                        1 => 'Activa',
                        0 => 'Inactiva',
                    ]),
                    
                Filter::make('draft_redirections')
                    ->query(function (Builder $query) {
                        return $query->whereNull('new_url');
                    })
                    ->label('Borradores (Sin destino)'),
                    
                Filter::make('external_urls')
                    ->query(function (Builder $query) {
                        return $query->where('new_url', 'LIKE', 'http%');
                    })
                    ->label('URLs Externas'),
            ])
            ->actions([
                Actions\EditAction::make(),
                Actions\DeleteAction::make(),
                // view_current action removed (now icon column at start)
            ])
            ->headerActions([
                Actions\ExportAction::make()
                    ->label('Exportar')
                    ->icon('heroicon-o-arrow-down-tray')
                    ->exporter(\App\Filament\Exports\RedirectionExporter::class)
                    
                     
                    ->before(function () {
                        \Illuminate\Support\Facades\Config::set('queue.default', 'sync');
                    }),
                // ImportAction::make()
                //     ->label('Importar Excel')
                //     ->icon('heroicon-o-arrow-up-tray')
                //     ->importer(\App\Filament\Imports\RedirectionImporter::class)
                //     ->modalHeading('Importar Redirecciones desde Excel')
                //     ->modalSubheading('Formato: old_url, new_url (opcional), redirect_code, is_active, description'),
            ])
            ->bulkActions([
                Actions\DeleteBulkAction::make(),
                Actions\BulkAction::make('toggle_active')
                    ->label('Alternar Estado')
                    ->icon('heroicon-o-arrow-path')
                    ->action(function ($records) {
                        foreach ($records as $record) {
                            $record->update(['is_active' => !$record->is_active]);
                        }
                    }),
            ])
            ->defaultSort('created_at', 'desc');
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ManageRedirections::route('/'),
        ];
    }
}

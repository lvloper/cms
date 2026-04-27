<?php

namespace App\Filament\Resources\Bases;

use Filament\Resources\Resource;
use App\Filament\Traits\HasRoute;
use App\Models\Page;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Forms\Components\Tabs;
use Filament\Schemas\Schema;
use App\Filament\Templates\DefaultTemplate;
use App\Filament\Templates\ModalTemplate;





abstract class ResourceBase extends Resource
{
    use HasRoute;


    protected static function mainTab(Schema $schema): array
    {
        $record = $schema->getRecord();

        if ($record instanceof \Illuminate\Database\Eloquent\Model && $record->route && $record->route->layout == 'modal') {
            return [
                ...ModalTemplate::schema($schema)
            ];
        }

        return [
            ...DefaultTemplate::schema($schema)
        ];
    }

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->schema([
                Tabs::make('pageTabs')
                    ->tabs([
                        Tabs\Tab::make('Contenido')
                            ->icon('heroicon-o-document-text')
                            ->schema(static::mainTab($schema)),
                        Tabs\Tab::make(__('Configuración de página'))
                            ->icon('heroicon-o-cog')
                            ->schema([
                                ...HasRoute::formRoute($schema),
                            ]),
                    ])
                    ->contained(false)
            ])

            ->columns(1);
    }


    public static function table(Table $table): Table
    {
        $model = static::getModel();

        $hasPublishedDate = property_exists($model, 'hasPublishedDate');

        return $table
            ->columns([
                Tables\Columns\TextColumn::make('route.status')
                    ->badge()
                    ->width('100px')
                    ->label('Estado'),
                Tables\Columns\TextColumn::make('route.title')
                    ->searchable()
                    ->extraAttributes([
                        'class' => 'max-w-[380px] overflow-hidden text-ellipsis whitespace-nowrap hover:overflow-visible hover:whitespace-normal transition-all duration-300',
                    ])
                    ->sortable()
                    ->description(fn($record) => url($record->route ? $record->route->getFullPath() : ''))
                    ->label('Entrada'),

                Tables\Columns\TextColumn::make($hasPublishedDate ? 'published_at' : 'created_at')
                    ->since()
                    ->label($hasPublishedDate ? 'Publicado' : 'Creado')
                    ->dateTimeTooltip()
                    ->sortable()
                    ->color(fn($record) =>  $hasPublishedDate ? $record->published_at?->isAfter(now()) ? 'warning' : 'default' : 'default'),
                Tables\Columns\TextColumn::make('updated_at')
                    ->since()
                    ->sortable()
                    ->label('Actualizado')
                    ->dateTimeTooltip(),
            ])
            ->defaultSort($hasPublishedDate ? 'published_at' : 'id', 'desc')
            ->filters([
                // Filtro por fecha de creación
                \Filament\Tables\Filters\Filter::make('created_at')
                    ->form([
                        \Filament\Forms\Components\DatePicker::make('Creado el'),
                        \Filament\Forms\Components\DatePicker::make('Hasta el'),
                    ])
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\Action::make('preview')
                    ->label('Ver')
                    ->url(fn($record) => $record->preview_url)
                    ->icon('heroicon-o-eye')
                    ->color('primary')
                    ->openUrlInNewTab(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }
}

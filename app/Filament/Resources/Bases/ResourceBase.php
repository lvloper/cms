<?php

namespace App\Filament\Resources\Bases;

use Filament\Resources\Resource;
use App\Filament\Traits\HasRoute;
use App\Models\Page;
use Filament\Actions;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\View;
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
        $tabs = [
            Tabs\Tab::make('Contenido')
                ->icon('heroicon-o-document-text')
                ->schema(static::mainTab($schema)),
            Tabs\Tab::make(__('Configuración de página'))
                ->icon('heroicon-o-cog')
                ->schema([
                    ...static::formRoute($schema),
                ]),
        ];

        if ($schema->getOperation() === 'edit') {
            $tabs[] = Tabs\Tab::make('Historial')
                ->icon('heroicon-o-clock')
                ->schema([
                    View::make('filament.admin.pages.history-tab'),
                ]);
        }

        return $schema
            ->schema([
                Tabs::make('pageTabs')
                    ->tabs($tabs)
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
                Actions\EditAction::make(),
                Actions\Action::make('preview')
                    ->label('Ver')
                    ->url(fn($record) => $record->preview_url)
                    ->icon('heroicon-o-eye')
                    ->color('primary')
                    ->openUrlInNewTab(),
                Actions\DeleteAction::make()
                    ->hidden(fn($record) => method_exists($record, 'isProtected') && $record->isProtected()),
            ])
            ->bulkActions([
                Actions\BulkActionGroup::make([
                    Actions\DeleteBulkAction::make()
                        ->hidden(fn($records) => $records && $records->contains(fn($r) => method_exists($r, 'isProtected') && $r->isProtected())),
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

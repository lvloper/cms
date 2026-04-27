<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ConfigurationResource\Pages;
use App\Filament\Resources\ConfigurationResource\RelationManagers;
use App\Models\Configuration;
use App\Filament\Traits\FormShortcuts;
use Filament\Forms;
use Filament\Schemas\Schema;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Filament\Forms\Components\ToggleButtons;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Group;
use Filament\Forms\Components\Section;
use Filament\Tables\Columns\TextColumn;
use Filament\Forms\Get;
use Filament\Forms\Set;
use Illuminate\Support\Facades\Auth;

class ConfigurationResource extends Resource
{
    use FormShortcuts;
    
    protected static ?string $model = Configuration::class;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-cog-6-tooth';
    
    protected static ?string $navigationLabel = 'Configuraciones';
    
    protected static ?string $modelLabel = 'Configuración';
    
    protected static ?string $pluralModelLabel = 'Configuraciones';
    
    protected static string|\UnitEnum|null $navigationGroup = 'Configuración';

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->schema([
                Section::make('Información básica')
                    ->hidden(fn (Get $get, $record) => $record !== null)
                    ->schema([
                        TextInput::make('key')
                            ->label('Clave')
                            ->required()
                            ->unique(ignorable: fn ($record) => $record)
                            ->rules(['regex:/^[a-z0-9_-]+$/'])
                            ->helperText('Solo letras minúsculas, números, guiones y guiones bajos')
                            ->disabled(fn ($record) => $record && !Auth::user()->hasRole('super_admin'))
                            ->columnSpan(1),

                        ToggleButtons::make('type')
                            ->label('Tipo de valor')
                            ->options(Configuration::getTypes())
                            ->required()
                            ->inline()
                            ->disabled(fn ($record) => $record && !Auth::user()->hasRole('super_admin'))
                            ->reactive()
                            ->columnSpan(1),
                    ])
                    ->columns(2),

                Section::make('Valor')
                    ->hidden(fn (Get $get): bool => !$get('type'))
                    ->schema([
                        Group::make([
                            // Texto simple
                            Forms\Components\Textarea::make('value.text')
                                ->label('Valor')
                                ->visible(fn (Get $get): bool => $get('type') === 'text')
                                ->required(fn (Get $get): bool => $get('type') === 'text'),

                            // Texto enriquecido
                            static::Rich('value.rich_content', 'basic')
                                ->label('Contenido')
                                ->visible(fn (Get $get): bool => $get('type') === 'rich_text')
                                ->required(fn (Get $get): bool => $get('type') === 'rich_text'),

                            // URL/Enlace
                            Group::make([
                                static::RoutePicker(
                                    'value.route',
                                    'URL/Enlace',
                                    required: true
                                )
                            ])
                                ->visible(fn (Get $get): bool => $get('type') === 'url'),

                            // Imagen
                            static::Image(
                                'value.image',
                                'Imagen',
                                required: true
                            )
                                ->visible(fn (Get $get): bool => $get('type') === 'image'),
                        ])
                    ])
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('key')
                    ->label('Clave')
                    ->searchable()
                    ->sortable()
                    ->copyable(),

                Tables\Columns\TextColumn::make('type')
                    ->label('Tipo')
                    ->formatStateUsing(fn (string $state): string => Configuration::getTypes()[$state] ?? $state)
                    ->badge()
                    ->color(fn (Configuration $record): string => $record->getTypeBadgeColor()),

                TextColumn::make('value_preview')
                    ->label('Vista previa del valor')
                    ->getStateUsing(function (Configuration $record): string {
                        return match($record->type) {
                            'text' => $record->value['text'] ?? '',
                            'rich_text' => strip_tags($record->value['rich_content'] ?? '') ?: 'Contenido HTML',
                            'url' => 'Enlace configurado',
                            'image' => $record->value['image'] ? 'Imagen subida' : 'Sin imagen',
                            default => 'Valor configurado',
                        };
                    })
                    ->limit(50),

                TextColumn::make('updated_at')
                    ->label('Última actualización')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('type')
                    ->label('Tipo')
                    ->options(Configuration::getTypes()),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('key');
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListConfigurations::route('/'),
            'create' => Pages\CreateConfiguration::route('/create'),
            // 'edit' => Pages\EditConfiguration::route('/{record}/edit'),
        ];
    }
}

<?php

namespace App\Filament\Resources;

use App\Filament\Resources\MenuResource\Pages;
use App\Filament\Resources\MenuResource\RelationManagers;
use App\Models\Menu;
use Filament\Actions;
use Filament\Forms;
use Filament\Schemas\Schema;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Str;
use Filament\Schemas\Components\Utilities\Set;

use App\Filament\Forms\Components\RoutePicker;

class MenuResource extends Resource
{
    protected static ?string $model = Menu::class;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-bars-4';

    // protected static string|\UnitEnum|null $navigationGroup = 'Site';

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->schema([
                Forms\Components\TextInput::make('name')
                    ->label('Nombre')
                    ->required()
                    ->live(onBlur: true)
                    ->afterStateUpdated(function (?string $state, Set $set) {
                        if (! $state) {
                            return;
                        }

                        $set('slug', Str::slug($state));
                    })
                    ->maxLength(255),
                Forms\Components\TextInput::make('slug')
                    ->required()
                    ->unique(ignoreRecord: true)
                    ->disabledOn('edit')
                    ->maxLength(255),
                Forms\Components\Repeater::make('items')
                    ->label('Ítems')
                    ->columnSpanFull()
                    ->reorderableWithButtons()
                    ->schema([
                        Forms\Components\Hidden::make('_token')
                            ->default(fn (): string => (string) Str::ulid()),
                        Forms\Components\TextInput::make('label')
                            ->label('Nombre')
                            ->required(),

                        RoutePicker::make('route')
                            ->required()
                            ->allowAnchor(),
                        Forms\Components\Repeater::make('children')
                            ->label('Subítems')
                            ->reorderableWithButtons()
                            ->schema([
                                Forms\Components\Hidden::make('_token')
                                    ->default(fn (): string => (string) Str::ulid()),
                                Forms\Components\TextInput::make('label')
                                    ->label('Nombre')
                                    ->required(),
                                RoutePicker::make('route')
                                    ->required()
                                    ->allowAnchor(),
                            ])
                            ->default([])
                            ->collapsed(),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('Nombre')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('slug'),
            ])
            ->filters([
                //
            ])
            ->actions([
                Actions\EditAction::make(),
            ])
            ->bulkActions([
                Actions\BulkActionGroup::make([
                    Actions\DeleteBulkAction::make(),
                ]),
            ]);
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
            'index' => Pages\ListMenus::route('/'),
            'create' => Pages\CreateMenu::route('/create'),
            'edit' => Pages\EditMenu::route('/{record}/edit'),
        ];
    }
}

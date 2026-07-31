<?php

declare(strict_types=1);

namespace App\Filament\Resources;

use App\Filament\Resources\PacoIntentResource\Pages\ManagePacoIntents;
use App\Models\Paco\Intent;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

final class PacoIntentResource extends Resource
{
    protected static ?string $model = Intent::class;

    protected static ?string $modelLabel = 'Intención';

    protected static ?string $pluralModelLabel = 'Intenciones';

    protected static ?string $navigationLabel = 'Intenciones';

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-sparkles';

    protected static string|\UnitEnum|null $navigationGroup = 'Conversaciones';

    protected static ?int $navigationSort = 20;

    public static function form(Schema $schema): Schema
    {
        return $schema->columns(1)->schema([
            Section::make('Clasificación')->schema([
                TextInput::make('code')->label('Código')->required()->alphaDash()->unique(ignoreRecord: true),
                TextInput::make('name')->label('Nombre')->required(),
                Select::make('type')->label('Tipo')->options([
                    'commercial' => 'Comercial', 'support' => 'Soporte', 'vendor' => 'Proveedor', 'other' => 'Otro',
                ])->required(),
                Select::make('status')->label('Estado')->options(['active' => 'Activa', 'inactive' => 'Inactiva'])->required(),
                Textarea::make('description')->label('Descripción para clasificación')->rows(4)->columnSpanFull(),
            ])->columns(1),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table->columns([
            TextColumn::make('name')->label('Intención')->searchable()->sortable()->description(fn (Intent $record): string => $record->code),
            TextColumn::make('type')->label('Tipo')->badge(),
            TextColumn::make('status')->label('Estado')->badge(),
            TextColumn::make('description')->label('Descripción')->limit(60),
        ])->filters([SelectFilter::make('type')->options([
            'commercial' => 'Comercial', 'support' => 'Soporte', 'vendor' => 'Proveedor', 'other' => 'Otro',
        ])])->actions([EditAction::make(), DeleteAction::make()])
            ->bulkActions([BulkActionGroup::make([DeleteBulkAction::make()])]);
    }

    public static function getPages(): array
    {
        return ['index' => ManagePacoIntents::route('/')];
    }
}

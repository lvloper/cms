<?php

declare(strict_types=1);

namespace App\Filament\Resources;

use App\Filament\Resources\PacoResponseBlockResource\Pages\ManagePacoResponseBlocks;
use App\Models\Paco\ResponseBlock;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TagsInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

final class PacoResponseBlockResource extends Resource
{
    protected static ?string $model = ResponseBlock::class;

    protected static ?string $modelLabel = 'Bloque de respuesta';

    protected static ?string $pluralModelLabel = 'Bloques de respuesta';

    protected static ?string $navigationLabel = 'Respuestas';

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-chat-bubble-left-right';

    protected static string|\UnitEnum|null $navigationGroup = 'Conversaciones';

    protected static ?int $navigationSort = 50;

    public static function form(Schema $schema): Schema
    {
        return $schema->columns(1)->schema([
            Section::make('Respuesta aprobada')->schema([
                TextInput::make('code')->label('Código')->required()->alphaDash()->unique(ignoreRecord: true),
                Select::make('block_type')->label('Uso')->options([
                    'opening' => 'Apertura', 'bridge' => 'Puente', 'closing' => 'Cierre', 'unsupported' => 'Fuera de alcance', 'error' => 'Error',
                ])->required(),
                Select::make('intent_id')->label('Intención')->relationship('intent', 'name')->searchable()->preload(),
                TextInput::make('stage')->label('Etapa'),
                Textarea::make('text')->label('Texto aprobado')->required()->rows(5)->columnSpanFull(),
                TagsInput::make('allowed_variables')->label('Variables permitidas')->placeholder('name')->columnSpanFull(),
                Select::make('adaptation_mode')->label('Adaptación')->options([
                    'exact' => 'Texto exacto', 'variables' => 'Solo variables', 'guided' => 'Adaptación guiada',
                ])->required(),
                Select::make('status')->label('Estado')->options(['active' => 'Activo', 'inactive' => 'Inactivo'])->required(),
                TextInput::make('priority')->label('Prioridad')->numeric()->minValue(1)->required(),
                TextInput::make('version')->label('Versión')->numeric()->minValue(1)->required(),
            ])->columns(1),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table->columns([
            TextColumn::make('code')->label('Código')->searchable()->sortable(),
            TextColumn::make('block_type')->label('Uso')->badge(),
            TextColumn::make('intent.name')->label('Intención')->placeholder('General'),
            TextColumn::make('text')->label('Texto')->limit(70)->wrap(),
            TextColumn::make('adaptation_mode')->label('Adaptación')->badge(),
            TextColumn::make('status')->label('Estado')->badge(),
        ])->filters([SelectFilter::make('block_type')->options([
            'opening' => 'Apertura', 'bridge' => 'Puente', 'closing' => 'Cierre', 'unsupported' => 'Fuera de alcance', 'error' => 'Error',
        ])])->actions([EditAction::make(), DeleteAction::make()])
            ->bulkActions([BulkActionGroup::make([DeleteBulkAction::make()])]);
    }

    public static function getPages(): array
    {
        return ['index' => ManagePacoResponseBlocks::route('/')];
    }
}

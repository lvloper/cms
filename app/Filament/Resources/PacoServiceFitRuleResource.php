<?php

declare(strict_types=1);

namespace App\Filament\Resources;

use App\Filament\Resources\PacoServiceFitRuleResource\Pages\ManagePacoServiceFitRules;
use App\Models\Paco\ServiceFitRule;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\KeyValue;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TagsInput;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

final class PacoServiceFitRuleResource extends Resource
{
    protected static ?string $model = ServiceFitRule::class;

    protected static ?string $modelLabel = 'Regla de servicio';

    protected static ?string $pluralModelLabel = 'Alcance de servicios';

    protected static ?string $navigationLabel = 'Qué hacemos';

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-check-badge';

    protected static string|\UnitEnum|null $navigationGroup = 'Conversaciones';

    protected static ?int $navigationSort = 60;

    public static function form(Schema $schema): Schema
    {
        return $schema->columns(1)->schema([
            Section::make('Decisión de alcance')->description('Estas reglas son la fuente editable de trabajos aceptados, condicionales o no ofrecidos.')->schema([
                TextInput::make('code')->label('Código')->required()->alphaDash()->unique(ignoreRecord: true),
                Select::make('intent_id')->label('Intención')->relationship('intent', 'name')->searchable()->preload(),
                Select::make('status')->label('Resultado')->options([
                    'supported' => 'Lo hacemos', 'conditional' => 'Requiere revisión', 'unsupported' => 'No lo hacemos', 'unknown' => 'Sin definir',
                ])->required(),
                Select::make('approved_response_block_id')->label('Respuesta aprobada')->relationship('responseBlock', 'code')->searchable()->preload(),
                TextInput::make('priority')->label('Prioridad')->numeric()->minValue(1)->required(),
                TextInput::make('version')->label('Versión')->numeric()->minValue(1)->required(),
                Toggle::make('active')->label('Activa')->required(),
                KeyValue::make('conditions')->label('Condiciones')->columnSpanFull(),
                TagsInput::make('alternative_service_ids')->label('IDs de alternativas')->columnSpanFull(),
            ])->columns(1),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table->columns([
            TextColumn::make('code')->label('Regla')->searchable()->sortable(),
            TextColumn::make('intent.name')->label('Intención')->searchable(),
            TextColumn::make('status')->label('Resultado')->badge(),
            TextColumn::make('responseBlock.code')->label('Respuesta')->placeholder('Predeterminada'),
            TextColumn::make('priority')->label('Prioridad')->numeric()->sortable(),
            IconColumn::make('active')->label('Activa')->boolean(),
        ])->filters([SelectFilter::make('status')->options([
            'supported' => 'Lo hacemos', 'conditional' => 'Requiere revisión', 'unsupported' => 'No lo hacemos', 'unknown' => 'Sin definir',
        ])])->actions([EditAction::make(), DeleteAction::make()])
            ->bulkActions([BulkActionGroup::make([DeleteBulkAction::make()])])
            ->defaultSort('priority');
    }

    public static function getPages(): array
    {
        return ['index' => ManagePacoServiceFitRules::route('/')];
    }
}

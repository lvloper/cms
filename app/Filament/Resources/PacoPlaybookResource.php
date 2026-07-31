<?php

declare(strict_types=1);

namespace App\Filament\Resources;

use App\Filament\Resources\PacoPlaybookResource\Pages\ManagePacoPlaybooks;
use App\Models\Paco\Playbook;
use App\Models\Paco\Question;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\KeyValue;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

final class PacoPlaybookResource extends Resource
{
    protected static ?string $model = Playbook::class;

    protected static ?string $modelLabel = 'Playbook';

    protected static ?string $pluralModelLabel = 'Playbooks';

    protected static ?string $navigationLabel = 'Playbooks';

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-queue-list';

    protected static string|\UnitEnum|null $navigationGroup = 'Conversaciones';

    protected static ?int $navigationSort = 30;

    public static function form(Schema $schema): Schema
    {
        return $schema->columns(1)->schema([
            Section::make('Estrategia')->schema([
                TextInput::make('code')->label('Código')->required()->alphaDash()->unique(ignoreRecord: true),
                TextInput::make('name')->label('Nombre')->required(),
                Select::make('status')->label('Estado')->options([
                    'active' => 'Activo', 'draft' => 'Borrador', 'inactive' => 'Inactivo',
                ])->required(),
                TextInput::make('version')->label('Versión')->numeric()->minValue(1)->required(),
                Textarea::make('objective')->label('Objetivo')->required()->rows(3)->columnSpanFull(),
                Select::make('intents')->label('Intenciones')->relationship('intents', 'name')->multiple()->preload()->searchable()->columnSpanFull(),
            ])->columns(1),
            Section::make('Límites y suficiencia')->schema([
                TextInput::make('max_interactions')->label('Interacciones máximas')->numeric()->minValue(2)->maxValue(20)->required(),
                TextInput::make('max_questions_after_contact')->label('Preguntas luego del contacto')->numeric()->minValue(0)->maxValue(5)->required(),
                TextInput::make('minimum_sufficiency_score')->label('Umbral de suficiencia')->numeric()->minValue(0)->maxValue(1)->step(0.05)->required(),
                KeyValue::make('settings')->label('Ajustes adicionales')->columnSpanFull(),
            ])->columns(1),
            Section::make('Campos a obtener')->description('El orden de prioridad define qué pregunta aparece primero.')->schema([
                Repeater::make('fields')->relationship()->schema([
                    TextInput::make('field_code')->label('Campo')->required()->alphaDash(),
                    Select::make('importance')->label('Importancia')->options([
                        'required' => 'Obligatorio', 'useful' => 'Útil', 'optional' => 'Opcional',
                    ])->required(),
                    Select::make('question_id')
                        ->label('Pregunta')
                        ->relationship('question', 'prompt')
                        ->getOptionLabelFromRecordUsing(
                            fn (Question $record): string => $record->short_prompt ?: $record->prompt,
                        )
                        ->searchable(['short_prompt', 'prompt'])
                        ->preload()
                        ->required(),
                    TextInput::make('priority')->label('Prioridad')->numeric()->minValue(1)->required(),
                    KeyValue::make('ask_condition')->label('Condición para preguntar')->columnSpanFull(),
                ])->columns(1)->defaultItems(0)->reorderableWithButtons()->columnSpanFull(),
            ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table->columns([
            TextColumn::make('name')->label('Playbook')->searchable()->sortable()->description(fn (Playbook $record): string => $record->code),
            TextColumn::make('status')->label('Estado')->badge(),
            TextColumn::make('intents.name')->label('Intenciones')->badge()->limitList(3),
            TextColumn::make('fields_count')->label('Campos')->counts('fields'),
            TextColumn::make('max_interactions')->label('Máx. turnos')->numeric(),
            TextColumn::make('version')->label('Versión')->numeric(),
        ])->filters([SelectFilter::make('status')->options([
            'active' => 'Activo', 'draft' => 'Borrador', 'inactive' => 'Inactivo',
        ])])->actions([EditAction::make(), DeleteAction::make()])
            ->bulkActions([BulkActionGroup::make([DeleteBulkAction::make()])]);
    }

    public static function getPages(): array
    {
        return ['index' => ManagePacoPlaybooks::route('/')];
    }
}

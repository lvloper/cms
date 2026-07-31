<?php

declare(strict_types=1);

namespace App\Filament\Resources;

use App\Filament\Resources\PacoQuestionResource\Pages\ManagePacoQuestions;
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
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

final class PacoQuestionResource extends Resource
{
    protected static ?string $model = Question::class;

    protected static ?string $modelLabel = 'Pregunta';

    protected static ?string $pluralModelLabel = 'Preguntas';

    protected static ?string $navigationLabel = 'Preguntas';

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-question-mark-circle';

    protected static string|\UnitEnum|null $navigationGroup = 'Conversaciones';

    protected static ?int $navigationSort = 40;

    public static function form(Schema $schema): Schema
    {
        return $schema->columns(1)->schema([
            Section::make('Pregunta')->schema([
                TextInput::make('code')->label('Código')->required()->alphaDash()->unique(ignoreRecord: true),
                TextInput::make('field_code')->label('Campo que completa')->required()->alphaDash(),
                Select::make('component_type')->label('Tipo de respuesta')->options([
                    'text_input' => 'Texto', 'single_select' => 'Selección única', 'multi_select' => 'Selección múltiple', 'date' => 'Fecha',
                ])->required()->live(),
                Textarea::make('prompt')->label('Texto para la persona')->required()->rows(3)->columnSpanFull(),
                Select::make('status')->label('Estado')->options(['active' => 'Activa', 'inactive' => 'Inactiva'])->required(),
                TextInput::make('version')->label('Versión')->numeric()->minValue(1)->required(),
                Toggle::make('is_skippable')->label('Se puede omitir'),
                Toggle::make('is_sensitive')->label('Dato sensible'),
            ])->columns(1),
            Section::make('Opciones')->schema([
                Repeater::make('options')->schema([
                    TextInput::make('value')->label('Valor')->required(),
                    TextInput::make('label')->label('Etiqueta')->required(),
                    Toggle::make('allow_detail')->label('Permitir aclaración escrita')->default(false),
                    Toggle::make('detail_required')->label('Aclaración obligatoria')->default(false),
                    TextInput::make('detail_label')->label('Pregunta para la aclaración'),
                    TextInput::make('detail_placeholder')->label('Ejemplo dentro del campo'),
                    TextInput::make('detail_max_length')->label('Máximo de caracteres')->numeric()->minValue(3)->default(600),
                ])->columns(1)->defaultItems(0)->columnSpanFull(),
                KeyValue::make('validation_schema')->label('Validación adicional')->columnSpanFull(),
            ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table->columns([
            TextColumn::make('prompt')
                ->label('Pregunta')
                ->searchable()
                ->sortable()
                ->limit(100)
                ->tooltip(fn (Question $record): string => $record->prompt)
                ->description(fn (Question $record): string => $record->code),
            TextColumn::make('field_code')->label('Campo')->searchable()->badge(),
            TextColumn::make('component_type')->label('Control')->badge(),
            IconColumn::make('is_skippable')->label('Omitible')->boolean(),
            IconColumn::make('is_sensitive')->label('Sensible')->boolean(),
            TextColumn::make('status')->label('Estado')->badge(),
        ])->filters([SelectFilter::make('component_type')->options([
            'text_input' => 'Texto', 'single_select' => 'Selección única', 'multi_select' => 'Selección múltiple', 'date' => 'Fecha',
        ])])->actions([EditAction::make(), DeleteAction::make()])
            ->bulkActions([BulkActionGroup::make([DeleteBulkAction::make()])]);
    }

    public static function getPages(): array
    {
        return ['index' => ManagePacoQuestions::route('/')];
    }
}

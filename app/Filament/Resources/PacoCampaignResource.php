<?php

declare(strict_types=1);

namespace App\Filament\Resources;

use App\Filament\Resources\PacoCampaignResource\Pages\ManagePacoCampaigns;
use App\Models\Paco\Campaign;
use App\Services\Paco\PacoPrefillService;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\KeyValue;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TagsInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

final class PacoCampaignResource extends Resource
{
    protected static ?string $model = Campaign::class;

    protected static ?string $modelLabel = 'Campaña conversacional';

    protected static ?string $pluralModelLabel = 'Campañas conversacionales';

    protected static ?string $navigationLabel = 'Campañas';

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-megaphone';

    protected static string|\UnitEnum|null $navigationGroup = 'Conversaciones';

    protected static ?int $navigationSort = 10;

    public static function form(Schema $schema): Schema
    {
        return $schema->columns(1)->schema([
            Section::make('Entrada y mensaje')->schema([
                TextInput::make('code')->label('Código')->required()->alphaDash()->unique(ignoreRecord: true),
                TextInput::make('name')->label('Nombre')->required()->maxLength(255),
                Select::make('status')->label('Estado')->options([
                    'draft' => 'Borrador', 'active' => 'Activa', 'paused' => 'Pausada', 'archived' => 'Archivada',
                ])->required()->default('draft'),
                TextInput::make('max_interactions')->label('Máximo de interacciones')->numeric()->minValue(2)->maxValue(20),
                Textarea::make('initial_message')->label('Mensaje inicial')->required()->rows(4)->columnSpanFull(),
                Select::make('preferred_intent_id')->label('Intención sugerida')->relationship('intent', 'name')->searchable()->preload(),
                Select::make('preferred_playbook_id')->label('Playbook sugerido')->relationship('playbook', 'name')->searchable()->preload(),
            ])->columns(1),
            Section::make('Contexto y distribución')->schema([
                KeyValue::make('context')->label('Contexto controlado')->keyLabel('Clave')->valueLabel('Valor')->columnSpanFull(),
                TagsInput::make('allowed_origins')->label('Orígenes permitidos')->placeholder('socies.com.ar')->helperText('Vacío permite cualquier origen; ingresá solamente hosts.'),
                DateTimePicker::make('starts_at')->label('Comienza'),
                DateTimePicker::make('ends_at')->label('Finaliza')->after('starts_at'),
            ])->columns(1),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')->label('Campaña')->searchable()->sortable()->description(fn (Campaign $record): string => $record->code),
                TextColumn::make('status')->label('Estado')->badge()->sortable(),
                TextColumn::make('intent.name')->label('Intención')->placeholder('Detección automática'),
                TextColumn::make('playbook.name')->label('Playbook')->placeholder('Selección automática'),
                TextColumn::make('conversations_count')->label('Conversaciones')->counts('conversations')->sortable(),
                TextColumn::make('updated_at')->label('Actualizada')->since()->sortable(),
            ])
            ->filters([SelectFilter::make('status')->options([
                'draft' => 'Borrador', 'active' => 'Activa', 'paused' => 'Pausada', 'archived' => 'Archivada',
            ])])
            ->actions([
                self::prefillAction(),
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->bulkActions([BulkActionGroup::make([DeleteBulkAction::make()])])
            ->defaultSort('updated_at', 'desc');
    }

    private static function prefillAction(): Action
    {
        return Action::make('prefill')
            ->label('Crear enlace')
            ->icon('heroicon-o-link')
            ->visible(fn (Campaign $record): bool => $record->status === 'active')
            ->schema(fn (Schema $schema): Schema => $schema->columns(1)->schema([
                TextInput::make('name')->label('Nombre'),
                TextInput::make('email')->label('Email')->email(),
                TextInput::make('phone')->label('WhatsApp')->tel(),
                Textarea::make('initial_query')->label('Consulta inicial')->rows(3),
                TextInput::make('utm_source')->label('UTM source'),
                TextInput::make('utm_medium')->label('UTM medium'),
                TextInput::make('utm_campaign')->label('UTM campaign'),
            ]))
            ->action(function (Campaign $record, array $data, PacoPrefillService $prefills): void {
                $url = $prefills->createLink($record, $data, $data);
                Notification::make()
                    ->title('Enlace privado creado')
                    ->body($url)
                    ->success()
                    ->persistent()
                    ->send();
            });
    }

    public static function getPages(): array
    {
        return ['index' => ManagePacoCampaigns::route('/')];
    }
}

<?php

declare(strict_types=1);

namespace App\Filament\Resources;

use App\Filament\Resources\PacoLeadResource\Pages\ManagePacoLeads;
use App\Filament\Resources\PacoLeadResource\Pages\ViewPacoLead;
use App\Models\Paco\ConversationEvent;
use App\Models\Paco\Lead;
use Filament\Actions\ViewAction;
use Filament\Infolists\Components\RepeatableEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

final class PacoLeadResource extends Resource
{
    protected static ?string $model = Lead::class;

    protected static ?string $modelLabel = 'Consulta';

    protected static ?string $pluralModelLabel = 'Consultas';

    protected static ?string $navigationLabel = 'Consultas recibidas';

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-inbox-stack';

    protected static string|\UnitEnum|null $navigationGroup = 'Conversaciones';

    protected static ?int $navigationSort = 1;

    public static function getNavigationBadge(): ?string
    {
        $count = self::getModel()::query()->where('status', 'pending_review')->count();

        return $count > 0 ? (string) $count : null;
    }

    public static function infolist(Schema $schema): Schema
    {
        return $schema->schema([
            Section::make('Contacto')->schema([
                TextEntry::make('name')->label('Nombre')->placeholder('No informado'),
                TextEntry::make('organization_name')->label('Organización')->placeholder('No informada'),
                TextEntry::make('email')->label('Email')->copyable()->placeholder('No informado'),
                TextEntry::make('phone_e164')->label('WhatsApp')->copyable()->placeholder('No informado'),
                TextEntry::make('contact_channel')->label('Canal preferido')->badge(),
                TextEntry::make('status')->label('Estado')->badge(),
            ])->columns(1),
            Section::make('Calificación')->schema([
                TextEntry::make('primary_intent_code')->label('Intención')->badge(),
                TextEntry::make('fit_level')->label('Encaje')->badge(),
                TextEntry::make('score')->label('Puntaje')->suffix('/100'),
                TextEntry::make('next_action')->label('Próxima acción')->badge(),
                TextEntry::make('problem_summary')->label('Problema')->columnSpanFull(),
                TextEntry::make('summary')->label('Resumen')->columnSpanFull(),
            ])->columns(1),
            Section::make('Conversación')->schema([
                TextEntry::make('conversation.campaign.name')->label('Campaña')->placeholder('Sin campaña'),
                TextEntry::make('conversation.utm_source')->label('Origen UTM')->placeholder('Sin UTM'),
                TextEntry::make('conversation.created_at')->label('Inicio')->dateTime(),
                TextEntry::make('conversation.closed_at')->label('Cierre')->dateTime()->placeholder('En curso'),
                RepeatableEntry::make('conversation.events')->label('Historial')->schema([
                    TextEntry::make('sequence')->label('#'),
                    TextEntry::make('actor')->label('Actor')->badge(),
                    TextEntry::make('message')->label('Contenido')->getStateUsing(
                        fn (ConversationEvent $record): string => (string) (
                            $record->payload['turn']['message']
                            ?? $record->payload['display']
                            ?? $record->kind
                        ),
                    )->columnSpanFull(),
                    TextEntry::make('created_at')->label('Fecha')->dateTime(),
                ])->columns(1)->columnSpanFull(),
            ])->columns(1),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table->columns([
            TextColumn::make('name')->label('Persona')->searchable()->sortable()->placeholder('Sin nombre'),
            TextColumn::make('primary_intent_code')->label('Intención')->badge()->searchable(),
            TextColumn::make('fit_level')->label('Encaje')->badge(),
            TextColumn::make('score')->label('Puntaje')->numeric()->sortable()->placeholder('Pendiente'),
            TextColumn::make('status')->label('Estado')->badge()->sortable(),
            TextColumn::make('conversation.campaign.name')->label('Campaña')->placeholder('Directa'),
            TextColumn::make('created_at')->label('Recibida')->since()->sortable(),
        ])->filters([
            SelectFilter::make('status')->options([
                'collecting' => 'En conversación', 'pending_review' => 'Pendiente de revisión',
                'contacted' => 'Contactada', 'won' => 'Ganada', 'lost' => 'Descartada', 'abandoned' => 'Abandonada',
            ]),
            SelectFilter::make('fit_level')->options([
                'supported' => 'Lo hacemos', 'conditional' => 'Requiere revisión', 'unsupported' => 'No lo hacemos', 'unknown' => 'Sin definir',
            ]),
        ])->actions([
            ViewAction::make()->label('Ver consulta')
                ->url(fn (Lead $record): string => ViewPacoLead::getUrl(['record' => $record])),
        ])
            ->defaultSort('created_at', 'desc');
    }

    public static function getPages(): array
    {
        return [
            'index' => ManagePacoLeads::route('/'),
            'view' => ViewPacoLead::route('/{record}'),
        ];
    }
}

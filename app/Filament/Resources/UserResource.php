<?php

namespace App\Filament\Resources;

use App\Filament\Resources\UserResource\Pages;
use App\Filament\Resources\UserResource\RelationManagers;
use App\Models\User;
use Filament\Actions;
use Filament\Forms;
use Filament\Schemas\Schema;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

use Filament\Forms\Components\Select;
use Filament\Schemas\Components\Section;
// use App\Models\Role; // Assuming Role model exists - Replaced by Spatie/Permission
use Illuminate\Support\Facades\Hash; // For password hashing
use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\TextInput;

class UserResource extends Resource
{
    protected static ?string $model = User::class;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-users';

    protected static string|\UnitEnum|null $navigationGroup = 'Usuarios';

    protected static ?string $modelLabel = 'Usuario';

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->schema([
                Section::make('Información Personal')
                    ->columns(2)
                    ->schema([
                        TextInput::make('name')
                            ->label('Nombre')
                            ->required()
                            ->maxLength(255),
                        TextInput::make('email')
                            ->label('Correo Electrónico')
                            ->email()
                            ->required()
                            ->maxLength(255)
                            ->unique(ignoreRecord: true),
                        // TextInput::make('mobile')
                        //     ->label('Móvil')
                        //     ->tel()
                        //     ->maxLength(255),
                    ]),
                Section::make('Autenticación')
                    ->columns(2)
                    ->schema([
                        TextInput::make('password')
                            ->label('Contraseña')
                            ->password()
                            ->dehydrateStateUsing(fn($state) => Hash::make($state))
                            ->dehydrated(fn($state) => filled($state))
                            ->required(fn(string $context): bool => $context === 'create')
                            ->maxLength(255),
                        // DateTimePicker::make('email_verified_at')
                        //     ->label('Correo Verificado')
                        //     ->native(false),
                        // Toggle::make('is_active')
                        //     ->label('Está Activo')
                        //     ->default(true)
                        //     ->required(),
                        // Toggle::make('is_admin')
                        //     ->label('Es Administrador')
                        //     ->required(),
                    ]),
                Section::make('Rol y Permisos')
                    ->schema([
                        Select::make('roles') // Changed from role_id
                            ->label('Roles') // Changed label to plural
                            ->multiple() // Allow multiple roles
                            ->relationship('roles', 'name') // Uses 'roles' relationship from Spatie\Permission\Traits\HasRoles
                            ->searchable()
                            ->preload()
                            ->required(),
                    ]),
                Section::make('Información Adicional')
                    ->columns(2)
                    ->collapsible()
                    ->collapsed()
                    ->schema([
                        DateTimePicker::make('last_seen')
                            ->label('Última Vista')
                            ->disabled(),
                        TextInput::make('created_by')
                            ->label('Creado Por')
                            ->numeric()
                            ->disabled(),
                        TextInput::make('updated_by')
                            ->label('Actualizado Por')
                            ->numeric()
                            ->disabled(),
                        TextInput::make('deleted_by')
                            ->label('Eliminado Por')
                            ->numeric()
                            ->disabled(),
                    ])
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
                Tables\Columns\TextColumn::make('email')
                    ->label('Correo Electrónico')
                    ->searchable()
                    ->sortable(),
                // Tables\Columns\IconColumn::make('is_admin')
                //     ->label('Admin')
                //     ->boolean()
                //     ->sortable(),
                Tables\Columns\TextColumn::make('roles.name') // Uses 'roles' relationship
                    ->label('Roles') // Changed label to plural
                    ->searchable()
                    ->badge()
                    ->sortable(),
                Tables\Columns\TextColumn::make('last_seen')
                    ->label('Última Vista')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                // Tables\Columns\TextColumn::make('mobile')
                //     ->label('Móvil')
                //     ->searchable()
                //     ->toggleable(isToggledHiddenByDefault: true),
                // Tables\Columns\TextColumn::make('email_verified_at')
                //     ->label('Correo Verificado')
                //     ->dateTime()
                //     ->sortable()
                //     ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Creado En')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('updated_at')
                    ->label('Actualizado En')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('created_by')
                    ->label('Creado Por')
                    ->numeric()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('updated_by')
                    ->label('Actualizado Por')
                    ->numeric()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('deleted_by')
                    ->label('Eliminado Por')
                    ->numeric()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->actions([
                Actions\ViewAction::make(),
                Actions\EditAction::make(),
                Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Actions\BulkActionGroup::make([
                    Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ManageUsers::route('/'),
        ];
    }
}

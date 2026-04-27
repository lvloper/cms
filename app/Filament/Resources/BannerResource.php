<?php

namespace App\Filament\Resources;

use App\Filament\Resources\BannerResource\Pages;
use App\Filament\Resources\BannerResource\RelationManagers;
use App\Models\Banner;
use Filament\Actions;
use Filament\Forms;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Schema;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;;
use App\Enums\BannerLocation;
use App\Enums\Status;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ImageColumn;
use Filament\Schemas\Components\Utilities\Get;
use App\Filament\Forms\Components\RoutePicker;


class BannerResource extends Resource
{
    protected static ?string $model = Banner::class;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-rectangle-stack';

    protected static ?string $modelLabel = 'Banner';
    protected static ?string $pluralLabel = 'Banners';


    public static function form(Schema $schema): Schema
    {
        return $schema
            ->schema([
                Forms\Components\TextInput::make('title')
                    ->label('Título')
                    ->required()
                    ->columnSpanFull(),
                Forms\Components\ToggleButtons::make('status')
                    ->label('Estado')
                    ->options(Status::class)
                    ->inline()
                    ->default(Status::Draft)
                    ->required()
                    ->columnSpanFull()
                    ->hiddenLabel(),

                Tabs::make('image_desktop_mobile')
                    ->tabs([
                        Tabs\Tab::make('Escritorio')
                            ->schema([
                                Forms\Components\FileUpload::make('image')
                                    ->label('Imagen')
                                                            ->image()
                                    ->imageEditor()
                                    ->imageEditorMode(2)
                                    ->preserveFilenames()
                                    ->imageResizeTargetWidth(fn (Get $get): int => $get('location') ? BannerLocation::from($get('location'))->getWidth() : false)
                                    ->imageResizeTargetHeight(fn (Get $get): int => $get('location') ? BannerLocation::from($get('location'))->getHeight() : false)
                                    ->imageResizeMode('cover')
                                    ->imageResizeUpscale(false)
                                    ->orientImagesFromExif()
                                    ->multiple(false)
                                    ->required()
                                    ->reactive()
                                    ->helperText(fn (Get $get): string => $get('location') ? "Tamaño recomendado: " . BannerLocation::from($get('location'))->getWidth() . " x " . BannerLocation::from($get('location'))->getHeight() : '')
                                    ->visibility('public')
                            ])
                            ->icon('heroicon-o-computer-desktop'),
                        Tabs\Tab::make('Mobile')
                            ->schema([
                                Forms\Components\FileUpload::make('image_mobile')
                                    ->label('Imagen')
                                                            ->image()
                                    ->imageEditor()
                                    ->imageEditorMode(2)
                                    ->preserveFilenames()
                                    ->helperText('Esta en dispositivos móviles, en caso de no asignar, se mostrará la imagen de escritorio')
                                    ->imageResizeMode('cover')
                                    ->imageResizeUpscale(false)
                                    ->multiple(false)
                                    ->orientImagesFromExif()
                                    ->directory('banners')
                                    ->visibility('public')
                            ])
                            ->icon('heroicon-o-device-phone-mobile'),
                    ])
                    ->columns(2),
                Forms\Components\ToggleButtons::make('location')
                    ->label('Ubicación')
                    ->options(BannerLocation::class)
                    ->reactive()
                    ->required(),


                RoutePicker::make('route')
                ->required()
                ->label('Enlace de destino')
                ->columnSpanFull(),

            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                ImageColumn::make('image')
                ->label('Imagen'),
                TextColumn::make('title')
                    ->label('Título'),
                TextColumn::make('status')
                    ->label('Estado')
                    ->badge(),
                TextColumn::make('location')
                    ->label('Ubicación')
                    ->badge(),
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
            'index' => Pages\ListBanners::route('/'),
            'create' => Pages\CreateBanner::route('/create'),
            'edit' => Pages\EditBanner::route('/{record}/edit'),
        ];
    }
}

<?php

namespace App\Filament\Resources;

use App\Filament\Resources\BlogResource\Pages;
use App\Models\Blog;
use App\Filament\Resources\Bases\ResourceBase;
use Filament\Forms\Components\Builder;
use Filament\Forms\Components\Builder\Block;
use Filament\Schemas\Schema;

use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\SpatieTagsInput;

use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class BlogResource extends ResourceBase
{
    use \App\Filament\Traits\FormShortcuts;

    protected static ?string $model = Blog::class;

    protected static ?string $modelLabel = 'Novedades';

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-newspaper';

    protected static function mainTab(Schema $schema): array
    {
        return [

            TextInput::make('route.title')
                ->label('Título')
                ->required()
                ->live(onBlur: true)
                ->afterStateUpdated(function (Get $get, Set $set, ?string $operation, ?string $old, ?string $state, ?Model $record) {

                    if ($operation === 'edit' && $record?->isPublished()) {
                        return;
                    }

                    if (($get('route.slug') ?? '') !== Str::slug($old)) {
                        return;
                    }

                    $set('route.slug', Str::slug($state));
                }),

            \Filament\Schemas\Components\Grid::make(2)
                ->schema([
                    static::Image(
                        name: 'image',
                        label: 'Imagen',
                        width: '1910',
                        height: '1000',
                    ),
                    \Filament\Schemas\Components\Group::make()
                        ->schema([
                            SpatieTagsInput::make('tags')->label('Nube de tags'),
                        ]),
                ]),




            static::TipTap(
                name: 'description',
                label: 'Descripción',
                profile: 'minimal',
                required: false
            ),

            static::TipTap(
                name: 'content',
                label: 'Contenido',
                profile: 'avanced',
                required: true
            ),
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListBlogs::route('/'),
            'create' => Pages\CreateBlog::route('/create'),
            'edit' => Pages\EditBlog::route('/{record}/edit'),
        ];
    }
}

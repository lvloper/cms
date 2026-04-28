<?php

namespace App\Filament\Resources;

use App\Filament\Forms\Components\Image;
use App\Filament\Resources\BlogResource\Pages;
use App\Filament\Resources\Bases\ResourceBase;
use App\Models\Blog;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\SpatieTagsInput;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class BlogResource extends ResourceBase
{
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
                    Image::make(
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
            RichEditor::make('description')
                ->label('Descripción')
                ->toolbarButtons(config('admin.richEditor.minimal', config('admin.richEditor.basic'))),

            RichEditor::make('content')
                ->label('Contenido')
                ->toolbarButtons(config('admin.richEditor.avanced', config('admin.richEditor.basic')))
                ->required(),
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

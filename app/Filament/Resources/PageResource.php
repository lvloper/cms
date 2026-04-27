<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PageResource\Pages;
use App\Models\Page;
use App\Filament\Resources\Bases\ResourceBase;


class PageResource extends ResourceBase
{
    protected static ?string $model = Page::class;

    protected static ?string $modelLabel = 'Página';

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-square-2-stack';

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListPages::route('/'),
            'create' => Pages\CreatePage::route('/create'),
            'edit' => Pages\EditPage::route('/{record}/edit'),
        ];
    }
}

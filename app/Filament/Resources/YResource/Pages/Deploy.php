<?php

namespace App\Filament\Resources\YResource\Pages;

use App\Filament\Resources\YResource;
use Filament\Resources\Pages\Page;

class Deploy extends Page
{
    protected static string $resource = YResource::class;

    protected string $view = 'filament.resources.y-resource.pages.deploy';
}
